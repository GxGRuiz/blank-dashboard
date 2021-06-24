<?php

/*
Uploadt en leest een csv file om persoonsgegevens toe te voegen of wijzigen voor het geselecteerde bedrijf.
Gaat eerst na of de eerste rij met veldnamen kloppen, geeft een error bericht weer als dit niet het geval is.
Gaat dan na of de medewerker in kwestie al bestaat, voegt deze toe indien nee of past de bijhorende gegevens indien ja.
Geeft tot slot aan voor welk bedrijf gegevens zijn bijgewerkt op de frontend.
*/

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
            exit;
          }
        
        if (!bedrijfValidatie(htmlspecialchars($bedrijf))){
            $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
            header("Location: http://dummy.url/" . $redirect_pagina);
            exit();
                     }
    
    }
    
    else {
        $bedrijf = $_SESSION["bedrijf"];
    }
    
    if (pathinfo($_FILES["medewerker-data"]["name"])["extension"] != "csv"){
        $_SESSION["error-message"] = "Kies a.u.b. een csv bestand met persoonsgegevens";
        header("Location: http://dummy.url/" . $redirect_pagina);
        exit;   
    }
    
    if ($_FILES["medewerker-data"]["size"] > 10000000){
        $_SESSION["error-message"] = "Uw bestand is groter dan 10MB, pas deze a.u.b. aan";
        header("Location: http://dummy.url/" . $redirect_pagina);
        exit();   
    } 
    
    $uploaddir = '../uploads/';
$uploadfile = $uploaddir . basename($_FILES['medewerker-data']['name']);

    
if (move_uploaded_file($_FILES['medewerker-data']['tmp_name'], $uploadfile)) {
    $test = fopen($uploadfile, 'r');

    $row = 1;
    $veld_namen = [];
    $link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts); 
    
    
    while (($content = fgetcsv($test)) !== FALSE) {
        $content = explode(";", $content[0]);
        $num = count($content);
        
        if ($row > 1){
            
            $nieuwe_rij = [];
            
            for ($c=0; $c < $num; $c++) {
                 
                 $nieuwe_rij[] = trim($content[$c]);
                }
            
            $stmt = $link_accounts->prepare("SELECT * FROM `" . $bedrijf . "` WHERE roepnaam = ? AND achternaam = ? AND geboortedatum = ? AND geboorteplaats = ?");
            
            $result = "";
            $roepnaam = $nieuwe_rij[0];
            $tussenvoegsel = $nieuwe_rij[1];
            $achternaam = $nieuwe_rij[2];
            $geboortedatum = date('Y-m-d', strtotime($nieuwe_rij[3]));
            $geboorteplaats = $nieuwe_rij[4];
            
            if ($tussenvoegsel != ""){
                $achternaam .= ", " . $tussenvoegsel;
            }
                
            if ($stmt){
            
            $stmt->bind_param("ssss", $roepnaam, $achternaam, $geboortedatum, $geboorteplaats);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_all(MYSQLI_ASSOC);
                
            }
            
            if (is_array($result) && count($result) != 0){
            
                $stmt = $link_accounts->prepare("UPDATE `" . $bedrijf . "` SET cbr = ?, code_95 = ?, rijbewijs_c = ? WHERE roepnaam = ? AND achternaam = ? AND geboortedatum = ? AND geboorteplaats = ?");
            $stmt->bind_param('sssssss', $kandidaat_nummer_cbr, $verloopdatum_code_95, $verloopdatum_rijbewijs_c, $roepnaam, $achternaam, $geboortedatum, $geboorteplaats);
            
             $kandidaat_nummer_cbr = $nieuwe_rij[5];    
            $verloopdatum_code_95 = date('Y-m-d', strtotime($nieuwe_rij[6]));
            $verloopdatum_rijbewijs_c = date('Y-m-d', strtotime($nieuwe_rij[7]));
            
            $stmt->execute();
                $_SESSION["error-message"] .= $roepnaam . " aangepast<br>";
               }
            
            else{
                
            $stmt = $link_accounts->prepare("INSERT INTO `" . $bedrijf . "` (roepnaam, achternaam, geboortedatum, geboorteplaats, cbr, code_95, rijbewijs_c) VALUES(?, ?, ?, ?, ?, ?, ?)");  
            $stmt->bind_param('sssssss', $roepnaam, $achternaam, $geboortedatum, $geboorteplaats, $kandidaat_nummer_cbr, $verloopdatum_code_95, $verloopdatum_rijbewijs_c);
            
            $kandidaat_nummer_cbr = $nieuwe_rij[5];    
            $verloopdatum_code_95 = date('Y-m-d', strtotime($nieuwe_rij[6]));
            $verloopdatum_rijbewijs_c = date('Y-m-d', strtotime($nieuwe_rij[7]));
            
            $stmt->execute();
                $_SESSION["error-message"] .= $roepnaam . " " . $achternaam . " toegevoegd<br>";
            }
        }
        
        else {
            
              for ($c=0; $c < $num; $c++) {
                   $veld_namen[] = strtolower(trim($content[$c]));
                  }
            
            if ($veld_namen[0] === "roepnaam" && $veld_namen[1] === "tussenvoegsel" && $veld_namen[2] === "achternaam" && $veld_namen[3] === "geboortedatum" && $veld_namen[4] === "geboorteplaats" && $veld_namen[5] === "cbr nummer" && $veld_namen[6] === "verloopdatum code 95" && $veld_namen[7] === "verloopdatum rijbewijs c"){
                $good = "to go";
            }
            
            else {
                $_SESSION["error-message"] = "Veldnamen zijn onjuist, pas deze in de file aan.";
                header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
            }
            
        }
        
        
        $row++;
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

 $_SESSION["error-message"] .= "Persoonsgegevens voor " . $bedrijf . " bijgewerkt";
header("Location: http://dummy.url/" . $redirect_pagina);
                exit();
?>