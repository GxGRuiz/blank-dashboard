<?php 

/* WIP */

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 

  session_start();

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

if ($_SESSION["rol"] != "admin"){
    header("Location: http://dummy.url/home");
    exit();
}


$link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);



?>