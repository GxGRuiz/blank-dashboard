<?php


/* 
Roept de informatie aan voor de geklikte deelnemercel in de punten tabel overzicht op de overzicht pagina en geeft deze weer in een modal pop-up op de frontend.
*/


define('ROOT', __DIR__);

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 
$lifetime=600;
  session_start();
  setcookie(session_name(),session_id(),time()+$lifetime);

include_once("config.php");


if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    header("Location: http://dummy.url/login");
    exit();
}

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

$bedrijf;

if ($data[1]){
    $bedrijf = $data[1];
    
    if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        exit();
}
}

else {
    $bedrijf = $_SESSION['bedrijf'];
}

$link = new mysqli(host, database_accounts, password_accounts, database_accounts);

$voornaam = explode(" ", htmlspecialchars($data[0]))[0]; 
$achternaam = explode(" ", htmlspecialchars($data[0]))[1];

for ($j = 2; $j < count(explode(" ", $data[0])); $j++){
            $achternaam .= " " . explode(" ", $data[0])[$j];
        } 


$stmt = $link->prepare("SELECT roepnaam, achternaam, geboortedatum, geboorteplaats, cbr, rijbewijs_c FROM `" . $bedrijf . "` WHERE roepnaam = ? AND achternaam = ?");
$stmt->bind_param('ss', $voornaam, $achternaam);
$stmt->execute();
$result = $stmt->get_result();
$result = $result->fetch_all(MYSQLI_ASSOC);

$deelnemer = '
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deelnemer-details-Label">Persoonsgegevens</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
';

if ($result){
    $deelnemer .= "<table><tbody><tr><th>Roepnaam</th><td>" . $result[0]['roepnaam'] . "</td></tr><tr><th>Achternaam</th><td>" . $result[0]['achternaam'] . "</td></tr><tr><th>Geboortedatum</th><td>" . $result[0]['geboortedatum'] . "</td></tr><tr><th>Geboorteplaats</th><td>" . $result[0]['geboorteplaats'] . "</td></tr><tr><th>CBR nummer</th><td>" . $result[0]['cbr'] . "</td></tr><tr><th>Verloopdatum rijbewijs c</th><td>" . $result[0]['rijbewijs_c'] . "</td></tr></tbody></table>";
}

$deelnemer .= '</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Sluiten</button>
      </div>
    </div>
  </div>';

echo $deelnemer;
?>