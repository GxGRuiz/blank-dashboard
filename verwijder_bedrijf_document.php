<?php

/*
Verwijdert document beschikbaarheid voor het geselecteerde bedrijf, mits deze bestaat.
Geeft anders een error bericht weer op de frontend als het fout gaat.
*/

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

include_once("config.php");

$link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);

$bedrijf = $data[0];

if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        
        exit();
     }

$document = $data[1];
$categorie = $data[2];
$doc_link = "/assets/"; 

switch ($categorie){
        case "Algemene Documenten":
             $doc_link .= "downloads/";
             break;
            
        case "Handleiding":    
             $doc_link .= "handleiding/";
             break;
            
        case "Juridisch":
             $doc_link .= "juridisch/";
             break;
        
        case "Kies een categorie":
             
             exit();  
             break;        
            
        default:
             $_SESSION["error-message"] = "Er ging iets fout, probeer het later opnieuw";
             
             exit();   
}

$doc_link .= $document;

$stmt = $link_documentatie->prepare("DELETE FROM `". $bedrijf ."` WHERE doc_link = ? AND categorie = ?");
$stmt->bind_param("ss", $doc_link, $categorie);

if ($stmt->execute()){
    $_SESSION["error-message"] = $document . " verwijderd voor " . $bedrijf;
    exit();
}

else {
    $_SESSION["error-message"] = "Er ging iets fout, probeer het later opnieuw";
             exit();   
}

?>