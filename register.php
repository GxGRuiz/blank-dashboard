<?php

/*
Gaat na of alle velden in het registratie formulier zijn ingevuld.
Daarna wordt gekeken of het bedrijf al in de account database staat.
Als dit niet het geval is worden alle gegevens opgeslagen en de logo geupload.
*/

session_start();
include_once("config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


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


$gebruikersnaam; $wachtwoord; $bevestig_wachtwoord; $email; $bedrijf; $rechten; $logo;



if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $gebruikersnaam = htmlspecialchars($_POST["gebruiker"]); 
    
    if ($gebruikersnaam == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een gebruikersnaam in.";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    
    $email = htmlspecialchars($_POST["email"]); 
    
    if ($email == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een email in.";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    
    $bedrijf = htmlspecialchars($_POST["bedrijf"]); 
    
    if ($bedrijf == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een bedrijf in.";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    if ($bedrijf == "dummy"){
        $_SESSION["error-message"] = "Bedrijf is al in gebruik, kies een andere bedrijfsnaam";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    if (bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Bedrijf is al in gebruik, kies een andere bedrijfsnaam";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    $temp_link = "http://dummy.url/" . random_str(8);
    
    
    $rechten = htmlspecialchars($_POST["rechten"]);
    
    if ($rechten == ""){
        $_SESSION["error-message"] = "Bevestig a.u.b het rechtenniveau";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    $toestemming = 0;
    
    if (isset($_POST["toestemming"]) && $_POST["toestemming"] == "on"){
        $toestemming = 1;
    }
    
    
    $logodir = "../logos/";
    $logofile = $logodir . basename($_FILES['logo']['name']);
    
    if ($_FILES['logo']['error'] == 2 || $_FILES['logo']['size'] > 1000000){
        $_SESSION["error-message"] = "De logo afbeelding moet kleiner dan 1MB zijn";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
     
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logofile)){
        $logo = "/assets" . substr($logofile, 2);
    }
    
    else {
        $logo = "/assets/logos/standaard-logo.jpg"; 
    }
    
    $profiel_fotos_dir = "../profiel-fotos/";
    $foto_file = $profiel_fotos_dir . basename($_FILES['profiel-foto']['name']);
    $foto = null; 
    
    
    if ($_FILES['profiel-foto']['error'] == 2 || $_FILES['profiel-foto']['size'] > 1000000){
        $_SESSION["error-message"] = "De profiel foto afbeelding moet kleiner dan 1MB zijn";
        header("Location: http://dummy.url/registratie");
        exit();
    }
    
    
    if (move_uploaded_file($_FILES['profiel-foto']['tmp_name'], $foto_file)){
        $foto = "/assets" . substr($foto_file, 2);
    }
    
    $stmt;
    
    if ($foto != null){  
        $stmt = $link->prepare("INSERT INTO dummy (gebruiker, email, bedrijf, logo, profiel_foto, rechten, toestemming, temp_link_verloopdatum, temp_wachtwoord_link) VALUES(?, ?, ?, ?, ?, ?, ?, DATE_ADD(now() , INTERVAL 3 DAY), ?)");
        $stmt->bind_param("sssssiis", $gebruikersnaam, $email, $bedrijf, $logo, $foto, $rechten, $toestemming, $temp_link);
    }
    
    else {
        $stmt = $link->prepare("INSERT INTO dummy (gebruiker, email, bedrijf, logo, rechten, toestemming, temp_link_verloopdatum, temp_wachtwoord_link) VALUES(?, ?, ?, ?, ?, ?, DATE_ADD(now() , INTERVAL 3 DAY), ?)");
        $stmt->bind_param("ssssiis", $gebruikersnaam, $email, $bedrijf, $logo, $rechten, $toestemming, $temp_link);
    }
        
    
        if ($stmt->execute()){
            $_SESSION["error-message"] = "Account " . $gebruikersnaam . " aangemaakt.";
            
            $link_cursussen = new mysqli(host, database_cursus, password_cursus, database_cursus);
            $link_nieuws = new mysqli(host, database_nieuws, password_nieuws, database_nieuws);
            $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);
            
            $bedrijf_sql = "CREATE TABLE IF NOT EXISTS `$bedrijf` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            roepnaam TEXT,
            achternaam TEXT,
            geboortedatum DATE,
            geboorteplaats TEXT,
            cbr INT(11),
            code_95 DATE,
            rijbewijs_c DATE,
            verloop_email_verzending BOOLEAN
            )";
            
            $cursussen_sql = "CREATE TABLE IF NOT EXISTS `$bedrijf` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            deelnemer_id INT(11),
            cursus_code TEXT,
            cursus_naam TEXT,
            behaald TINYINT(1),
            punten INT(11),
            verloopdatum DATE
            )";
            
            $nieuws_sql = "CREATE TABLE IF NOT EXISTS `$bedrijf` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            titel TEXT,
            bericht LONGTEXT,
            afbeelding MEDIUMTEXT,
            datum DATETIME
            )";
            
            $documentatie_sql = "CREATE TABLE IF NOT EXISTS `$bedrijf` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            doc_link MEDIUMTEXT,
            categorie TEXT,
            datum DATETIME
            )";
            
            
            $link->query($bedrijf_sql);
    
            $link_cursussen->query($cursussen_sql);
            
            $link_nieuws->query($nieuws_sql);
            
            $link_documentatie->query($documentatie_sql);
            
            $standaard_documentatie_sql = "INSERT INTO `" . $bedrijf . "` (doc_link, categorie, datum) VALUES(?, ?, now())";
            
            $standaard_documentatie_array = [["/assets/dummy categorie/dummy_doc.pdf", "dummy categorie"], ["/assets/dummy categorie/dummy_doc.pdf", "dummy categorie"], ["/assets/dummy categorie/dummy_doc.csv", "dummy categorie"], ["/assets/dummy categorie/dummy doc.csv", "dummy categorie"], ["/assets/dummy categorie/dummy doc.pdf", "dummy categorie"],["/assets/dummy categorie/dummy doc.pdf", "dummy categorie"]];
                
            for ($i = 0; $i < count($standaard_documentatie_array); $i++){
                 $stmt_documentatie = $link_documentatie->prepare($standaard_documentatie_sql);
                 
                 $stmt_documentatie->bind_param("ss", $standaard_documentatie_array[$i][0], $standaard_documentatie_array[$i][1]);
                
                 $stmt_documentatie->execute();
            }    
            
            $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress($email);
   $mail->Subject = "Welkomstmail";
   $mail->Body = '
   <div style="padding: 20px;">
   Welkomstbericht
   </div>';
   $mail->IsHTML(true);   
   
    $mail->isSendmail();
  
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
    header("Location: http://dummy.url/registratie");
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    header("Location: http://dummy.url/registratie");
    exit();
}
            
            header("Location: http://dummy.url/registratie");
            exit();
        }
    
        else {
            $_SESSION["error-message"] = $stmt->error;
            header("Location: http://dummy.url/registratie");
            exit();
        }
    
    
}
 
    header("Location: http://dummy.url/registratie");

?>