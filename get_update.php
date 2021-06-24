<?php

/*
Roept de laatste wijziging voor de cursussen tabel van het geselecteerde bedrijf aan en geeft deze weer op de frontend.
*/

define('ROOT', __DIR__);

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 
$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);


if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

include_once("config.php");

$bedrijf;

if ($data[0] != "default" && $data[0] != "" && $_SESSION["rol"] == "admin"){
    $bedrijf = $data[0];
}

else {
    $bedrijf = $_SESSION['bedrijf'];
}

$link = new mysqli(host, database_cursus, password_cursus, database_cursus);


$stmt = $link->prepare("SELECT `update` FROM laatste_wijziging WHERE bedrijf = ?");
$stmt->bind_param('s', $bedrijf);
$stmt->execute();

 $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
if ($result){
   
echo "Laatste update: " . $result[0]["update"];
}

else {
    echo "Update datum niet beschikbaar";
}
?>