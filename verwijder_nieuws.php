<?php 
/*
Verwijdert een nieuws bericht voor het geselecteerde bedrijf, mits de titel en bericht niet leeg zijn.
Geeft een error bericht op de userend als dit wel het geval is.
Indien het nieuwsbericht wordt verwijderd, geeft een error bericht aan over welk bericht en voor welk bedrijf het betreft.
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
        $_SESSION["error-message"] = "Vul a.u.b. een titel in.";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
    $bedrijf = htmlspecialchars($_POST["bedrijf"]);
    
     if ($bedrijf == ""){
        $_SESSION["error-message"] = "Vul a.u.b. een titel in.";
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
        $_SESSION["error-message"] = "Laat het veld";
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    }
    
              
                  $nieuws_sql = "DELETE FROM `" . $bedrijf . "` WHERE id = ? AND titel = ? AND bericht = ?";
    
                  $stmt = $link_nieuws->prepare($nieuws_sql);
                  $stmt->bind_param('iss', $id, $titel, $bericht);
                  $stmt->execute();
                  
          
        
    
    $_SESSION["error-message"] = "Bericht " . $titel . " verwijderd voor " . $bedrijf;
        header("Location: http://dummy.url/admin-nieuws");
        exit();
    
}

?>