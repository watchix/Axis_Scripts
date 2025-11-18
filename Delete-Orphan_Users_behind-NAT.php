<?php
/**
 * Script: clean_orphan_users.php
 * But: supprime (ou simule la suppression) des utilisateurs "orphelins" (sans credential)
 * Mode dry-run par défaut, écrit des logs par port :
 *  - user_<port>.logs
 *  - credentials_<port>.logs
 *  - retour_<port>.logs
 *
 * Adapter les variables $ctrl_user, $ctrl_password, $ctrl_ip et $ctrl_ports avant usage.
 */

$ctrl_user = "root";
$ctrl_password = "password";
$ctrl_ip = "ctrls.domain.org";
$ctrl_ports = [20080, 20081, 20082]; // Liste des ports à tester
$dry_run = true; // true = simulate, false = execute deletion

/**
 * Envoi d'une requête JSON vers le contrôleur Axis PACS
 * Retourne la réponse brute ou false en cas d'échec réseau.
 */
function request_api($url, $ctrl_user, $ctrl_password, $data_string) {
    $headers = ["Content-Type: application/json"];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "$ctrl_user:$ctrl_password",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return false;
    }

    // on considère comme échec si code HTTP absent ou >= 400
    if ($code === 0 || $code >= 400) {
        // retourner la réponse quand même (peut contenir message d'erreur)
        return $resp;
    }

    return $resp;
}

/**
 * Obtenir la liste (single-page) des users (Limit configurable)
 * Écrit dans $logFile (user_<port>.logs)
 * Retourne un tableau d'utilisateurs (chaque élément tableau associatif) ou [] si aucun.
 */
