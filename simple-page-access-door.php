<?php
if (session_status() == PHP_SESSION_NONE){
	session_start();
	$premier_affichage = "yes";
}
else
	$premier_affichage = "no";

$user = "root";
$password = "xxxxx";
$ip = "my.axis.domain.com";
$url = "http://$user:$password@$ip/vapix/doorcontrol";

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
		\"Token\":\"Axis-accc8ee11485:1578502297.927987000\",
	}
}";

$data_string_unlock_door = "{
	\"tdc:UnlockDoor\":{
		\"Token\":\"Axis-accc8ee11485:1578502297.927987000\",
	}
}";

$data_string_lock_door = "{
	\"tdc:LockDoor\":{
		\"Token\":\"Axis-accc8ee11485:1578502297.927987000\",
	}
}";


if (isset($_POST["Unlock_Entree_01"]) && $_POST["Unlock_Entree_01"] != "")
	$retour = request_api($url,$user,$password,$data_string_access_door);

if (isset($_POST["Lock_Entree_01"]) && $_POST["Lock_Entree_01"] != "")
	$retour = request_api($url,$user,$password,$data_string_access_door);

if (isset($_POST["Access_Entree_01"]) && $_POST["Access_Entree_01"] != "" OR $premier_affichage == "yes")
	$retour = request_api($url,$user,$password,$data_string_access_door);
else
	$retour = request_api($url,$user,$password,$data_string_access_door);
?>

<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8"/>
		<script src="js/jquery.min.js"/></script>
		<link rel="icon" href="favicon.png"/>
		<link rel="icon" sizes="16x16" href="favicon.png"/>
	</head>
	<body>
		<form method="POST" action="simple-page-access-door.php">
			<h2>Ouverture de portes</h2>
			<input type="submit" name="Access_Entree_01" class="button" value="Access_Entree_01"/>
			<input type="submit" name="Unlock_Entree_01" class="button" value="Unlock_Entree_01"/>
			<input type="submit" name="Lock_Entree_01" class="button" value="Lock_Entree_01"/>
		</form>
	</body>
</html>
