<?php

/*
Gaat na hoeveel juridische files er zijn en maakt download links om op de frontend weer te geven. 
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

include_once("config.php");

$juridische_dir = "../juridisch/";
$juridisch = scandir($juridische_dir);
$juridische_links = "";

if ($_SESSION["rol"] == "gebruiker"){
    
    $bedrijf = htmlspecialchars($_SESSION["bedrijf"]);
    
    $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);
    
    $result_juridisch = $link_documentatie->query("SELECT doc_link FROM `" . $bedrijf . "` WHERE categorie = 'Juridisch' ORDER BY doc_link ASC")->fetch_all(MYSQLI_ASSOC);
    
    $rel_path_add = "..";
    
    for ($i = 0; $i < count($result_juridisch); $i++){
        
        $juridische_links .= "<div><a href='"  . $rel_path_add . $result_juridisch[$i]["doc_link"] . "' target='_blank' download=''>" . explode("/", $result_juridisch[$i]["doc_link"])[count(explode("/", $result_juridisch[$i]["doc_link"])) - 1] . "</a></div>";
        
    }
   }

else {

for ($i = 0; $i < count($juridisch); $i++){
    $juridisch_href = "../assets/juridisch/";
    if (is_file($juridische_dir . $juridisch[$i])){
    $juridische_links .= "<div><a href='"  . $juridisch_href . $juridisch[$i] . "' target='_blank' download=''>" . $juridisch[$i] . "</a></div>";
    }
    
}
    
}

echo $juridische_links;
?>