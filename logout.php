<?php

/*
Logt het account uit. 
*/

define('ROOT', __DIR__);

// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("Location: http://dummy.url/login");
exit;
?>