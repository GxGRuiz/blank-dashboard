<?php

/*
Roept 'kaarten' gevuld met titels, tekst en afbeedlingen van nieuwsberichten voor het geselecteerde bedrijf.
Het bovenstaande kan veranderd en opgeslagen of verwijderd worden dmv de bijhorende knoppen.
*/

session_start();
include_once("config.php");
header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 

$link_nieuws = new mysqli(host, database_nieuws, password_nieuws, database_nieuws);

if(!mysqli_set_charset($link_nieuws, 'utf8')){
    printf("Error loading character set utf8: %s\n", $link->error);
    printf("Current character set: %s\n", $link->character_set_name());
    die();
};   

if (!$link_nieuws){
     die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
   }

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

$bedrijf = htmlspecialchars($data[0]);

if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        exit();
}

$nieuws_berichten_links = "<div class='col-lg-6'>";
$nieuws_berichten_rechts = "<div class='col-lg-6'>";

$result = $link_nieuws->query("SELECT id, titel, bericht, afbeelding, datum FROM `" . $bedrijf . "` ORDER BY datum DESC")->fetch_all(MYSQLI_ASSOC);

for ($i = 0; $i < count($result); $i++){
    
    if ($i == 0 || $i % 2 == 0){
     
     $nieuws_berichten_links .= '<div class="card">
                         <form action="/assets/php/aanpassen_nieuws.php" enctype="multipart/form-data" method="POST">
                         <input type="hidden" name="id" value="' . $result[$i]["id"] . '">
                         <input type="hidden" name="bedrijf" value="' . $bedrijf . '">
                         <div class="card-header"><input type="text" name="titel" class="form-control" value="' . $result[$i]["titel"] . '"></h5></div><div class="input-group mb-3">'; 
    
        if ($result[$i]["afbeelding"] != "" && $result[$i]["afbeelding"] != null){
            $nieuws_berichten_links .= '<img src="' . $result[$i]["afbeelding"] . '">'; 
        }
        
        else {
            $nieuws_berichten_links .= '<span>Geen afbeelding voor dit bericht gevonden</span>';
        }
                    
                        $nieuws_berichten_links .= '<br><input type="hidden" name="MAX_FILE_SIZE" value="1000000">     
             <input type="file" name="nieuws-afbeelding" accept="image/*">
               </div>
               
                        <div class="card-body"><textarea name="bericht">' . $result[$i]["bericht"] . '</textarea><br><i>' . $result[$i]["datum"] . '</i></div>
                         
                         <div class="card-footer">
        <input type="submit" class="btn btn-secondary" value="Wijzigen">
        <button class="btn btn-secondary" formaction="/assets/php/verwijder_nieuws.php">Verwijderen</button>
      </div></form></div>
                         ';
    }
    
    else {
         $nieuws_berichten_rechts .= '<div class="card">
                         <form action="/assets/php/aanpassen_nieuws.php" enctype="multipart/form-data" method="POST">
                         <input type="hidden" name="id" value="' . $result[$i]["id"] . '">
                         <input type="hidden" name="bedrijf" value="' . $bedrijf . '">
                         <div class="card-header"><input type="text" name="titel" class="form-control" value="' . $result[$i]["titel"] . '"></h5></div><div class="input-group mb-3">';
        
        if ($result[$i]["afbeelding"] != "" && $result[$i]["afbeelding"] != null){
            $nieuws_berichten_rechts .= '<img src="' . $result[$i]["afbeelding"] . '">'; 
        }
        
        else {
            $nieuws_berichten_rechts .= '<span>Geen afbeelding voor dit bericht gevonden</span>';
        }

            $nieuws_berichten_rechts .= '<br><input type="hidden" name="MAX_FILE_SIZE" value="1000000">     
             <input type="file" name="nieuws-afbeelding" accept="image/*">
             </div>
             
            <div class="card-body"><textarea name="bericht">' . $result[$i]["bericht"] . '</textarea><br><i>' . $result[$i]["datum"] . '</i></div>
                         
                         <div class="card-footer">
        <input type="submit" class="btn btn-secondary" value="Wijzigen">
        <button class="btn btn-secondary" formaction="/assets/php/verwijder_nieuws.php">Verwijderen</button>
      </div></form></div>
                         ';
    }
                           
    }


$nieuws_berichten_links .= "</div>";
$nieuws_berichten_rechts .= "</div>";

echo $nieuws_berichten_links;
echo $nieuws_berichten_rechts;
?>