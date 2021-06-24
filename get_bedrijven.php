<?php

/*
Roept bedrijfnamen in de database aan en maakt hiermee <option> elementen aan voor een <select> element op de frontend.
*/

define('ROOT', __DIR__);

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

$bedrijven = "";

include_once("config.php");

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts); 

$result = $link_accounts->query("SELECT bedrijf FROM dummy")->fetch_all(MYSQLI_ASSOC);

for ($i = 0; $i < count($result); $i++){
    
     if ($_SESSION["bedrijf"] === $result[$i]["bedrijf"]){
         $bedrijven .= "<option selected>" . $result[$i]["bedrijf"] . "</option>"; 
        }
    
    else {
        $bedrijven .= "<option>" . $result[$i]["bedrijf"] . "</option>"; 
    }
}

echo $bedrijven;
?>