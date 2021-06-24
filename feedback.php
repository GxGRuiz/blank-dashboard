<?php

session_start();

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    header("Location: http://dummy.url/login");
    exit();
}

include_once("config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    $categorie;
   
    switch($_POST["categorie"]){
       
           case "Kies een optie":
                $_SESSION["error-message"] = "Kies a.u.b. een categorie";
                header("Location: http://dummy.url/" . $_POST["extra-data"]);
                exit();
                break;
        
           case "Suggestie":
                $categorie = $_POST["categorie"];
                break;
           
           case "Foutmelding":
                $categorie = $_POST["categorie"];
                break;
           
           default:
                   $_SESSION["error-message"] = "Kies a.u.b. een categorie";
                   header("Location: http://dummy.url/" . $_POST["extra-data"]);
                   exit();
                   break;
   }    
     
    if ($_POST["bericht"] == ""){
        $_SESSION["error-message"] = "Typ a.u.b. uw bericht";
        header("Location: http://dummy.url/" . $_POST["extra-data"]);
                   exit();
                   
    }
    
    $bericht = htmlspecialchars($_POST["bericht"]);
    
    $attachment = null;
    
    
    
    $ext = PHPMailer::mb_pathinfo($_FILES['feedback-bijlage']['name'], PATHINFO_EXTENSION);
    //Define a safe location to move the uploaded file to, preserving the extension
    $uploadfile = tempnam(sys_get_temp_dir(), hash('sha256', $_FILES['feedback-bijlage']['name'])) . '.' . $ext;

    if (move_uploaded_file($_FILES['feedback-bijlage']['tmp_name'], $uploadfile)) {
        $attachment = $uploadfile;
    }
    
    $link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts);
    $stmt = $link_accounts->prepare("SELECT email FROM `dummy` WHERE gebruiker = ?");
    $stmt->bind_param("s", $_SESSION["gebruiker"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);
    
    $email = $result[0]["email"];
    
    
    
    
    $subject = $categorie;
    $message = $bericht;
    
    

$mail = new PHPMailer(TRUE);

try {
   
   $mail->setFrom($email);
   $mail->addAddress('dummy@dummy.com');
   $mail->Subject = $categorie;
   $mail->Body = $bericht;
   
   if ($attachment != null){
       $mail->addAttachment($attachment);
   }    
   
    $mail->isSendmail();
   
   $mail->send();
}
catch (Exception $e)
{
    $_SESSION["error-message"] = $e->errorMessage() . $mail->ErrorInfo;
    header("Location: http://dummy.url/" . $_POST["extra-data"]);
    exit();
}
catch (\Exception $e)
{
    $_SESSION["error-message"] = $e->getMessage();
    header("Location: http://dummy.url/" . $_POST["extra-data"]);
    exit();
}   
    
    $_SESSION["error-message"] = "Feedback verzonden";
        header("Location: http://dummy.url/" . $_POST["extra-data"]);
        
        exit();

   }


?>