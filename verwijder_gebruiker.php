<?php

/*
Verwijdert de account gekozen in de account tabel op de "registratie" pagina, tenzij deze gebruiker een admin is.
Indien een gebruiker, worden de gekoppelde tabellen in verschillende databases verwijderd voordat de gebruikersaccount wordt verwijderd.
Indien een admin, wordt aan de frontend een error bericht weergegeven die aangeeft dat admin accounts niet verwijderbaar zijn. 
*/

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

$gebruikersnaam = htmlspecialchars($data[0]);

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts);

$stmt = $link_accounts->prepare("SELECT rechten, bedrijf FROM dummy WHERE gebruiker = ?");
$stmt->bind_param("s", $gebruikersnaam);
$stmt->execute();
$result = $stmt->get_result();
$result = $result->fetch_all(MYSQLI_ASSOC);

if ($result[0]["rechten"] == 1){
    $bedrijf = $result[0]["bedrijf"];
    
    if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        header("Location: http://dummy.url/registratie");
        exit();
     }
    
    $link_cursus = new mysqli(host, database_cursus, password_cursus, database_cursus);
    $link_nieuws = new mysqli(host, database_nieuws, password_nieuws, database_nieuws);
    $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie); 
    
    $delete_sql = "DROP TABLE `$bedrijf`";
    
    $link_accounts->query($delete_sql);
    $link_cursus->query($delete_sql);
    $link_nieuws->query($delete_sql);
    $link_documentatie->query($delete_sql);
        
    $stmt = $link_accounts->prepare("DELETE FROM `dummy` WHERE gebruiker = ? AND bedrijf = ?");
    $stmt->bind_param("ss", $gebruikersnaam, $bedrijf);

    if ($stmt->execute()){
        $_SESSION["error-message"] = "Account van " . $gebruikersnaam . " verwijderd";
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
    $_SESSION["error-message"] = "Accounts van admins kunnen niet worden verwijderd";
            header("Location: http://dummy.url/registratie");
            exit();
}

?>