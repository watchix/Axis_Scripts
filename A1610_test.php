<?php
if (session_status() == PHP_SESSION_NONE){
	session_start();
	$premier_affichage = "yes";
}
else
	$premier_affichage = "no";

$user = "xxxx";
$password = "xxxxx";
$ip = "my-domain.com";
$port = "80";
$url_door = "http://$user:$password@$ip:$port/vapix/doorcontrol";
$url_users = "http://$user:$password@$ip:$port/vapix/pacs";
$url_logs = "http://$user:$password@$ip:$port/vapix/eventlogger";

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

$data_string_users_info_list = "{\"axudb:GetUserList\":{}}";
$data_string_door_info_list = "{\"axtdc:GetDoorList\":{}}";


$data_string_access_door1 = "{
	\"axtdc:AccessDoorWithoutUnlock\":{
		\"Token\":\"Axis-b8a44f974773:1706541727.391507000\",
	}
}";

$data_string_configuration_door1 = "{
	\"axtdc:GetDoorConfiguration\":{
		\"Token\":\"Axis-b8a44f974773:1706541727.391507000\",
	}
}";

$data_string_access_door2 = "{
	\"axtdc:AccessDoorWithoutUnlock\":{
		\"Token\":\"Axis-b8a44f974773:1706541727.302685000\",
	}
}";

$data_string_release_door1 = "{
	\"axtdc:ReleaseDoor\":{
		\"Token\":\"Axis-b8a44f974773:1706541727.302685000\",
	}
}";

$data_string_release_door2 = "{
	\"axtdc:ReleaseDoor\":{
		\"Token\":\"Axis-b8a44f974773:1706541727.302685000\",
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

// $retour = request_api($url_users,$user,$password,$data_string_users_info_list);
$retour = request_api($url_door,$user,$password,$data_string_door_info_list);
// $retour = request_api($url_door,$user,$password,$data_string_configuration_door1);
// $retour = request_api($url_door,$user,$password,$data_string_release_door1);
// $retour = request_api($url_door,$user,$password,$data_string_access_door2);
// $retour = request_api($url_door,$user,$password,$data_string_release_door2);

var_dump ($retour);

?>
