<?php

session_start();
include_once("config.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

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

$titel; $bericht; 

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    $titel = htmlspecialchars($_POST["titel"]); 
    
    if ($titel == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een titel in.";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    
     $bericht = htmlspecialchars($_POST["bericht"]); 
    
    if ($bericht == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een bericht in.";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    if (count($_POST) < 3){
        $_SESSION["error-message"] = "Kies a.u.b. ten minste één bedrijf";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    
    else {
        
    $nieuws_afbeelding_dir = "../nieuws-afbeeldingen/";
    $afbeelding_file = $nieuws_afbeelding_dir . basename($_FILES['nieuws-afbeelding']['name']);
    $afbeelding = null; 
    
    if (move_uploaded_file($_FILES['nieuws-afbeelding']['tmp_name'], $afbeelding_file)){
        $afbeelding = "/assets" . substr($afbeelding_file, 2);
    }
        
        if ($afbeelding != null){
        
          foreach ($_POST as $key => $value){
                  
                  if ($key == "titel" || $key == "bericht" || $key == "MAX_FILE_SIZE"){
                      continue;
                  }            
                  
                  if (!bedrijfValidatie(htmlspecialchars($value))){
                      $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
                      header("Location: http://dummy.url/admin-nieuws");
                      exit();
                     }
              
                  $nieuws_sql = "INSERT INTO `" . htmlspecialchars($value) . "` (titel, bericht, afbeelding, datum) VALUES(?, ?, ?, now())";
                  $stmt = $link_nieuws->prepare($nieuws_sql);
                  $stmt->bind_param('sss', $titel, $bericht, $afbeelding);
                  $stmt->execute();
              
                  $email_sql = "SELECT email, gebruiker FROM `dummy` WHERE bedrijf = ?";
                  $email_stmt = $link->prepare($email_sql);
                  $email_stmt->bind_param('s', htmlspecialchars($value));
                  $email_stmt->execute();   
                  
                  $result = $email_stmt->get_result();
                  $result = $result->fetch_all(MYSQLI_ASSOC);
                
                  $email = $result[0]["email"];
                  $gebruikersnaam = $result[0]["gebruiker"];
                
                  $mail_bericht = '
   <div style="padding: 20px;">
   Nieuwsbericht 
   </div>';
                 
           $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress($email);
   $mail->Subject = "Nieuwsbericht notificatie";
   $mail->Body = $mail_bericht;  
   $mail->IsHTML(true);   
   
    $mail->isSendmail();
  
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
    header("Location: http://dummy.url/admin-nieuws");
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    header("Location: http://dummy.url/admin-nieuws");
    exit();
}     
                 
          }
            
        }
        
        else{
            
            foreach ($_POST as $key => $value){
                   
                  if ($key == "titel" || $key == "bericht" || $key == "MAX_FILE_SIZE"){
                      continue;
                  }            
                  
                  $nieuws_sql = "INSERT INTO `" . htmlspecialchars($value) . "` (titel, bericht, datum) VALUES(?, ?, now())";
                  $stmt = $link_nieuws->prepare($nieuws_sql);
                  $stmt->bind_param('ss', $titel, $bericht);
                  $stmt->execute();
                
                  $email_sql = "SELECT email, gebruiker FROM `dummy` WHERE bedrijf = ?";
                  $email_stmt = $link->prepare($email_sql);
                  $email_stmt->bind_param('s', htmlspecialchars($value));
                  $email_stmt->execute();   
                  
                  $result = $email_stmt->get_result();
                  $result = $result->fetch_all(MYSQLI_ASSOC);
                
                  $email = $result[0]["email"];
                  $gebruikersnaam = $result[0]["gebruiker"];
                
                  $mail_bericht = '
                  <div style="padding: 20px;">
                  Nieuwsbericht 
                  </div>';
                 
           $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress($email);
   $mail->Subject = "Nieuwsbericht notificatie";
   $mail->Body = $mail_bericht;  
   $mail->IsHTML(true);   
   
    $mail->isSendmail();
  
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
    header("Location: http://dummy.url/admin-nieuws");
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    header("Location: http://dummy.url/admin-nieuws");
    exit();
}     
                
          }
        }
        
    }
    $_SESSION["error-message"] = "Nieuwsbericht voor geselecteerde bedrijven toegevoegd";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    
}


?>