<?php

header("Content-Type: application/json"); 
$data = json_decode(file_get_contents("php://input")); 

  session_start();

if (!isset($_SESSION["logged-in"]) || $_SESSION["logged-in"] == false){
    echo "http://dummy.url/login";
    exit();
}

if ($_SESSION["rol"] == "ongeregistreerde_gebruiker"){
    header("Location: http://dummy.url/nieuw-wachtwoord");
    exit();
}

if ($_SESSION["rol"] != "admin"){
    header("Location: http://dummy.url/home");
    exit();
}


include_once("config.php");

$categorie = $data[0];

$dir_selectie;

switch ($categorie){
        case "Algemene Documenten":
             $dir_selectie = "downloads";
             break;
            
        case "Handleiding":    
             $dir_selectie = "handleiding";
             break;
            
        case "Juridisch":
             $dir_selectie = "juridisch";
             break;
        
        case "Kies een categorie":
             
             exit();  
             break;        
            
        default:
             $_SESSION["error-message"] = "Er ging iets fout, probeer het later opnieuw";
             //header("Location: http://dummy.url/admin-documentatie");
             exit();   
}

$geselecteerde_dir = '../' . $dir_selectie . "/";
$dir_file_namen = scandir($geselecteerde_dir);

$file_namen_array = [];

$documentatie_overzicht = "<table><thead><tr><th>Bedrijf | Document</th>";

for ($i = 2; $i < count($dir_file_namen); $i++){
     $documentatie_overzicht .= "<th>" . $dir_file_namen[$i] . "</th>";
     
     $file_namen_array[] = $dir_file_namen[$i];
}

$documentatie_overzicht .= "</tr></thead><tbody>";
//echo $documentatie_overzicht;
//exit();

$link_accounts = new mysqli(host, database_accounts, password_accounts, database_accounts); 
 $link_documentatie = new mysqli(host, database_documentatie, password_documentatie, database_documentatie);

$result = $link_accounts->query("SELECT bedrijf FROM dummy")->fetch_all(MYSQLI_ASSOC);

for ($i = 0; $i < count($result); $i++){
     $bedrijf = $result[$i]["bedrijf"];
    
     if (!bedrijfValidatie($bedrijf)){
        $_SESSION["error-message"] = "Gekozen bedrijf bestaat niet";
        exit();
     }
    
     $bedrijf_documentatie_array = [];
    
     $stmt = $link_documentatie->prepare("SELECT doc_link FROM `" . $bedrijf . "` WHERE categorie = ?");
     $stmt->bind_param("s", $categorie);
     $stmt->execute();
     $bedrijf_documentatie = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
     
    
     for ($j = 0; $j < count($bedrijf_documentatie); $j++){
          $bedrijf_documentatie_array[] = explode('/', $bedrijf_documentatie[$j]["doc_link"])[count(explode('/', $bedrijf_documentatie[$j]["doc_link"]))-1];
     }

     
     $documentatie_overzicht .= "<tr><td><b>" . $bedrijf . "</b></td>";
    
     for ($j = 0; $j < count($file_namen_array); $j++){
         
         
         
          if (in_array($file_namen_array[$j], $bedrijf_documentatie_array)){
              $documentatie_overzicht .= "<td><button class='btn btn-primary' data-toggle='modal' data-target='#actie-prompt' onClick='DocumentatieBerichtTekst(\"" . htmlspecialchars($bedrijf, ENT_QUOTES) . "\", \"verwijdering\", \"". $file_namen_array[$j] ."\")'>Verwijderen?</button></td>";
          } 
         
         else {
             $documentatie_overzicht .= "<td><button class='btn btn-primary' data-toggle='modal' data-target='#actie-prompt' onClick='DocumentatieBerichtTekst(\"" . htmlspecialchars($bedrijf, ENT_QUOTES) . "\", \"toevoeging\", \"". $file_namen_array[$j] ."\")'>Toevoegen?</button></td>";
         }
              
     }
    
    $documentatie_overzicht .= "</tr>";
     
}

$documentatie_overzicht .= "</tbody></table>
<div id='actie-prompt' class='modal fade'>
<div class='modal-dialog modal-dialog-centered modal-dialog-scrollable'>
    <div class='modal-content'>
      <div class='modal-header'>
        <h5 class='modal-title'>Document toegang aanpassen</h5>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
          <span aria-hidden='true'>×</span>
        </button>
      </div>

      <div id='actie-bericht' class='modal-body'>

</div>
<div class='modal-footer'>
        <button id='actie-uitvoeren' class='btn btn-primary' data-dismiss='modal'>Verwijderen</button>
        <button type='button' class='btn btn-secondary' data-dismiss='modal'>Sluiten</button>
      </div>
    </div>
  </div>
</div>
";

echo $documentatie_overzicht;
?>