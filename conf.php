<?php

$user = "root";
$password = "xxxxx";
$ip = "x.x.x.x";
$port = "80";



$url_logs = "http://$user:$password@$ip:$port/vapix/eventlogger";  // URL pour les logs

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

function get_user_info_list($user,$password,$ip,$port){
    $url_users = "http://$user:$password@$ip:$port/vapix/pacs";  // URL pour les utilisateurs
    $data_string_users_info_list = "{\"axudb:GetUserList\":{}}";
    $retour = request_api($url_users,$user,$password,$data_string_users_info_list);
    //var_dump ($retour);
    return json_decode($retour, true);
}

function get_user_detail($user,$password,$ip,$port,$user_token){
    $url_users = "http://$user:$password@$ip:$port/vapix/pacs";  // URL pour les utilisateurs
    $data_string_users_info_list = "{\"axudb:GetUser\": { \"Token\" : [\"$user_token\"] }}";
    $retour = request_api($url_users,$user,$password,$data_string_users_info_list);
    var_dump ($retour);
    //return json_decode($retour, true);
}

function get_door_info_list($user,$password,$ip,$port){
    $url_door = "http://$user:$password@$ip:$port/vapix/doorcontrol";  // URL pour les portes
    $data_string_door_info_list = "{\"axtdc:GetDoorList\":{}}";
    $retour = request_api($url_door,$user,$password,$data_string_door_info_list);
    var_dump ($retour);
}

function add_user($user,$password,$ip,$port,$data_add_user){
    $url_users = "http://$user:$password@$ip:$port/vapix/pacs";  // URL pour les utilisateurs
    $retour = request_api($url_users,$user,$password,$data_add_user);
    var_dump ($retour);
}

function remove_user($user,$password,$ip,$port,$user_token){
    $url_users = "http://$user:$password@$ip:$port/vapix/pacs";  // URL pour les utilisateurs
    $data_remove_user = "{\"axudb:RemoveUser\":{\"Token\" : [\"$user_token\"]}}";
    $retour = request_api($url_users,$user,$password,$data_remove_user);
    var_dump ($retour);
}

$data_add_user = "{\"axudb:SetUser\":{\"User\":[
      {
        \"Name\":\"Utilisateur 02\",
        \"Description\":\"\",
        \"Attribute\":[
          {
            \"type\":\"string\",
            \"Name\":\"FirstName\",
            \"Value\":\"Jean\"
          },
          {
            \"type\":\"string\",
            \"Name\":\"LastName\",
            \"Value\":\"Michel\"
          }
        ]
      }
    ]}}";


//get_user_info_list($user,$password,$ip,$port);
//get_user_detail($user,$password,$ip,$port,"Axis-b8a44f82a012:1710104147.010620000");
//get_door_info_list($user,$password,$ip,$port);
//remove_user($user,$password,$ip,$port,$user_token);
//add_user($user,$password,$ip,$port,$data_add_user);
?>
