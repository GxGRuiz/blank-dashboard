<?php

/*
Roept de src link aan voor de bedrijfslogo voor het ingelogde account en weergeeft de logo op de frontend met een <img> element.
*/

define('ROOT', __DIR__);

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    header("Location: http://dummy.url/login");
    exit();
}

include_once("config.php");

$bedrijf = $_SESSION["bedrijf"];

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts); 

$stmt = $link_accounts->prepare("SELECT profiel_foto FROM dummy WHERE bedrijf = ?");

$stmt->bind_param("s", $bedrijf);

$stmt->execute();
$result = $stmt->get_result();
$result = $result->fetch_all(MYSQLI_ASSOC);
$foto = $result[0]["profiel_foto"];

echo "<img src='" . $foto . "'>";
?>