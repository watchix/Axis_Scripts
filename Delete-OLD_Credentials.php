<?php

$ctrl_user = "root";
$ctrl_password = "password";
$ctrl_ip = "ctrl.axis.com";
$ctrl_ports = [20080, 20081, 20082]; // Liste des ports à tester
$dry_run = true; // Mettre false pour supprimer réellement

function request_api($url, $ctrl_user, $ctrl_password, $data_string)
{
    $headers = ["Content-Type: application/json"];

    $req = curl_init();
    curl_setopt_array($req, [
        CURLOPT_URL => $url,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "$ctrl_user:$ctrl_password",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_VERBOSE => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($req);
    if ($response === false) {
        $err = curl_error($req);
        curl_close($req);
        return "CURL ERROR: $err";
    }
    curl_close($req);
    return $response;
}

function find_first_key_recursive($data, array $keys)
{
    if (!is_array($data)) return null;
    foreach ($keys as $k) {
        if (array_key_exists($k, $data)) {
            return $data[$k];
        }
    }
    foreach ($data as $v) {
        if (is_array($v)) {
            $found = find_first_key_recursive($v, $keys);
            if ($found !== null) return $found;
        }
    }
    return null;
}

function delete_credential($url, $user, $password, $token, $logFile, $dry_run)
{
    if ($dry_run) {
        file_put_contents($logFile, "[" . date('c') . "] [DRY-RUN] Suppression simulée : $token\n", FILE_APPEND);
        return;
    }

    $payload = json_encode([
        "pacsaxis:RemoveCredential" => ["Token" => [$token]]
    ], JSON_UNESCAPED_SLASHES);

    $response = request_api($url, $user, $password, $payload);
    file_put_contents($logFile, "[" . date('c') . "] Suppression réelle du credential : $token\n", FILE_APPEND);
    file_put_contents($logFile, "Réponse: $response\n", FILE_APPEND);
	echo "Suppression réelle du credential : $token\n";
}

function GetCredentialList($logFile, $user, $password, $ip, $port, $dry_run)
{
    file_put_contents($logFile, ""); // reset du log
    $url = "https://$ip:$port/vapix/pacs";

    $payload = [
        "pacsaxis:GetCredentialList" => ["Limit" => 5000]
    ];
    $data_string = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $response = request_api($url, $user, $password, $data_string);
    $data = json_decode($response, true);

    if ($data === null) {
        file_put_contents($logFile, "[" . date('c') . "] JSON invalide: " . substr($response, 0, 2000) . "\n", FILE_APPEND);
        return;
    }

    $credentials = find_first_key_recursive($data, ['Credential', 'credential', 'Credentials']);
    $totalCreds = 0;
    $deletedCreds = 0;

    if (!empty($credentials) && is_array($credentials)) {
        foreach ($credentials as $cred) {
            $token = $cred["token"] ?? "";
            $userToken = $cred["UserToken"] ?? "";
            $validTo = $cred["ValidTo"] ?? "";
            $status = $cred["Status"] ?? "";

            $line = "Credential: $token | UserToken: $userToken | ValidTo: $validTo | Status: $status\n";
            file_put_contents($logFile, $line, FILE_APPEND);
            $totalCreds++;

            // Supprimer uniquement si ValidTo est une vraie date et expirée
            if (!empty($validTo) && strtotime($validTo) !== false) {
                $validToTime = strtotime($validTo);
                $oneMonthAgo = strtotime('-31 days');
                if ($validToTime < $oneMonthAgo) {
                    delete_credential($url, $user, $password, $token, $logFile, $dry_run);
                    $deletedCreds++;
                }
            }
        }
    }

    file_put_contents($logFile, "\nTotal Credentials: $totalCreds\n", FILE_APPEND);
    file_put_contents($logFile, "Total supprimés: $deletedCreds\n", FILE_APPEND);
}

// ==== Exécution principale ====
foreach ($ctrl_ports as $port) {
    $logFile = "credentials_{$port}.logs";
    echo "=== Traitement du port $port ===\n";
    GetCredentialList($logFile, $ctrl_user, $ctrl_password, $ctrl_ip, $port, $dry_run);
    echo "Résultats écrits dans $logFile\n";
}

?>
