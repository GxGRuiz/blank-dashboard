<?php

/*
Gaat na hoeveel downloads er zijn en maakt download links om op de frontend weer te geven. 
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

include_once("config.php");

$download_dir = "../downloads/";
$downloads = scandir($download_dir);
$download_links = "";

if ($_SESSION["rol"] == "gebruiker"){
    
    $bedrijf = htmlspecialchars($_SESSION["bedrijf"]);
    
    $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);
    
    $result_downloads = $link_documentatie->query("SELECT doc_link FROM `" . $bedrijf . "` WHERE categorie = 'Algemene Documenten' ORDER BY doc_link ASC")->fetch_all(MYSQLI_ASSOC);
    
    $rel_path_add = "..";
    
    for ($i = 0; $i < count($result_downloads); $i++){
        
        $download_links .= "<div><a href='"  . $rel_path_add . $result_downloads[$i]["doc_link"] . "' target='_blank' download=''>" . explode("/", $result_downloads[$i]["doc_link"])[count(explode("/", $result_downloads[$i]["doc_link"])) - 1] . "</a></div>";
        
    }
   }

else {
   
for ($i = 0; $i < count($downloads); $i++){
    $download_href = "../assets/downloads/";
    if (is_file($download_dir . $downloads[$i])){
    $download_links .= "<div><a href='"  . $download_href . $downloads[$i] . "' target='_blank' download=''>" . $downloads[$i] . "</a></div>";
    }
}
    
}

echo $download_links;
?>
