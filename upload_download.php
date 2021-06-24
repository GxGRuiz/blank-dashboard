<?php

/*
Uploadt een file naar de downloads folder die verstuurd is via de pagina "admin-documentatie".
Geeft een error op de frontend als de upload fout gaat.
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    header("Location: http://dummy.url/login");
    exit();
}

if ($_SESSION["rol"] != "admin"){
    header("Location: http://dummy.url/home");
    exit();
}

include_once("config.php");

$link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    if ($_POST["folder-selectie"] == ""){
        $_SESSION["error-message"] = "Kies a.u.b. een folder";
        header("Location: http://dummy.url/admin-documentatie");
        exit(); 
       }
    
    $folder;
    
    switch ($_POST["folder-selectie"]){
        case "Algemene Documenten":
             $folder = "downloads";
             break;
            
        case "Handleiding":    
             $folder = "handleiding";
             break;
            
        case "Juridisch":
             $folder = "juridisch";
             break;
            
        default:
             $_SESSION["error-message"] = "Er ging iets fout, probeer het later opnieuw";
             header("Location: http://dummy.url/admin-documentatie");
             exit();  
           }
   
    if ($_FILES['nieuwe-download']["size"] > 30000000){
        $_SESSION["error-message"] = "Uw bestand is groter dan 30MB, pas deze a.u.b. aan";
        header("Location: http://dummy.url/admin-documentatie");
        exit();   
    } 
    
    
    $uploaddir = '../' . $folder . "/";
$uploadfile = $uploaddir . basename($_FILES['nieuwe-download']['name']);

    
if (move_uploaded_file($_FILES['nieuwe-download']['tmp_name'], $uploadfile)) {
   $_SESSION["error-message"] = $_FILES['nieuwe-download']['name'] . " toegevoegd aan " . $_POST["folder-selectie"] . ".";
    
    $file_link = "/assets" . substr($uploadfile, 2);
    
    foreach ($_POST as $key => $value){
                  
                  if ($key == "folder-selectie" || $key == "MAX_FILE_SIZE"){
                      continue;
                  }            
        
                  if (!bedrijfValidatie(htmlspecialchars($value))){
                      $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
                      header("Location: http://dummy.url/admin-documentatie");
                      exit();
                     }
                  
                  $documentatie_sql = "INSERT INTO `" . htmlspecialchars($value) . "` (doc_link, categorie, datum) VALUES(?, ?, now())";
                  $stmt = $link_documentatie->prepare($documentatie_sql);
                  $stmt->bind_param('ss', $file_link, $_POST["folder-selectie"]);
                  $stmt->execute();
                  $_SESSION["error-message"] .= "<br>" . $_FILES['nieuwe-download']['name'] . " zichtbaar voor " . htmlspecialchars($value);
          }
    
header("Location: http://dummy.url/admin-documentatie");
                exit();
    
} else {
    $_SESSION["error-message"] = "Er ging iets fout, neem contact met de beheerder.";
    header("Location: http://dummy.url/admin-documentatie");
                exit();
}


}

else {
      $_SESSION["error-message"] = "Er ging iets fout met het verwerken van het bestand, probeer het (later) opnieuw.";
    header("Location: http://dummy.url/admin-documentatie");
                exit();
}

 
?>