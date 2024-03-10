<?php
include_once ("conf.php");
$retour = get_user_info_list($user,$password,$ip,$port);
$count_users_in_axis = (count ($retour["User"]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>manage users - Bootdey.com</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<table class="table table-sm">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Utilisateur</th>
      <th scope="col">Prénom</th>
      <th scope="col">Nom</th>
      <th scope="col">Description</th>
      <th scope="col">Token</th>
    </tr>
  </thead>
  <tbody>
    <?php
        for($i = 0; $i < $count_users_in_axis; ++$i) {
            $j=$i+1;
            $user_name = $retour["User"][$i]["Name"];
            $user_firstname = $retour["User"][$i]["Attribute"][0]["Value"];
            $user_lasttname = $retour["User"][$i]["Attribute"][1]["Value"];
            $user_description = $retour["User"][$i]["Description"];
            $user_token = $retour["User"][$i]["token"];
            echo"<tr>
        <th scope=\"row\">$j</th>
        <td>$user_name</td>
        <td>$user_firstname</td>
        <td>$user_lasttname</td>
        <td>$user_description</td>
        <td>$user_token</td>
    </tr>";
        }

    ?>
  </tbody>
</table>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
