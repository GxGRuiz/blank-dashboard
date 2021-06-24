<?php

/*
Een nieuwsbericht voor een geselecteerd bedrijf wordt aangepast dmv een formulier op de "admin-nieuws" pagina, tenzij de velden leeg zijn.
*/

session_start();
include_once("config.php");

$link = new mysqli(host, database_accounts, password_accounts, database_accounts); 
 $link_nieuws = new mysqli(host, database_nieuws, password_nieuws, database_nieuws);

if(!mysqli_set_charset($link, 'utf8')){
    printf("Error loading character set utf8: %s\n", $link->error);
    printf("Current character set: %s\n", $link->character_set_name());
    die();
};   

if (!$link){
     die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
   }

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

$id; $titel; $bericht; $bedrijf;

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    $id = htmlspecialchars($_POST["id"]);
    
    if ($id == ""){
        $_SESSION["error-message"] = "Iets ging mis, probeer het opnieuw";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    $bedrijf = htmlspecialchars($_POST["bedrijf"]);
    
     if ($bedrijf == ""){
        $_SESSION["error-message"] = "Iets ging mis, probeer het opnieuw";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    $titel = htmlspecialchars($_POST["titel"]); 
    
    if ($titel == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een titel in.";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    
     $bericht = htmlspecialchars($_POST["bericht"]); 
    
    if ($bericht == ""){
        $_SESSION["error-message"] = "";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    $nieuws_afbeelding_dir = "../nieuws-afbeeldingen/";
    $afbeelding_file = $nieuws_afbeelding_dir . basename($_FILES['nieuws-afbeelding']['name']);
    $afbeelding = null; 
    
    if (move_uploaded_file($_FILES['nieuws-afbeelding']['tmp_name'], $afbeelding_file)){
        $afbeelding = "/assets" . substr($afbeelding_file, 2);
    }
    
                  if ($afbeelding != null){           
    
                  $nieuws_sql = "UPDATE `" . $bedrijf . "` SET titel = ?, bericht = ?, afbeelding = ?, datum = now() WHERE id = ?";
    
                  $stmt = $link_nieuws->prepare($nieuws_sql);
                  $stmt->bind_param('sssi', $titel, $bericht, $afbeelding, $id);
                  $stmt->execute();
                      
                  }
    
                  else {
                      
                      $nieuws_sql = "UPDATE `" . $bedrijf . "` SET titel = ?, bericht = ?, datum = now() WHERE id = ?";
    
                  $stmt = $link_nieuws->prepare($nieuws_sql);
                  $stmt->bind_param('ssi', $titel, $bericht, $id);
                  $stmt->execute();
                      
                  }
    
          
        
    
    $_SESSION["error-message"] = "Bericht " . $titel . " aangepast voor " . $bedrijf;
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    
}
?>