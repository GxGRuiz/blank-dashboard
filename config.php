<?php

/*
Definitieert de verschillende databases en mysql credenties
*/

define('host', 'localhost');
define('username', 'dummy');
define('password_websiteDB', 'filltheblank');
define('password_accounts', 'filltheblank');
define('password_cursus', 'filltheblank');
define('password_nieuws', 'filltheblank');
define('password_documentatie', 'nNKN8GsU');
define('database_accounts', 'dummy_bedrijven');
define('database_webpages', 'dummy_websiteDB');
define('database_cursus', 'dummy_cursussen');
define('database_nieuws', 'dummy_nieuws');
define('database_documentatie', 'dummy_documentatie');


function dateDiff($date){
    $now = time();
    $your_date = strtotime($date);
        $date_diff = round(($your_date - $now) / (60 * 60 * 24));
    return $date_diff;
}

function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    header("Location: http://dummy.url/login");
    exit();
}

function bedrijfValidatie ($bedrijfsnaam){ 
    
    $quick_link = new mysqli(host, database_accounts, password_accounts, database_accounts);
    $bedrijfsql_check = "SELECT * FROM dummy WHERE bedrijf = ?";
    
    $stmt_check = $quick_link->prepare($bedrijfsql_check);
    $stmt_check->bind_param('s', $bedrijfsnaam);
    $stmt_check->execute();
    
    $result = $stmt_check->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);
    
    if (is_array($result) && count($result) != 0){
        return true;
    }
    
    else {
        return false;
    }
}

//Gaat na of de cursus code/naam combinatie klopt of vult de andere in indien er maar één is toegevoegd 

function cursusCheck ($cursusdata){
    
    $cursus_details = [["dummy code", "dummy naam"],["dummy code", "dummy naam"]];
    
    if (is_array($cursusdata)){
        
        for ($i = 0; $i < count($cursus_details); $i++){
                 
             if ($cursusdata[0] == $cursus_details[$i][0] && $cursusdata[1] == $cursus_details[$i][1]){
                 return true;
             }
            
             elseif ($cursusdata[0] != $cursus_details[$i][0] && $cursusdata[1] == $cursus_details[$i][1]){
                     return "Cursus code " . $cursusdata[0] . " komt niet overeen met gegeven cursus naam " . $cursusdata[1];
             }
            
             elseif ($cursusdata[0] == $cursus_details[$i][0] && $cursusdata[1] != $cursus_details[$i][1]){
                     return "Cursus naam " . $cursusdata[1] . " komt niet overeen met gegeven cursus code " . $cursusdata[0];
             }
        
             else {
                   continue;
             }
            
        }
        
        return false;
        
    }
    
    else {
    
        for ($i = 0; $i < count($cursus_details); $i++){
              
            
            if ($cursusdata == $cursus_details[$i][0]){
                return $cursus_details[$i][1];
            }
            
            elseif ($cursusdata == $cursus_details[$i][1]){
                return $cursus_details[$i][0];
           }
        
           else {
               continue;
           }
        
        }
        
        return false;
    
    }
}

?>