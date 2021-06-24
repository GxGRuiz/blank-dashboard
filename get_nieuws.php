<?php

session_start();
include_once("config.php");
 
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

$bedrijf = $_SESSION["bedrijf"];

if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        exit();
}

$nieuws_berichten_links = "<div class='col-lg-6'>";
$nieuws_berichten_rechts = "<div class='col-lg-6'>";

$result = $link_nieuws->query("SELECT titel, bericht, afbeelding, datum FROM `" . $bedrijf . "` ORDER BY datum DESC")->fetch_all(MYSQLI_ASSOC);

if (count($result) != 0){

for ($i = 0; $i < count($result); $i++){
    
     if ($i == 0 || $i % 2 == 0){
     $nieuws_berichten_links .= '<div class="card">

                         <div class="card-header"><h5 class="m-0">' . $result[$i]["titel"] . '</h5></div>';
         
         if ($result[$i]["afbeelding"] != "" && $result[$i]["afbeelding"] != null){
            $nieuws_berichten_links .= '<div class="input-group mb-3"><img src="' . $result[$i]["afbeelding"] . '"></div>'; 
        }

                         $nieuws_berichten_links .= '<div class="card-body">' . $result[$i]["bericht"] . '</div>
                         
                         <div class="card-footer">
        <i>' . $result[$i]["datum"] . '</i>
      </div></div>';
     }
    
    else {
        $nieuws_berichten_rechts .= '<div class="card">

                         <div class="card-header"><h5 class="m-0">' . $result[$i]["titel"] . '</h5></div>';
        
        if ($result[$i]["afbeelding"] != "" && $result[$i]["afbeelding"] != null){
            $nieuws_berichten_rechts .= '<div class="input-group mb-3"><img src="' . $result[$i]["afbeelding"] . '"></div>'; 
        }

                         $nieuws_berichten_rechts .= '<div class="card-body">' . $result[$i]["bericht"] . '</div>
                         
                         <div class="card-footer">
        <i>' . $result[$i]["datum"] . '</i>
      </div></div>';
    }
    
    }

$nieuws_berichten_links .= "</div>";
$nieuws_berichten_rechts .= "</div>";

echo $nieuws_berichten_links;
echo $nieuws_berichten_rechts;
    
}

else {
    echo "<div>Er zijn geen nieuwsberichten voor het bedrijf gevonden.</div>";
}

?>