function GetUserInfoList($logFile, $user, $password, $ip, $port, $limit = 5000) {
    file_put_contents($logFile, ""); // reset
    $url = "https://$ip:$port/vapix/pacs";

    $payload = ["axudb:GetUserInfoList" => ["Limit" => $limit]];
    $data_string = json_encode($payload, JSON_UNESCAPED_SLASHES);

    $response = request_api($url, $user, $password, $data_string);
    if ($response === false) {
        file_put_contents($logFile, "[".date('c')."] ERROR: request failed\n", FILE_APPEND);
        return [];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        file_put_contents($logFile, "[".date('c')."] ERROR: invalid JSON response\n", FILE_APPEND);
        file_put_contents($logFile, substr($response, 0, 2000) . "\n", FILE_APPEND);
        return [];
    }

    $users = [];
    if (!empty($decoded['UserInfo']) && is_array($decoded['UserInfo'])) {
        foreach ($decoded['UserInfo'] as $u) {
            $token = $u['token'] ?? '';
            $name  = $u['Name'] ?? '';
            $line = sprintf("UserToken: %s | Name: %s\n", $token, $name);
            file_put_contents($logFile, $line, FILE_APPEND);
            $users[] = $u;
        }
        file_put_contents($logFile, "Total Users: ".count($decoded['UserInfo'])."\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "[".date('c')."] No UserInfo found in response\n", FILE_APPEND);
    }

    return $users;
}

/**
 * Obtenir la liste (single-page) des credentials (Limit configurable)
 * Écrit dans $logFile (credentials_<port>.logs)
 * Retourne un tableau de credentials (chaque élément assoc) ou [].
 */
function GetCredentialList($logFile, $user, $password, $ip, $port, $limit = 5000) {
    file_put_contents($logFile, ""); // reset
    $url = "https://$ip:$port/vapix/pacs";

    $payload = ["pacsaxis:GetCredentialList" => ["Limit" => $limit]];
    $data_string = json_encode($payload, JSON_UNESCAPED_SLASHES);

    $response = request_api($url, $user, $password, $data_string);
    if ($response === false) {
        file_put_contents($logFile, "[".date('c')."] ERROR: request failed\n", FILE_APPEND);
        return [];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        file_put_contents($logFile, "[".date('c')."] ERROR: invalid JSON response\n", FILE_APPEND);
        file_put_contents($logFile, substr($response, 0, 2000) . "\n", FILE_APPEND);
        return [];
    }

    $creds = [];
    if (!empty($decoded['Credential']) && is_array($decoded['Credential'])) {
        foreach ($decoded['Credential'] as $c) {
            $token = $c['token'] ?? '';
            $userToken = $c['UserToken'] ?? '';
            $validTo = $c['ValidTo'] ?? '';
            $status = $c['Status'] ?? '';
            $line = sprintf("Credential: %s | UserToken: %s | ValidTo: %s | Status: %s\n", $token, $userToken, $validTo, $status);
            file_put_contents($logFile, $line, FILE_APPEND);
            $creds[] = $c;
        }
        file_put_contents($logFile, "Total Credentials: ".count($decoded['Credential'])."\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "[".date('c')."] No Credential found in response\n", FILE_APPEND);
    }

    return $creds;
}

/**
 * Supprime (ou simule) un utilisateur par UserToken.
 * Utilise axudb:RemoveUser payload.
 */
function delete_user($url, $user, $password, $userToken, $logFile, $dry_run) {
    if ($dry_run) {
        file_put_contents($logFile, "[".date('c')."] [DRY-RUN] Would remove user: $userToken\n", FILE_APPEND);
        return true;
    }

    $payload = json_encode(["axudb:RemoveUser" => ["Token" => [$userToken]]], JSON_UNESCAPED_SLASHES);
    $resp = request_api($url, $user, $password, $payload);

    file_put_contents($logFile, "[".date('c')."] RemoveUser request for $userToken\n", FILE_APPEND);
    file_put_contents($logFile, "Response: ".(is_string($resp) ? $resp : json_encode($resp))."\n", FILE_APPEND);

    return true;
}

/**
 * Identifie les users sans credential (orphelins) et les supprime (ou simule).
 * Ecrit les actions dans $retourFile.
 */
function CleanOrphanUsers($userFileLog, $credFileLog, $retourFile, $user, $password, $ip, $port, $dry_run) {
    file_put_contents($retourFile, ""); // reset
    $url = "https://$ip:$port/vapix/pacs";

    // Lire les user tokens depuis le log users produit par GetUserInfoList
    $userTokens = [];
    if (is_file($userFileLog)) {
        foreach (file($userFileLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (preg_match('/UserToken:\s*([^\s|]+)/', $line, $m)) {
                $userTokens[] = trim($m[1]);
            }
        }
    }

    // Lire les user tokens qui ont un credential depuis le log credentials
    $credUserTokens = [];
    if (is_file($credFileLog)) {
        foreach (file($credFileLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (preg_match('/UserToken:\s*([^\s|]+)/', $line, $m)) {
                $credUserTokens[] = trim($m[1]);
            }
        }
    }

    // Déduire les orphelins
    $orphans = array_values(array_diff($userTokens, $credUserTokens));

    if (empty($orphans)) {
        $msg = "[".date('c')."] Aucun utilisateur orphelin détecté sur le port $port\n";
        file_put_contents($retourFile, $msg, FILE_APPEND);
        echo $msg;
        return;
    }

    // Supprimer (ou simuler) chaque orphan
    $count = 0;
    foreach ($orphans as $ut) {
        // sécurité: ignorer tokens vides
        if (empty($ut)) continue;
        file_put_contents($retourFile, "[".date('c')."] Orphan user detected: $ut\n", FILE_APPEND);
        $ok = delete_user($url, $user, $password, $ut, $retourFile, $dry_run);
        if ($ok) $count++;
    }

    $summary = "[".date('c')."] Total orphans processed: $count\n";
    file_put_contents($retourFile, $summary, FILE_APPEND);
    echo $summary;
}

/**
 * Main : boucle sur les ports, récupère users & credentials, puis supprime orphelins.
 */
foreach ($ctrl_ports as $port) {
    echo "=== Processing port $port ===\n";

    $userLog  = "user_{$port}.logs";
    $credLog  = "credentials_{$port}.logs";
    $retourLog= "retour_{$port}.logs";

    // Récupérer users & credentials (single page)
    $users = GetUserInfoList($userLog, $ctrl_user, $ctrl_password, $ctrl_ip, $port);
    $creds = GetCredentialList($credLog, $ctrl_user, $ctrl_password, $ctrl_ip, $port);

    // Si aucun user récupéré, on skip
    if (empty($users)) {
        echo "No users retrieved on port $port, skipping.\n";
        file_put_contents($retourLog, "[".date('c')."] No users retrieved on port $port\n", FILE_APPEND);
        continue;
    }

    // Effectuer la suppression/simulation des orphelins
    CleanOrphanUsers($userLog, $credLog, $retourLog, $ctrl_user, $ctrl_password, $ctrl_ip, $port, $dry_run);

    echo "Logs: $userLog, $credLog, $retourLog\n";
    echo "=== Done for port $port ===\n\n";
}

echo "All done.\n";
