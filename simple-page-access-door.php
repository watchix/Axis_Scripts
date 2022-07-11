<?php
if (session_status() == PHP_SESSION_NONE){
	session_start();

$user = "root";
$password = "xxxxx";
$ip = "my.axis.domain.com";
$url = "http://$user:$password@$ip/vapix/doorcontrol";
$axis_token = "Axis-xxxxxxxx:1578502297.927987000";

function request_api($url,$user,$password,$data_string){
	$headers = array("Content-Type: application/json");

	$req = curl_init();
	curl_setopt_array($req, array(
		CURLOPT_URL => $url,
		CURLOPT_TIMEOUT => "10",
		CURLOPT_HTTPAUTH => CURLAUTH_ANY,
		CURLOPT_USERPWD => "$user:$password",
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $data_string,
		CURLOPT_HEADER => false,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_VERBOSE => false,
		CURLOPT_RETURNTRANSFER => true,
		));

	return curl_exec($req);

	curl_close($req);
}

$data_string_access_door = "{
	\"tdc:AccessDoor\":{
		\"Token\":\"$axis_token\",
	}
}";

$data_string_unlock_door = "{
	\"tdc:UnlockDoor\":{
		\"Token\":\"$axis_token\",
	}
}";

$data_string_lock_door = "{
	\"tdc:LockDoor\":{
		\"Token\":\"$axis_token\",
	}
}";


if (isset($_POST["Unlock_Entree_01"]) && $_POST["Unlock_Entree_01"] != "")
	$retour = request_api($url,$user,$password,$data_string_unlock_door);

if (isset($_POST["Lock_Entree_01"]) && $_POST["Lock_Entree_01"] != "")
	$retour = request_api($url,$user,$password,$data_string_lock_door);

if (isset($_POST["Access_Entree_01"]) && $_POST["Access_Entree_01"] != "")
	$retour = request_api($url,$user,$password,$data_string_access_door);

?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8"/>
		<link rel="icon" href="favicon.png"/>
		<link rel="icon" sizes="16x16" href="favicon.png"/>
	</head>
	<body>
		<form method="POST" action="simple-page-access-door.php">
			<h2>Ouverture de portes</h2>
			<input type="submit" name="Access_Entree_01" class="button" value="Ouvrir la porte quelques secondes"/>
			<input type="submit" name="Unlock_Entree_01" class="button" value="Deverouiller la porte"/>
			<input type="submit" name="Lock_Entree_01" class="button" value="Verouiller la porte"/>
		</form>
	</body>
</html>
