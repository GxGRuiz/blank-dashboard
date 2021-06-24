<?php

session_start();

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

if ($_SESSION["rol"] != "admin"){
    header("Location: http://dummy.url/home");
    exit();
}

include_once("config.php");

$accounts_overzicht = "<table><thead><tr><th>Gebruiker</th><th>Bedrijf</th><th>Rechten</th><th>Toestemming</th><th>Acties</th></tr></thead><tbody>";

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts); 

$result = $link_accounts->query("SELECT gebruiker, bedrijf, rechten, toestemming, temp_link_verloopdatum FROM dummy")->fetch_all(MYSQLI_ASSOC);

for ($i = 0; $i < count($result); $i++){
    
    $reg_check = "";
    
    if ($result[$i]["temp_link_verloopdatum"] != null){
        $reg_check = "class='ongeregistreerd' title='Deze account is onregistreerd'";
    }
     
     $accounts_overzicht .= "<tr><td " . $reg_check . ">" . $result[$i]["gebruiker"] . "</td><td>" . $result[$i]["bedrijf"] . "</td><td>";
    
    if ($result[$i]["rechten"] == 1){
        $accounts_overzicht .= "Gebruiker</td>";
    }
    
    else {
        $accounts_overzicht .= "Admin</td>";
    }
    
    if ($result[$i]["toestemming"] == 1){
        $accounts_overzicht .= "<td>Ja</td>";
    }
    
    else {
        $accounts_overzicht .= "<td>Nee</td>";
    }
    
    
    
    $accounts_overzicht .= "<td><button class='btn btn-primary' data-toggle='modal' data-target='#actie-prompt' onClick='ActieBerichtTekst(\"" . $result[$i]["gebruiker"] . "\", \"verwijdering\")'>Verwijderen</button><button class='btn btn-primary' data-toggle='modal' data-target='#actie-prompt' onClick='ActieBerichtTekst(\"" . $result[$i]["gebruiker"] . "\", \"wachtwoord herinstelling\")'>Wachtwoord opnieuw instellen</button></td><tr>";
    
 }

$accounts_overzicht .= "</tbody></table>
<div id='actie-prompt' class='modal fade'>
<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Gebruiker verwijderen</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>×</span>
        </button>
      </div>

      <div id='actie-bericht' class='modal-body'>

</div>
<div class='modal-footer'>
        <button id='actie-uitvoeren' class='btn btn-primary' data-dismiss='modal'>Verwijderen</button>
        <button type='button' class='btn btn-secondary' data-dismiss='modal' onClick='GetGebruikers()'>Sluiten</button>
      </div>
    </div>
  </div>
</div>
";
echo $accounts_overzicht;

?>