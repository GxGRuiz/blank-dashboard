<?php

/*
Roept behaalde cursussen van het geselecteerde bedrijf aan en maakt hiervan een puntentabeloverzicht die aan de frontend wordt weergegeven.
*/

define('ROOT', __DIR__);

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 
$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);


if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

include_once("config.php");

$limit = htmlspecialchars($data[0]);

if ($limit == ""){
    $limit = 10;
}

$offset = (intval(htmlspecialchars($data[1])) - 1) * $limit;

if ($offset == "" || $offset < 0){
    $offset = 0;
}

$bedrijf;

if ($data[2] && $_SESSION["rol"] == "admin"){
    $bedrijf = htmlspecialchars($data[2]);
    
    if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        exit();
    }
}

else {
    $bedrijf = htmlspecialchars($_SESSION['bedrijf']);
}



$overzicht = "<table><thead><tr><th>Medewerker</th><th>Cursus Code</th><th>Cursus Naam</th><th>Punten</th><th>Verloopdatum Cursus/Code 95</th><th>Verloopdatum Rijbewijs C</th><th>Totaal Theorie</th><th>Totaal Praktijk</th></tr></thead><tbody>";

$link = new mysqli(host, database_cursus, password_cursus, database_cursus);

$link_persoongegevens = new mysqli(host, database_accounts, password_accounts, database_accounts);

$paginas;

$stmt = $link_persoongegevens->query("SELECT * FROM `$bedrijf`");

if ($stmt){
    $paginas = ceil(count($stmt->fetch_all(MYSQLI_ASSOC)) / $limit);
}


$result = $link_persoongegevens->query("SELECT id, roepnaam, achternaam, code_95, rijbewijs_c FROM `" . $bedrijf . "` ORDER BY achternaam ASC LIMIT " . $limit . " OFFSET " . $offset . "")->fetch_all(MYSQLI_ASSOC);

