<?php

/*
Uploadt en leest een csv file om cursussen toe te voegen of wijzigen voor het geselecteerde bedrijf.
*/

define('ROOT', __DIR__);

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);


include_once("config.php");


if ($_SERVER["REQUEST_METHOD"] === "POST"){
    
    $bedrijf;
    $redirect_pagina;
    
    if ($_SESSION["rol"] == "admin"){
        $redirect_pagina = "admin-persoonsgegevens";
    }
    
    else {
        $redirect_pagina = "persoonsgegevens";
    }
    
    if (isset($_POST["bedrijf"])){
    
        $bedrijf = htmlspecialchars($_POST["bedrijf"]);
    
        if ($bedrijf == ""){
            $_SESSION["error-message"] = "Kies a.u.b een bedrijf.";
            header("Location: http://dummy.url/" . $redirect_pagina);
            exit();
          }
        
        if(!bedrijfValidatie($bedrijf)){
             $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet.";
            header("Location: http://dummy.url/" . $redirect_pagina);
            exit();
        }
    
    }
    
    else {
        $bedrijf = $_SESSION["bedrijf"];
    }
    
   
   if (pathinfo($_FILES["medewerker-data"]["name"])["extension"] != "csv"){
        $_SESSION["error-message"] = "Kies a.u.b. een csv bestand met cursussen";
        header("Location: http://dummy.url/" . $redirect_pagina);
        exit();   
    }
    
    if ($_FILES["medewerker-data"]["size"] > 10000000){
        $_SESSION["error-message"] = "Uw bestand is groter dan 10MB, pas deze a.u.b. aan";
        header("Location: http://dummy.url/" . $redirect_pagina);
        exit();   
    } 

    $uploaddir = "../uploads/";
$uploadfile = $uploaddir . basename($_FILES['medewerker-data']['name']);

    
if (move_uploaded_file($_FILES['medewerker-data']['tmp_name'], $uploadfile)) {
    $test = fopen($uploadfile, 'r');

    $row = 1;
    $veld_namen = [];
    $link_cursus = new mysqli(host, database_cursus, password_cursus, database_cursus); 
    $link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts);
    
    
    
    while (($content = fgetcsv($test)) !== FALSE) {
       
                $content = explode(";", $content[0]);
        $num = count($content);
        
       
        if ($row > 1){
            
            $nieuwe_rij = [];
            
            for ($c=0; $c < $num; $c++) {
                 //echo $content[$c] . "<br />\n";
                 $nieuwe_rij[] = trim($content[$c]);
                }
            
            $stmt = $link_accounts->prepare("SELECT id FROM `" . $bedrijf . "` WHERE roepnaam = ? AND achternaam = ?");
            $stmt->bind_param("ss", $roepnaam, $achternaam);
            
            $roepnaam = $nieuwe_rij[0];
            $tussenvoegsel = $nieuwe_rij[1];
            $achternaam = $nieuwe_rij[2];
            
            if ($tussenvoegsel != ""){
                $achternaam .= ", " . $tussenvoegsel;
            }
           
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_all(MYSQLI_ASSOC);
            
            if (count($result) != 0){
            $id = $result[0]["id"];
            $cursus_code = $nieuwe_rij[3];
            $cursus_naam = $nieuwe_rij[4];
            }
            
            else {
                $_SESSION["error-message"] = $roepnaam . ", " . $achternaam . " is niet geregistreerd voor " . $bedrijf;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
            }
            
            
            
            /*if ($cursus_code != "" && $cursus_naam != ""){
                
                if (cursusCheck([$cursus_code, $cursus_naam]) === true){
                    $good = "to go";
                }
                
                elseif (!cursusCheck([$cursus_code, $cursus_naam])){
                    $_SESSION["error-message"] = "Cursus code " . $cursus_code . " en cursus naam " . $cursus_naam . " zijn niet gevonden voor " . $roepnaam . ", " . $achternaam;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
                }
                
                else {
                    $_SESSION["error-message"] = cursusCheck([$cursus_code, $cursus_naam]) . " voor " . $roepnaam . ", " . $achternaam;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
                }
                
                
            }
            
            elseif ($cursus_code != ""){
                
                if (!cursusCheck($cursus_code)){
                    $_SESSION["error-message"] = "Geen cursus naam gevonden voor cursus code " . $cursus_code . " voor " . $roepnaam . ", " . $achternaam;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
                }
                
                else {
                    $cursus_naam = cursusCheck($cursus_code);
                }
                
            }
            
            elseif ($cursus_naam != ""){
                
                if (!cursusCheck($cursus_naam)){
                    $_SESSION["error-message"] = "Geen cursus code gevonden voor cursus naam " . $cursus_naam . " voor " . $roepnaam . ", " . $achternaam;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
                }
                
                else {
                    $cursus_code = cursusCheck($cursus_naam);
                }
                
            }
            
            else {
                $_SESSION["error-message"] = "Vul a.u.b. ten minste een cursus code of cursus naam in voor " . $roepnaam . ", " . $achternaam;
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
            }*/
            
            
            $stmt = $link_cursus->prepare("SELECT * FROM `" . $bedrijf . "` WHERE deelnemer_id = ? AND cursus_code = ? AND cursus_naam = ?");
            
            $result = "";
            
            // Gaat na of de cursus voor de betreffende persoon bestaat
            
            if ($stmt){
            $stmt->bind_param("sss", $id, $cursus_code, $cursus_naam);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_all(MYSQLI_ASSOC);
              
            }
            
            
            if (is_array($result) && count($result) != 0){
            
                $stmt = $link_cursus->prepare("UPDATE `" . $bedrijf . "` SET behaald = ?, punten = ?, verloopdatum = ? WHERE deelnemer_id = ? AND cursus_code = ? AND cursus_naam = ?");
            $stmt->bind_param('iissss', $behaald, $punten, $verloopdatum, $id, $cursus_code, $cursus_naam);
            
            $punten = $nieuwe_rij[5];
            $verloopdatum = date('Y-m-d', strtotime($nieuwe_rij[6]));
            $behaald;
                
            if ($punten == 7){
                $behaald = 1;
            }
            
            else {
                $behaald = 0;
            }
            
            $stmt->execute();
               }
            
            else{
                
            $stmt = $link_cursus->prepare("INSERT INTO `" . $bedrijf . "` (deelnemer_id, cursus_code, cursus_naam, behaald, punten, verloopdatum) VALUES(?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssiis', $id, $cursus_code, $cursus_naam, $behaald, $punten, $verloopdatum);
            
            $punten = $nieuwe_rij[5];
            $verloopdatum = date('Y-m-d', strtotime($nieuwe_rij[6]));
            $behaald;
            
            if ($punten == 7){
                $behaald = 1;
            }
            
            else {
                $behaald = 0;
            }
            
            $stmt->execute();
            
            }
        }
        
        else {
            
              for ($c=0; $c < $num; $c++) {
                   $veld_namen[] = strtolower(trim($content[$c]));
                  }
            
              if ($veld_namen[0] === "roepnaam" && $veld_namen[1] === "tussenvoegsel" && $veld_namen[2] === "achternaam" && $veld_namen[3] === "cursus code" && $veld_namen[4] === "cursus naam" && $veld_namen[5] === "punten" && $veld_namen[6] === "verloopdatum"){
                $good = "to go";
            }
            
            else {
                $_SESSION["error-message"] = "Veldnamen in dit bestand zijn onjuist, zie de uitleg voor meer informatie";
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
            }
            
        }
        
        
        $row++;
    }

    $link_cursus = new mysqli(host, database_cursus, password_cursus, database_cursus);
    $check_sql = "SELECT bedrijf FROM laatste_wijziging WHERE bedrijf = '$bedrijf'";
    
    $stmt = $link_cursus->query($check_sql)->fetch_all(MYSQLI_ASSOC);

    if ($stmt){
        $update_sql = "UPDATE laatste_wijziging SET `update` = now() WHERE bedrijf = '$bedrijf'";
        $link_cursus->query($update_sql);
    }
    
    else {
        $update_sql = "INSERT INTO laatste_wijziging (bedrijf, `update`) VALUES('$bedrijf', now())";
        $link_cursus->query($update_sql);
    }
    
} else {
    $_SESSION["error-message"] = "Er ging iets fout, neem contact met de beheerder.";
    header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
}


}

else {
      $_SESSION["error-message"] = "Er ging iets fout met het verwerken van het bestand, probeer het (later) opnieuw.";
    header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
}


 $_SESSION["error-message"] = "Cursussen voor " . $bedrijf . " bijgewerkt";
header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
?>