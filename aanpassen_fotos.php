<?php

/*
Upload nieuwe afbeeldingen naar de profiel fotos en/of logos naar de bijhorende folders, mits er ten minste één afbeelding geupload wordt.
De profielfoto/logo (of beide) voor de gebruiker wordt bijgewerkt naar de geuploade bestand(en) en een error bericht geeft op de frontend aan welke is veranderd.
*/

session_start();
include_once("config.php");

$link = new mysqli(host, database_accounts, password_accounts, database_accounts); 

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

$gebruikersnaam = $_SESSION["gebruiker"];

$logo = null; $foto = null;

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    //var_dump($_FILES);
    //exit();
    
    
    $return_page = "http://dummy.url/" . $_POST["extra-data"];
    

    $logodir = "../logos/";
    $logofile = $logodir . basename($_FILES['logo']['name']);
    
     
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logofile)){
        $logo = "/assets" . substr($logofile, 2);
    }
    
    
    $profiel_fotos_dir = "../profiel-fotos/";
    $foto_file = $profiel_fotos_dir . basename($_FILES['profiel-foto']['name']);
    
    if (move_uploaded_file($_FILES['profiel-foto']['tmp_name'], $foto_file)){
        $foto = "/assets" . substr($foto_file, 2);
    }
    
    
    if ($foto == null && $logo == null){
        $_SESSION["error-message"] = "Voeg a.u.b. ten minste één afbeelding toe";
        header("Location: " . $return_page);
        exit();
    }
    
    
    if ($foto == null && $logo != null){  
        $stmt = $link->prepare("UPDATE dummy SET logo = ? WHERE gebruiker = ?");
        $stmt->bind_param("ss", $logo, $gebruikersnaam);
        $stmt->execute();
        
        $_SESSION["error-message"] = "Logo afbeelding aangepast";
        header("Location: " . $return_page);
        exit();
    }
    
    elseif ($logo == null && $foto != null){
        $stmt = $link->prepare("UPDATE dummy SET profiel_foto = ? WHERE gebruiker = ?");
        $stmt->bind_param("ss", $foto, $gebruikersnaam);
        $stmt->execute();
        
        $_SESSION["error-message"] = "Profiel foto aangepast";
        header("Location: " . $return_page);
        exit();
    }
    
    else {
        $stmt = $link->prepare("UPDATE dummy SET logo = ?, profiel_foto = ? WHERE gebruiker = ?");
        $stmt->bind_param("sss", $logo, $foto, $gebruikersnaam);
        $stmt->execute();
        
        $_SESSION["error-message"] = "Profiel foto en logo afbeelding aangepast";
        header("Location: " . $return_page);
        exit();
    }
    
}

?>