<?php

/*
Gaat het verstuurde formulier op de pagina "nieuw-wachtwoord" na.
Kijkt of het wachtwoord veld en bevestig wachtwoord veld overeenkomen.
Zo ja, wordt het wachtwoord voor de betreffende gebruiker aangepast en de gebruiker naar de login pagina gestuurd.
Zo niet, wordt de gebruiker naar "nieuw-wachtwoord" gestuurd met een error bericht.
*/

session_start();
define('host', 'localhost');
define('username', 'dummy');
define('password_websiteDB', 'filltheblank');
define('password_accounts', 'filltheblank');
define('password_cursus', 'filltheblank');
define('password_nieuws', 'filltheblank');
define('password_documentatie', 'filltheblank');
define('database_accounts', 'dummy_bedrijven');
define('database_webpages', 'dummy_websiteDB');
define('database_cursus', 'dummy_cursussen');
define('database_nieuws', 'dummy_nieuws');
define('database_documentatie', 'dummy_documentatie');



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

if ($_SESSION["rol"] != "ongeregistreerde_gebruiker" || !isset($_SESSION["rol"])){
    header("Location: http://dummy.url/login");
    exit();
}

$gebruikersnaam = $_SESSION["gebruiker"];
$bedrijf = $_SESSION["bedrijf"];

$wachtwoord; $bevestig_wachtwoord;
 


if ($_SERVER["REQUEST_METHOD"] === "POST"){
   
    $wachtwoord = htmlspecialchars($_POST['wachtwoord']);
    
    if ($wachtwoord == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een wachtwoord in.";
        header("Location: http://dummy.url/nieuw-wachtwoord");
        exit();
    }
    
     $bevestig_wachtwoord = htmlspecialchars($_POST['bevestig-wachtwoord']);
    
    if ($bevestig_wachtwoord == ""){
        $_SESSION["error-message"] = "Bevestig a.u.b het wachtwoord";
        header("Location: http://dummy.url/nieuw-wachtwoord");
        exit();
    }
    
    if ($bevestig_wachtwoord != $wachtwoord){
        $_SESSION["error-message"] = "Wachtwoorden komen niet overeen.";
        header("Location: http://dummy.url/nieuw-wachtwoord");
        exit();
    }
    
 
        $stmt = $link->prepare("UPDATE dummy SET wachtwoord = ?, temp_link_verloopdatum = ?, temp_wachtwoord_link = ?  WHERE gebruiker = ? AND bedrijf = ?");
        $stmt->bind_param("sssss", password_hash($wachtwoord, PASSWORD_DEFAULT), $temp_valid, $temp_valid, $gebruikersnaam, $bedrijf);
    
        $temp_valid = null;
    
        if ($stmt->execute()){ 
            $_SESSION = array();
        $_SESSION["error-message"] = "Wachtwoord gewijzigd, log opnieuw in om het dashboard te gebruiken";
            header("Location: http://dummy.url/login");
            exit();
        }
    
        else {
             $_SESSION["error-message"] = "Er ging iets mis, probeer het opnieuw of neem contact met de beheerder";
            header("Location: http://dummy.url/nieuw-wachtwoord");
            exit();
        }
}
 
    header("Location: http://dummy.url/nieuw-wachtwoord");

?>