<?php

/* 
Roept het opgeslagen error bericht op en geeft deze weer op de frontend.
Daarna wordt het bericht verwijderd om herhalende weergave van dit op de frontend te voorkomen.
*/

$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);
if ($_SESSION["error-message"]){
    echo $_SESSION["error-message"] . "<span id='error-sluiten'>x</span>";
    $_SESSION["error-message"] = "";
    exit();
}

else {
    echo "";
    exit();
}
?>