<?php

/*
Gaat na of de ingelogde gebruiker toestemming voor de huidige pagina heeft.
Zo niet, wordt de gebruiker verwezen naar diens homepagina.
*/

session_start();

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 
$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

//Voorkomt dat ingelogde gebruikers op de login pagina terechtkomen

if ($data == "login" && (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false)){
    echo "good";
    $_SESSION["rol"] = null;
    exit();
}

//Voorkomt dat non resetlink aanvragen van de wachtwoord reset pagina succesvol zijn

elseif ($data == "nieuw-wachtwoord" && $_SESSION["rol"] != "ongeregistreerde_gebruiker"){
    echo "http://dummy.url/login";
    exit();
}

//Voorkomt dat niet ingelogde gebruikers op de paginas van de dashboard terechtkomen

elseif ((!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false) && $_SESSION["rol"] != "ongeregistreerde_gebruiker"){
    echo "http://dummy.url/login";
    exit();
}

$_SESSION["current-page"] = $data;

$gebruiker = ["home", "punten-overzicht", "nieuws", "kalender", "persoonsgegevens", "documentatie", "facturatie", "certificaten"];

$admin = ["admin-home", "admin-punten-overzicht", "admin-nieuws", "admin-kalender", "admin-persoonsgegevens", "registratie", "admin-documentatie", "admin-facturatie", "admin-certificaten"];

$ongeregistreerde_gebruiker = ["nieuw-wachtwoord"];
    
$allowed_slugs = $_SESSION["rol"];

//Als de gegeven pagina overeenkomt met toegestane pagina's gebeurt er niets, ander wordt de gebruiker verwezen naar diens home pagina

if (in_array($data, $$allowed_slugs)){
    echo "good";
    exit();
   }

else {
    echo "http://dummy.url/" . $$allowed_slugs[0];
    exit();
}

?>