<?php

include_once("config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';


$link_accounts = new mysqli (host, database_accounts, password_accounts, database_accounts);
$link_cursus = new mysqli (host, database_cursus, password_cursus, database_cursus);

    
$result = $link_accounts->query("SELECT gebruiker, email, bedrijf FROM `dummy`")->fetch_all(MYSQLI_ASSOC);

$notif_mail_array = [];
for ($i = 0; $i < count($result); $i++){
     $bedrijf_naam = $result[$i]["bedrijf"];
     $$bedrijf_naam = [];
    
     $rijbewijs_data_check = $link_accounts->query("SELECT id, roepnaam, achternaam, rijbewijs_c FROM `" . $bedrijf_naam . "` WHERE verloop_email_verzending IS NULL")->fetch_all(MYSQLI_ASSOC);
    
    
    for ($j = 0; $j < count($rijbewijs_data_check); $j++){
         
        if (dateDiff($rijbewijs_data_check[$j]["rijbewijs_c"]) < 180){
            
            /*$email_pending_stmt = $link_accounts->prepare("UPDATE `" . $result[$i]["bedrijf"] . "` SET verloop_email_verzending = 0 WHERE id = ? AND roepnaam = ? AND achternaam = ?");
            $email_pending_stmt->bind_param("iss", $rijbewijs_data_check[$j]["id"], $rijbewijs_data_check[$j]["roepnaam"], $rijbewijs_data_check[$j]["achternaam"]);
            
            $email_pending_stmt->execute();*/
            
            array_push($$bedrijf_naam, [$rijbewijs_data_check[$j]["id"], $rijbewijs_data_check[$j]["roepnaam"], $rijbewijs_data_check[$j]["achternaam"], $rijbewijs_data_check[$j]["rijbewijs_c"], $result[$i]["email"], $result[$i]["gebruiker"]]);
        }
        
        else {
            
            continue;
            
        }
        
    }
    
    $notif_mail_array[$bedrijf_naam] = $$bedrijf_naam;
}


foreach ($notif_mail_array as $bedrijf => $verloop_data){
         
         $arr_length = count($verloop_data); 
    
         if ($arr_length > 0){
             
             $mail_bericht_bedrijf = $mail_bericht_beheerder = '
   <div>';
   
   $mail_bericht_beheerder .= "<p>" . $bedrijf . "<br><ul>";         
             
             $betreffende_deelnemers = "<br>
             <ul>";
             
             $email; 
             $gebruikersnaam;
             
             for ($i = 0; $i < $arr_length; $i++){
                 
                 $email_sent_stmt = $link_accounts->prepare("UPDATE `" . $bedrijf . "` SET verloop_email_verzending = 1 WHERE id = ? AND roepnaam = ? AND achternaam = ?");
                 
                 $email_sent_stmt->bind_param("iss", $verloop_data[$i][0], $verloop_data[$i][1], $verloop_data[$i][2]);
                 
                 $email_sent_stmt->execute();
                 
                 $mail_bericht_beheerder .= "<li>" . $verloop_data[$i][1] . " " . $verloop_data[$i][2] . ": " . $verloop_data[$i][3] . "</li>"; 
                  
                 if (dateDiff($verloop_data[$i][3]) < 0){
                     $betreffende_deelnemers .= "<li>" . $verloop_data[$i][1] . " " . $verloop_data[$i][2] . ": " . $verloop_data[$i][3] . "</li>"; 
                
                     
                     $email = $verloop_data[$i][4];
                     $gebruikersnaam =  $verloop_data[$i][5];
                 }
                 
                 else {
                     $betreffende_deelnemers .= "<li>" . $verloop_data[$i][1] . " " . $verloop_data[$i][2] . ": " . $verloop_data[$i][3] . "</li>";
                     
                     
                     $email = $verloop_data[$i][4];
                     $gebruikersnaam =  $verloop_data[$i][5];
                 }
                 
             }
             
            $mail_bericht_beheerder .= "</ul></p>
            
            <p> " . $bedrijf . "</p>
            "; 
             
            $mail_bericht_bedrijf .=  '<p>' . $bedrijf . ',</p><br>
            
            
   
   <p>
   ' . $betreffende_deelnemers . '
   </p>
   
   ';;
             
             
             
            
             
            $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress($email);
   $mail->Subject = "Verlopende rijbewijzen";
   $mail->Body = $mail_bericht_bedrijf;  
   $mail->IsHTML(true);   
   
    $mail->isSendmail();
  
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
    //header("Location: http://dummy.url/registratie");
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    //header("Location: http://dummy.url/registratie");
    exit();
}
             
             
 $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress('dummy@dummy.com');
   $mail->Subject = "Verloopmail";
   $mail->Body = $mail_bericht_beheerder;  
   $mail->IsHTML(true);   
   
    $mail->isSendmail();
  
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
   
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    
    exit();
}             
             
         }
    
         else {
             continue;
         }
             
             
}


?>