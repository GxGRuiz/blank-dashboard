<?php

/*
Gaat na hoeveel handleiding files er zijn en maakt download links om op de frontend weer te geven. 
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

include_once("config.php");

$handleiding_dir = "../handleiding/";
$handleiding = scandir($handleiding_dir);
$handleiding_links = "";

if ($_SESSION["rol"] == "gebruiker"){
    
    $bedrijf = htmlspecialchars($_SESSION["bedrijf"]);
    
    $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);
    
    $result_handleiding = $link_documentatie->query("SELECT doc_link FROM `" . $bedrijf . "` WHERE categorie = 'Handleiding' ORDER BY doc_link ASC")->fetch_all(MYSQLI_ASSOC);
    
    $rel_path_add = "..";
    
    for ($i = 0; $i < count($result_handleiding); $i++){
       
        $handleiding_links .= "<div><a href='"  . $rel_path_add . $result_handleiding[$i]["doc_link"] . "' target='_blank' download=''>" . explode("/", $result_handleiding[$i]["doc_link"])[count(explode("/", $result_handleiding[$i]["doc_link"])) - 1] . "</a></div>";
        
    }
   }

else {
    
    for ($i = 0; $i < count($handleiding); $i++){
    $handleiding_href = "../assets/handleiding/";
    if (is_file($handleiding_dir . $handleiding[$i])){
    $handleiding_links .= "<div><a href='"  . $handleiding_href . $handleiding[$i] . "' target='_blank' download=''>" . $handleiding[$i] . "</a></div>";
    }
}
    
}

echo $handleiding_links;
?>