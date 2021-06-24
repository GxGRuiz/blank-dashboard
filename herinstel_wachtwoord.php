<?php
session_start();
header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

if ($_SESSION["rol"] != "admin"){
    header("Location: http://dummy.url/home");
    exit();
}

include_once("config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$gebruikersnaam = htmlspecialchars($data[0]);

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts);

$stmt = $link_accounts->prepare("SELECT email, bedrijf FROM dummy WHERE gebruiker = ?");
$stmt->bind_param("s", $gebruikersnaam);

if($stmt->execute()){

$result = $stmt->get_result();
$result = $result->fetch_all(MYSQLI_ASSOC);

$email = $result[0]["email"];
$bedrijf = $result[0]["bedrijf"];    

$temp_link = "http://dummy.url/" . random_str(8);  
    
$wachtwoord_reset = null;     
    
$stmt = $link_accounts->prepare("UPDATE dummy SET wachtwoord = ?, temp_link_verloopdatum = DATE_ADD(now() , INTERVAL 3 DAY), temp_wachtwoord_link = ? WHERE gebruiker = ?");
$stmt->bind_param("sss", $wachtwoord_reset, $temp_link, $gebruikersnaam);
    
if ($stmt->execute()){    
    
      $mail = new PHPMailer(TRUE);
            $mail->CharSet = "UTF-8";

try {
   
   $mail->setFrom('info@dummy.nl');
   $mail->addAddress($email);
   $mail->Subject = "Wachtwoord opnieuw instellen";
   $mail->Body = '
  <div style="padding: 20px;">
   <p><a href="https://www.dummy.nl"><img width="249" height="50" src="https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-249x50.png" class="custom-logo" alt="dummy" loading="lazy" srcset="https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-249x50.png 249w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-300x60.png 300w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-768x153.png 768w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB.png 862w" sizes="(max-width: 249px) 100vw, 249px" style="margin:0 auto;"></a></p><br>
   <div style="font-family: Calibri, sans-serif; font-size: 11px;">
   <p>Beste ' . $bedrijf . ',</p><br>
   
   <p>Uw wachtwoord moet (opnieuw) ingesteld worden, volg de link hieronder om uw wachtwoord in te stellen</p>
   
   <p>
   <a href="' . $temp_link . '">Klik hier</a>
   </p>
   
   <p>Heeft u vragen over het dashboard of wilt u wat toelichting? Neem gerust contact op.</p><br>
   
   <p>
   Met vriendelijke groet,<br>
Esther Hendriks<br>
Mob: +31 6 271 160 76
   </p><br>
   <p><a href="https://www.dummy.nl"><img width="249" height="50" src="https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-249x50.png" class="custom-logo" alt="dummy" loading="lazy" srcset="https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-249x50.png 249w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-300x60.png 300w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB-768x153.png 768w, https://dummy.nl/wp-content/uploads/2019/07/cropped-logo-RB.png 862w" sizes="(max-width: 249px) 100vw, 249px" style="margin:0 auto;"></a></p>
   
   </div>
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
    
    $_SESSION["error-message"] = "Wachtwoord herinstelling email voor " . $gebruikersnaam . " verzonden";
    header("Location: http://dummy.url/registratie");
    exit();
}
    
    else {
        $_SESSION["error-message"] = $stmt->error;
    header("Location: http://dummy.url/registratie");
    exit();
    }
    
}

else {
    $_SESSION["error-message"] = "Account bestaat niet meer";
    header("Location: http://dummy.url/registratie");
    exit();
}
?>