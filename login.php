<?php

/*
Gaat de login gegevens met die in de database na.
Gaat naar de correcte dashboard indien deze overeen komen.
Anders terug naar de login pagina met een error bericht.
*/

define('ROOT', __DIR__);

$lifetime=600;
session_start();
define('host', 'localhost');
define('username', 'dummy');
define('password_websiteDB', 'filltheblank');
define('password_accounts', 'filltheblank');
define('password_cursus', 'filltheblank');
define('password_nieuws', 'filltheblank)');
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

$gebruikersnaam; $wachtwoord;

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $gebruikersnaam = htmlspecialchars($_POST["gebruikersnaam"]); 
    
    if ($gebruikersnaam == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een gebruikersnaam in.";
        header("Location: http://dummy.url/login");
        exit();
    }
    
    $wachtwoord = htmlspecialchars($_POST['wachtwoord']);
    
    if ($wachtwoord == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een wachtwoord in.";
        header("Location: http://dummy.url/login");
        exit();
    }
    
    
        $stmt = $link->prepare("SELECT gebruiker, wachtwoord, bedrijf, rechten, temp_link_verloopdatum, temp_wachtwoord_link FROM dummy WHERE gebruiker = ?");
        $stmt->bind_param("s", $gebruikersnaam);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 
    
        if ($result[0]["temp_link_verloopdatum"] != null || $result[0]["wachtwoord"] == null){
               
                $_SESSION["error-message"] = "Uw wachtwoord is nog niet ingesteld, doe dit via de link in de registratie email";
                header("Location: http://dummy.url/login");
                exit();
            }
    
        if (password_verify($wachtwoord, $result[0]['wachtwoord'])){
            switch ($result[0]['rechten']){
                case 1:
                    $_SESSION["rol"] = "gebruiker";
                    break;
                case 2:
                    $_SESSION["rol"] = "admin";
                    break;
                default:
                     $_SESSION["error-message"] = "Uw rol is niet ingesteld. Neem contact op met de beheerder.";
                     header("Location: http://dummy.url/login");
                     exit();
            }
    
            
            $_SESSION["logged-in"] = true;
            $_SESSION["gebruiker"] = $gebruikersnaam;
            $_SESSION["bedrijf"] = $result[0]['bedrijf'];
            
            setcookie(session_name(),session_id(),time()+$lifetime);
            header("Location: http://dummy.url/home");
            exit();
        }
    
        else {
            $_SESSION["error-message"] = "Uw gebruikersnaam of wachtwoord is incorrect. Ga deze na en probeer het opnieuw.";
            header("Location: http://dummy.url/login");
            exit();
        }
    
    
}
 
    header("Location: http://dummy.url/login");

?>