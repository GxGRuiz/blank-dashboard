<?php

/*
Geeft de gebruikersnaam weer op de frontend in een <a> link element die terug linkt naar de home pagina.
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);
if (isset($_SESSION["logged-in"]) && $_SESSION["logged-in"] == true){
    $slug_toevoeging = "";
    
    if ($_SESSION["rol"] == "admin" || $_SESSION["rol"] == "super_admin" || $_SESSION["rol"] == "head_admin"){
    $slug_toevoeging = "admin-";
    }
    
    echo "<a href='" . $slug_toevoeging . "home'><span>" . $_SESSION["gebruiker"] . "</span></a>"; 
    exit();
}

else {
    echo "http://dummy.url/login";
    exit();
}
?>