if ($result){
    $table_rows = [];
    

    $cursussen = $link->query("SELECT deelnemer_id, cursus_code, cursus_naam, behaald, punten, verloopdatum FROM `" . $bedrijf . "`")->fetch_all(MYSQLI_ASSOC);


        for ($i = 0; $i < count($result); $i++){

             $huidige_id = $result[$i]["id"];
             $rowspan = 0;
             $totaal_praktijk = 0;
             $totaal_theorie = 0;
             $loop_check = 0;

             for ($j = 0; $j < count($cursussen); $j++){


                if ($cursussen[$j]["deelnemer_id"] == $huidige_id){ 

                    $rowspan++;

                    if ($cursussen[$j]["cursus_code"][0] === "W"){
                        
                        $totaal_praktijk += $cursussen[$j]["punten"];

                    }

                    else {

                        $totaal_theorie += $cursussen[$j]["punten"];

                    }

                }

             }


             $table_row = "<tr><td name='" . $result[$i]["roepnaam"] . " " . $result[$i]["achternaam"] . "' rowspan=" . $rowspan . " data-toggle='modal' data-target='#deelnemer-details'>" . $result[$i]["achternaam"] . ", " . $result[$i]["roepnaam"] . "</td>";

             if ($rowspan == 0){
                 $rowspan++;
                 $limit_check++;
                 
                 
                 
                 $code95cel_toevoeging = "";
                        $verschil_code_95 = dateDiff($result[$i]['code_95']);
        
                        if ($verschil_code_95 < 180 && $verschil_code_95 > 0){
                            $code95cel_toevoeging = "class='waarschuwing' title='Rijbewijs verloopt in minder dan 6 maanden'";
                           }
        
                        elseif ($verschil_code_95 < 0){
                                $code95cel_toevoeging = "class='verloping' title='Rijbewijs is verlopen'";
                               }
        
                        else{
                             $code95cel_toevoeging = "";
                            }
                 
                 $result[$i]["code_95"] = DateTime::createFromFormat('Y-m-d', $result[$i]["code_95"])->format('d-m-Y');
                
                 $table_row = "<tr><td name='" . $result[$i]["roepnaam"] . " " . $result[$i]["achternaam"] . "' rowspan=" . $rowspan . " data-toggle='modal' data-target='#deelnemer-details'>" . $result[$i]["achternaam"] . ", " . $result[$i]["roepnaam"] . "</td><td>-</td><td>-</td><td>-</td><td>-</td><td " . $code95cel_toevoeging . ">" . $result[$i]["code_95"] . "<td>-</td><td>-</td></tr>";
            } 
 
             $loop_check = 0; 

             for ($j = 0; $j < count($cursussen); $j++){


                if ($cursussen[$j]["deelnemer_id"] == $huidige_id){ 

                  
                    $cursus_kleur = "";

                    if ($cursussen[$j]["cursus_code"][0] === "W"){
                        
                        $cursus_kleur = 'W-cursus';

                    }

                    else {

                        $cursus_kleur = "U-cursus";

                    }
                    
                    $verloopcel_toevoeging = "";
                    $verschil_cursus = dateDiff($cursussen[$j]["verloopdatum"]);
        
                    if ($verschil_cursus < 180 && $verschil_cursus > 0){
                        $verloopcel_toevoeging = "class='waarschuwing' title='Cursus verloopt in minder dan 6 maanden'"; 
                    }
                    
                    elseif ($verschil_cursus < 0){
                        $verloopcel_toevoeging = "class='verloping' title='Cursus is verlopen'"; 
                    }
                              
                    else {
                        $verloopcel_toevoeging = ""; 
                    }

                    if ($loop_check == 0){
                        $loop_check++;

                        $code95cel_toevoeging = "";
                        $verschil_code_95 = dateDiff($result[$i]['code_95']);
        
                        if ($verschil_code_95 < 180 && $verschil_code_95 > 0){
                            $code95cel_toevoeging = "class='waarschuwing' title='Rijbewijs verloopt in minder dan 6 maanden'";
                           }
        
                        elseif ($verschil_code_95 < 0){
                                $code95cel_toevoeging = "class='verloping' title='Rijbewijs is verlopen'";
                               }
        
                        else{
                             $code95cel_toevoeging = "";
                            }
                        
                        $result[$i]["code_95"] = DateTime::createFromFormat('Y-m-d', $result[$i]["code_95"])->format('d-m-Y');
                        $cursussen[$j]["verloopdatum"] = DateTime::createFromFormat('Y-m-d', $cursussen[$j]["verloopdatum"])->format('d-m-Y');

                        $table_row .= "<td class='" . $cursus_kleur . "'>" . $cursussen[$j]["cursus_code"] . "</td><td>" . $cursussen[$j]["cursus_naam"] . "</td><td>" . $cursussen[$j]["punten"] . "</td><td " . $verloopcel_toevoeging . ">" . $cursussen[$j]["verloopdatum"] . "</td><td " . $code95cel_toevoeging . " rowspan='" . $rowspan ."'>" . $result[$i]["code_95"] . "</td><td rowspan='" . $rowspan ."'>" . $totaal_theorie . "</td><td rowspan='" . $rowspan ."'>" . $totaal_praktijk . "</td></tr>";
                    }

                    else {
                        
                        $cursussen[$j]["verloopdatum"] = DateTime::createFromFormat('Y-m-d', $cursussen[$j]["verloopdatum"])->format('d-m-Y');
                        
                        $table_row .= "<tr><td class='" . $cursus_kleur . "'>" . $cursussen[$j]["cursus_code"] . "</td><td>" . $cursussen[$j]["cursus_naam"] . "</td><td>" . $cursussen[$j]["punten"] . "</td><td " . $verloopcel_toevoeging . ">" . $cursussen[$j]["verloopdatum"] . "</td></tr>";    
                    }

                }

             }

            $table_rows[] = $table_row;


        }
    
    
    $table = "<table><thead><tr><th>Medewerker</th><th>Cursus Code</th><th>Cursus Naam</th><th>Punten</th><th>Verloopdatum Cursus</th><th>Verloopdatum Code 95</th><th>Totaal Theorie</th><th>Totaal Praktijk</th></tr></thead><tbody>";

    for ($i = 0; $i < count($table_rows); $i++){
        $table .= $table_rows[$i];
    }

    $table .= "<tr><td id='praktijk'>Praktijk</td><td id='theorie'>Theorie</td><td></td><td></td><td></td><td></td><td></td><td></td></tr></tbody></table><div>
    Paginas:
    <span class='paginas'>";
    
    for ($i = 1; $i < $paginas+1; $i++){
         if ($i == htmlspecialchars($data[1])){
        
         $table .= "<span class='huidige-pagina' onclick='VeranderPagina(" . $i .");'>" . $i . "</span>";
             
         }
        
        elseif ($i == 1 && !$data[1]){
         $table .= "<span class='huidige-pagina' onclick='VeranderPagina(" . $i .");'>" . $i . "</span>";
        }
        
        else {
            $table .= "<span class='pagina' onclick='VeranderPagina(" . $i .");'>" . $i . "</span>";
        }
    }
    
    $table .= "</span></div>";

    

    echo $table;

    
}

else {
    echo "Er zijn geen cursussen voor het bedrijf gevonden.";
    exit();
}


?>