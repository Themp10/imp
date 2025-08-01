<?php
$projets=["MT","WL", "SH", "KPC", "MNO", "OP", "CP", "BA", "UP", "UPBC","ZT"];
$projects = [
  "MT" =>  ["Bureau", "Commerce", "Archive"],
  "WL" =>  ["Conventionne", "Coliving"],
  "SH" =>  ["Appartement", "TownHaus"],
  "KPC" => ["Appartement", "Magasin", "Bureau"],
  "MNO" => ["Magasin", "Bureau"],
  "OP" =>  ["Appartement", "Magasin", "Bureau"],
  "CP" =>  ["Appartement", "Magasin", "Bureau"],
  "BA" =>  ["Appartement", "Magasin"],
  "UP" =>  ["Appartement", "Parking Supp"],
  "UPBC"=> ["Bureau", "Parking Supp"],
  "ZT" =>  ["Appartement", "Magasin"]
];
$statuts = [
  "Disponible" =>  0,
  "Réservé" =>  2,
  "Soldé" => 6,
  "Bloqué" => 8,
  "Loué" =>  5
];

function sql_from_Hana_queryStock($sql){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);
 
    $data=[];
    //$setCharset = odbc_exec($Hanaconn, "SET NAMES UTF8");
    //$setCharset = odbc_exec($Hanaconn, "SET CHARACTER SET UTF8");
    $setDb = odbc_exec($Hanaconn, "SET SCHEMA " . "SYSTEM");
    $result = odbc_exec($Hanaconn,$sql);
    if (!$result)
    {
        echo "Error while sending SQL statement to the database server.\n";
        echo "ODBC error code: " . odbc_error() . ". Message: " . odbc_errormsg();
    }
    else
    {
        while ($row = odbc_fetch_array($result))
        {
            $data[]=mb_convert_encoding($row, "UTF-8", "iso-8859-1");
        }
    }
    

    odbc_close($Hanaconn);
    return $data;
}



function sql_to_Hana_insert($sql) {
  $dsn = "HANA";
  $username = "SYSTEM";
  $password = "Skatys2020";

  // Connect to HANA
  $Hanaconn = odbc_connect($dsn, $username, $password);
  if (!$Hanaconn) {
      die("Error connecting to the database: " . odbc_errormsg());
  }
  $setDb = odbc_exec($Hanaconn, "SET SCHEMA SYSTEM");
  if (!$setDb) {
      echo "Error setting schema: " . odbc_errormsg() . "\n";
      odbc_close($Hanaconn);
      return false;
  }

  // Execute the insert query
  $result = odbc_exec($Hanaconn, $sql);
  if (!$result) {
      echo "Insert operation failed. Error: " . odbc_errormsg() . "\n";
      odbc_close($Hanaconn);
      return false;
  }
  // Close connection
  odbc_close($Hanaconn);

  return true;
}


function generateProjectSelector(){
    global $projects;
    $soc="";
    $projets=["MT","WL", "SH", "KPC", "MNO", "OP", "CP", "BA", "UP", "UPBC","ZT"];

    foreach (array_keys($projects) as $project) {
      switch ($project) {
        case "MT":
          $soc="ANFA_69";
          break;
        case "WL":
          $soc="CASA_COLIVING";
          break;
        case "SH":
          $soc="NAVIS_PROPERTY";
          break;
        case "KPC":
          $soc="YASMINE_FONCIERE";
          break;
        case "MNO":
          $soc="YASMINE_FONCIERE";
          break;
        case "OP":
          $soc="RMM_BUILDING";
          break;
        case "CP":
          $soc="RMM_BUILDING";
          break;
        case "BA":
          $soc="AM_PROINVEST";
          break;
        case "UP":
          $soc="ANFA_REALISATION";
          break;
        case "UPBC":
          $soc="ANFA_REALISATION";
          break;
        case "ZT":
          $soc="M_PROPERTIES";
          break;
        default:
          $soc="";
          break;
        }
      echo "<button id='btn-project' class='btn-projet' project='".$project ."'  soc='".$soc ."'>".$project ."</button>";
  }
}
function generateStockTableContent($projet,$societe){
  global $projects;
  $html="";
  foreach ($projects[$projet] as $typology) {
    $tmp=0;
    $sql='select "StatutBien","U_StatutBien",count(*) as "U",TO_DECIMAL(sum("Price"),18,2) as "CA"   from "V_OITM"
          where  "U_Projet"=\''.$projet.'\'   and "TypeBien"=\''.$typology.'\'
          group by "StatutBien","U_StatutBien" order by "U_StatutBien"';
    $data=sql_from_Hana_queryStock($sql);

      $sql='select "V_OITM"."U_StatutBien",count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" 
            from "V_ORDR" "V_ORDR" 
            LEFT OUTER JOIN  "V_RDR1" "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
            LEFT OUTER JOIN  "V_OITM" "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and "V_RDR1"."LineNum"=\'0\' and "V_OITM"."U_Projet"=\''.$projet.'\' and  "TypeBien"=\''.$typology.'\'
            where   "V_ORDR"."CANCELED"=\'N\'
            group by "V_OITM"."U_StatutBien" order by "V_OITM"."U_StatutBien"';
    $data2=sql_from_Hana_queryStock($sql);

    $TU=$TCA=$uL=$caL=$uLO=$caLO=$uR=$caR=$uS=$caS=$uB=$caB=0;  
    foreach ($data as $row) {
      if($row["U_StatutBien"]=='0'){
        $uL=$row["U"];
        $caL=$row["CA"];
      }elseif ($row["U_StatutBien"]=='1') {
        $uLO=$row["U"];
        $caLO=$row["CA"];
      }elseif ($row["U_StatutBien"]=='8') {
        $uB=$row["U"];
        $caB=$row["CA"];
      }elseif($row["U_StatutBien"]=='2'){
        $uR=$row["U"];
      }elseif ($row["U_StatutBien"]=='6') {
        $uS=$row["U"];
      }
    }
    foreach ($data2 as $row) {
      if($row["U_StatutBien"]=='2'){
        $caR=$row["CA"];
      }elseif ($row["U_StatutBien"]=='6') {
        $caS=$row["CA"];
      }
    }
    $TU=$uL+$uR+$uS+$uB+$uLO;
    $TCA=$caL+$caR+$caS+$caB+$caLO;
    $html.='<tr>
            <td rowspan="2">'.$typology.'</td> 
            <td>U</td>
            <td>'.$TU.'</td>
            <td>'.$uL.'</td>
            <td>'.$uR.'</td>
            <td>'.$uS.'</td>
            <td>'.$uB.'</td>
            <td>'.$uLO.'</td>
          </tr>
          <tr>
            <td>C.A</td>
            <td>'.$TCA.'</td>
            <td>'.$caL.'</td>
            <td>'.$caR.'</td>
            <td>'.$caS.'</td>
            <td>'.$caB.'</td>
            <td>'.$caLO.'</td>
        </tr>';
          
  }
  return $html;  
}
function generateSaisieTableContent($projet,$validation,$prf){
  global $projects;
  $html="";
  $k=0;
  $v1=$validation[1];
  $v2=$validation[2];
  $disable='';
  if($prf=="directeur" && $v2==1){
    $disable='disabled';
  }
  if($prf=="commercial" && $v1==1){
    $disable='disabled';
  }

  $sql='select *  from "OBJECTIFS"
          where  "projet"=\''.$projet.'\' and "annee"=2025';
  $data=sql_from_Hana_queryStock($sql);
  $max=sizeof($projects[$projet]);
  $html.='<tr>';
  $html.='<td rowspan="4" id="total-0">Total Projet</td>
          <td class="smaller-td">Ventes U</td>
          <td>
            <div class="td-inputs" type="text" id="T-0" name="T-0" >%vente_u%</div>
            </td>';
    $vente_u_cum = 0;

  for ($i=0; $i < 12; $i++) { 
    $filteredMois = array_values(array_filter($data, function($objectif) use ($i) {return $objectif['mois'] == $i+1 ;}));
    $vente_u = array_sum(array_column($filteredMois, 'vente_u'))  ;
    $vente_u_cum+=$vente_u;
    $html.='<td class="td-input-container">
              <input disabled class="td-input" type="text" id="T-0-'.$i.'" name="T-0-'.$i.'" max-rows="'.$max.'" placeholder="0" value="'.$vente_u.'">
            </td>';
  }
 $html = str_replace("%vente_u%", $vente_u_cum, $html);

  $html.='</tr>';
  $html.='<tr>';
  $html.='<td class="smaller-td">Ventes CA</td>
          <td>
            <div class="td-input" type="text" id="T-1" name="T-1">%vente_ca%</div></td>';
  for ($i=0; $i < 12; $i++) { 
    $filteredMois = array_values(array_filter($data, function($objectif) use ($i) {return $objectif['mois'] == $i+1 ;}));
    $vente_ca = array_sum(array_column($filteredMois, 'vente_ca'))  ;
    $vente_ca_cum+=$vente_ca;
    $html.='<td class="td-input-container">
              <input disabled class="td-input" type="text" id="T-1-'.$i.'" name="T-1-'.$i.'" placeholder="0" value="'.(int) $vente_ca.'" >
            </td>';
  }          
  $html = str_replace("%vente_ca%", $vente_ca_cum, $html);

  $html.='</tr>';
  $html.='<tr>';
  $html.='<td class="smaller-td">Encaissement</td>
          <td>
            <div class="td-input" type="text" id="T-2" name="T-2">
            %encaissement%
            </div>
          </td>';
  for ($i=0; $i < 12; $i++) { 
    $filteredMois = array_values(array_filter($data, function($objectif) use ($i) {return $objectif['mois'] == $i+1 ;}));
    $encaissement = array_sum(array_column($filteredMois, 'encaissement'))  ;
    $encaissement_cum+=$encaissement;
    $html.='<td class="td-input-container">
              <input disabled class="td-input" type="text" id="T-2-'.$i.'" name="T-2-'.$i.'" placeholder="0" value="'.(int) $encaissement.'">
            </td>';
  }          
  $html = str_replace("%encaissement%", $encaissement_cum, $html);
  $html.='</tr>';
  $html.='<tr>';
  $html.='<td class="smaller-td">Recouvrement</td>
          <td>
            <div class="td-input" type="text" id="T-3" name="T-3">
            %recouvrement%
            </div>
          </td>';
  for ($i=0; $i < 12; $i++) { 
    $filteredMois = array_values(array_filter($data, function($objectif) use ($i) {return $objectif['mois'] == $i+1 ;}));
    $recouvrement = array_sum(array_column($filteredMois, 'recouvrement'))  ;
    $recouvrement_cum+=$recouvrement;
    $html.='<td class="td-input-container">
              <input disabled class="td-input" type="text" id="T-3-'.$i.'" name="T-3-'.$i.'" placeholder="0" value="'.(int) $recouvrement.'">
            </td>';
  }          
  $html = str_replace("%recouvrement%", $recouvrement_cum, $html);
  $html.='</tr>';
  foreach ($projects[$projet] as $typology) {

    $filteredDatabyTypology = array_values(array_filter($data, function($objectif) use ($typology) {return $objectif['typologie'] == $typology;}));
    
    $html.='<tr>';
    $html.='<td rowspan="4" id="type-'.$k.'">'.$typology.'</td>
            <td class="smaller-td">Ventes U</td>
            <td>
              <div class="td-input" type="text" id="B-'.$k.'-0" name="B-'.$k.'-0" >
              </div>
            </td>';
    
    for ($i=0; $i < 12; $i++) { 
    $filteredDatabyTypologybyMonth = array_values(array_filter($filteredDatabyTypology, function($objectif) use ($i) {return $objectif['mois'] == ($i+1);}));
    $vente_u = !empty($filteredDatabyTypologybyMonth) ? $filteredDatabyTypologybyMonth[0]["vente_u"] : 0;
      $html.='<td class="td-input-container">
                <input '.$disable.' class="td-input" type="text" id="'.$k.'-0-'.$i.'" name="'.$k.'-0-'.$i.'" max-rows="'.$max.'" placeholder="0" value="'.$vente_u.'">
              </td>';
    }
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td class="smaller-td">Ventes CA</td>
            <td>
              <div class="td-input" type="text" id="B-'.$k.'-1" name="B-'.$k.'-1">
              </div>
            </td>';
    for ($i=0; $i < 12; $i++) { 
      $filteredDatabyTypologybyMonth = array_values(array_filter($filteredDatabyTypology, function($objectif) use ($i) {return $objectif['mois'] == ($i+1);}));
      $vente_ca = !empty($filteredDatabyTypologybyMonth) ? $filteredDatabyTypologybyMonth[0]["vente_ca"] : 0;
      $html.='<td class="td-input-container">
                <input '.$disable.' class="td-input" type="text" id="'.$k.'-1-'.$i.'" name="'.$k.'-1-'.$i.'" placeholder="0" value="'.(int) $vente_ca.'" >
              </td>';
    }          
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td class="smaller-td">Encaissement</td>
            <td>
              <div class="td-input" type="text" id="B-'.$k.'-2" name="B-'.$k.'-2">
              </div>
            </td>';
    for ($i=0; $i < 12; $i++) { 
      $filteredDatabyTypologybyMonth = array_values(array_filter($filteredDatabyTypology, function($objectif) use ($i) {return $objectif['mois'] == ($i+1);}));
      $encaissement = !empty($filteredDatabyTypologybyMonth) ? $filteredDatabyTypologybyMonth[0]["encaissement"] : 0;
      $html.='<td class="td-input-container">
                <input '.$disable.' class="td-input" type="text" id="'.$k.'-2-'.$i.'" name="'.$k.'-2-'.$i.'" placeholder="0" value="'.(int) $encaissement.'">
              </td>';
    }          
    $html.='</tr>';
    $html.='<tr>';
    $html.='<td class="smaller-td">Recouvrement</td>
            <td>
              <div class="td-input" type="text" id="B-'.$k.'-3" name="B-'.$k.'-3">
              </div>
            </td>';
    for ($i=0; $i < 12; $i++) { 
      $filteredDatabyTypologybyMonth = array_values(array_filter($filteredDatabyTypology, function($objectif) use ($i) {return $objectif['mois'] == ($i+1);}));
      $recouvrement = !empty($filteredDatabyTypologybyMonth) ? $filteredDatabyTypologybyMonth[0]["recouvrement"] : 0;
      $html.='<td class="td-input-container">
                <input '.$disable.' class="td-input" type="text" id="'.$k.'-3-'.$i.'" name="'.$k.'-3-'.$i.'" placeholder="0" value="'.(int) $recouvrement.'">
              </td>';
    }          
    $html.='</tr>';
    $k++;
  }


  return $html;
}

function sql_to_Hana_delete($sql) {
  $dsn = "HANA";
  $username = "SYSTEM";
  $password = "Skatys2020";

  // Connect to HANA
  $Hanaconn = odbc_connect($dsn, $username, $password);
  if (!$Hanaconn) {
      die("Error connecting to the database: " . odbc_errormsg());
  }

  // Set schema
  $setDb = odbc_exec($Hanaconn, "SET SCHEMA SYSTEM");
  if (!$setDb) {
      echo "Error setting schema: " . odbc_errormsg() . "\n";
      odbc_close($Hanaconn);
      return false;
  }

  // Execute the delete query
  $result = odbc_exec($Hanaconn, $sql);
  if (!$result) {
      echo "Delete operation failed. Error: " . odbc_errormsg() . "\n";
      odbc_close($Hanaconn);
      return false;
  }

  // Close connection
  odbc_close($Hanaconn);
  return true;
}

function getCommentaire($projet){
  $mysqli = new mysqli("localhost", "sa", "MG+P@ssw0rd", "PRINTERS");

  if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
  }
  $query = "SELECT commentaire FROM objectifs WHERE projet = ? AND annee = 2025";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("s", $projet);
  $stmt->execute();
  $stmt->bind_result($commentaire);
  $stmt->fetch();
  $stmt->close();
  $mysqli->close();
  return $commentaire;
}

function updatecomm($projet,$annee,$commentaire){
  $mysqli = new mysqli("localhost", "sa", "MG+P@ssw0rd", "PRINTERS");

  if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
  }
    $query = "INSERT INTO objectifs (projet, annee, commentaire)
              VALUES (?, ?, ?)
              ON DUPLICATE KEY UPDATE commentaire = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("siss", $projet, $annee, $commentaire, $commentaire);
    $stmt->execute();
    $done=false;
    if ($stmt->affected_rows > 0) {
      $done= true;
    }

    $stmt->close();
    $mysqli->close();
    return $done;

}


function validate($projet,$annee,$type){
    $mysqli = new mysqli("localhost", "sa", "MG+P@ssw0rd", "PRINTERS");
    if($type=="v1"){
      $query = "UPDATE objectifs SET v1 = 1 WHERE projet = ? AND annee = ?";
    }elseif($type=="v2"){
      $query = "UPDATE objectifs SET v2 = 1 WHERE projet = ? AND annee = ?";
    }
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $projet, $annee);
    $stmt->execute();
    $done=false;
    if ($stmt->affected_rows > 0) {
      $done= true;
    }
    $stmt->close();
    $mysqli->close();
    return $done;
}

function getValidationStatus($projet,$annee){
    $mysqli = new mysqli("localhost", "sa", "MG+P@ssw0rd", "PRINTERS");

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    $query = "SELECT commentaire,v1,v2 FROM objectifs WHERE projet = ? AND annee = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("si", $projet, $annee);
    $stmt->execute();
    $stmt->bind_result($commentaire,$v1,$v2);
    if (!$stmt->fetch()) {
      $commentaire = "";
      $v1 = 0;
      $v2 = 0;
  }
    $stmt->close();
    $mysqli->close();
    return array($commentaire,$v1,$v2);
}



function generateSectionComm($prf,$commentaire,$v1,$v2){
  $disable='';
  if($prf=="directeur" && $v2==1){
    $disable='disabled';
  }
  if($prf=="commercial" && $v1==1){
    $disable='disabled';
  }
  $html='<textarea '.$disable.' id="obj-comm" cols="30" rows="10" class="obj-comm">'.$commentaire.'</textarea>';
  $html.='<div class="button-val-container">';
  if ($disable==''){
  $html.='<button class="btn-switch large-btn" onclick="insertBudget()" >Enregistrer</button>';
  }
  if ($prf=="directeur" && $v2==0 && $disable==''){
    $html.='<button class="btn-switch large-btn" onclick="insertBudget(\'valider\')" >Valider</button>';
  }
  if ($prf=='admin' && $v1==1){
    $html.='<button class="btn-switch large-btn" onclick="insertBudget(\'cloturer\')" >Cloturer</button>';
  }
  $html.='</div> ';
  return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $validation=array("",0,0);
  if (isset($_GET["action"])) {
      if ($_GET["action"]=="setproject") {
        $validation=getValidationStatus($_GET["projet"],2025);
        $stock= generateStockTableContent($_GET["projet"],$_GET["societe"]);
        $saisie= generateSaisieTableContent($_GET["projet"],$validation,$_GET["profile"]);
        $commSection=generateSectionComm($_GET["profile"],$validation[0],$validation[1],$validation[2]);
        $response = array("stock" => $stock,"saisie" => $saisie,"commenSection" => $commSection,"v1" => $validation[1],"v2" => $validation[2]);
        echo json_encode($response);
      }
      exit();
  }
  
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $data = json_decode(file_get_contents('php://input'), true);
  header('Content-Type: text/html; charset=utf-8');
  $status="failed";
  $objectifs = $data["objectifs"];
  $successCount = 0;
  $errorCount = 0;
  $timestamp = time();
  $sql='INSERT INTO OBJECTIFS_TMP select *,\''.$timestamp.'\' from OBJECTIFS where "annee"='.$objectifs[0]["annee"];
  $temp_saved= sql_to_Hana_insert($sql);
  if($temp_saved){
    $sql = 'DELETE FROM "OBJECTIFS" where "projet"=\''.$objectifs[0]["projet"].'\' and "annee"='.$objectifs[0]["annee"];
    sql_to_Hana_delete($sql);
    
    foreach ($objectifs as $objectif) {
        $sql = "INSERT INTO OBJECTIFS (\"code\",\"projet\",\"typologie\", \"vente_u\", \"vente_ca\", \"encaissement\", \"recouvrement\", \"mois\", \"annee\") 
        VALUES ('{$objectif['code']}','{$objectif['projet']}','{$objectif['typologie']}', '{$objectif['vente_u']}', '{$objectif['vente_ca']}', 
                '{$objectif['encaissement']}', '{$objectif['recouvrement']}', '{$objectif['mois']}', '{$objectif['annee']}')";
        $result = sql_to_Hana_insert($sql);
        if ($result) {
            $successCount++;
        } else {
            $errorCount++;
        }
    }
  }

  if($successCount==sizeof($objectifs)){
    $commentaire =$objectifs[0]["commentaire"];
    $annee=$objectifs[0]["annee"];
    $projet=$objectifs[0]["projet"];
    $ok=updatecomm($projet,$annee,$commentaire);
    $sql = 'DELETE FROM "OBJECTIFS_TMP" where "RNDID"='.$timestamp;
    sql_to_Hana_delete($sql);
    $status="success";
  }

  if($data["action"]=="valider"){
    validate($projet,$annee,"v1");
  }elseif($data["action"]=="cloturer"){
    validate($projet,$annee,"v2");
  }

  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode(array(
        "status" => $status,
        "COUNT" => sizeof($objectifs),
        "inserted" => $successCount,
        "failed" => $ok,
        "CommUpdated" => $ok,
        "validation"=> $data["action"]
    ));
  exit();
}

  

?>

<h1>Saisie des Objectifs </h1>
<h2 id="objectifs-project">Société</h2>
<div class="projet-container">
        <?php  generateProjectSelector();?> 
</div>   
<table border="1" class="table-obj" style="border-collapse:collapse; width: 100%; table-layout: fixed;">
  <thead class="TableHead">
    <tr>
      <th rowspan="2" colspan="2" width="30%">TYPOLOGIE</th>
      <th rowspan="2" width="10%">STOCK INITIAL</th>
      <th colspan="5" width="60%">STOCK VENTILE</th>
    </tr>
    <tr>
      <th width="20%">Disponible</th>
      <th width="20%">Réservé</th>
      <th width="20%">Soldé</th>
      <th width="20%">Bloqué</th>
      <th width="20%">Loué</th>
    </tr>  
  </thead>
  <tbody id="stock-projet-container">
  </tbody>
</table>

<br>
<table border="1"  class="table-obj" style="border-collapse:collapse; width: 100%; table-layout: fixed;">
  <thead class="TableHead">
    <tr>
      <th rowspan="2" colspan="2" width="15%">TYPOLOGIE</th>
      <th rowspan="2" width="15%">TOTAL BUDGET 2025</th>
      <th colspan="12" width="70%">2025</th>
      
    </tr>

    <tr>
      <th width="8.25%">Janvier</th>
      <th width="8.25%">Février</th>
      <th width="8.25%">Mars</th>
      <th width="8.25%">Avril</th>
      <th width="8.25%">Mai</th>
      <th width="8.25%">Juin</th>
      <th width="8.25%">Juillet</th>
      <th width="8.25%">Aout</th>
      <th width="8.25%">Septembre</th>
      <th width="8.25%">Octobre</th>
      <th width="8.25%">Novembre</th>
      <th width="8.25%">Décembre</th>

    </tr>  
  </thead>
  <tbody id="saisie-objectif-container">

  </tbody>
</table>
<div>
  <span class="span-message">*Les champs non saisies seront remplacés par des 0</span>
</div>

<tr>
  <div class="val-container">
    
  </div>
<div id="section-comm">

</div>




<script>
function updateTotal(){
  let maxTypologie=parseInt(document.getElementById("0-0-0").getAttribute('max-rows'))||0;
  let moisU=0
  let moisUCumul=0
  for (let col = 0; col < 12; col++) {
    for (let j = 0; j < maxTypologie; j++) {
    const TragetUId = document.getElementById(`T-0-${col}`);
    const srcUId = document.getElementById(`${j}-0-${col}`);
    let NospaceCell=parseFormattedNumber(srcUId.value)
    if (srcUId && !isNaN(NospaceCell)) {
      moisU += NospaceCell;
    }
    TragetUId.innerHTML = formatNumber(moisU);
    }
    moisUCumul=moisUCumul+moisU
    document.getElementById('T-0').innerHTML = formatNumber(moisUCumul);
  }
}

function updateRowSum(rowIndex, section) {
  let sum = 0;
  for (let col = 0; col < 12; col++) {
    const cellId = `${rowIndex}-${section}-${col}`;
    const cell = document.getElementById(cellId);
    let NospaceCell=parseFormattedNumber(cell.value)
    if (cell && !isNaN(NospaceCell)) {
      sum += NospaceCell;
    }

  }
  // Update the B-rowIndex-section cell with the sum
  const sumCell = document.getElementById(`B-${rowIndex}-${section}`);
  if (sumCell) {
    
    sumCell.innerHTML = formatNumber(sum);
  }
}

function formatTable(){
        let projet=document.getElementById('objectifs-project').textContent
        let commentaire= document.getElementById('obj-comm').value
        let code=""
        let maxTypologie=parseInt(document.getElementById("0-0-0").getAttribute('max-rows'))||0;
        const objectifs = [];
        for(let k=0;k<maxTypologie;k++){
          for (let i = 0; i < 12; i++) {
            code="OBJ"+(i+1)+"2025"
            const typologie = document.getElementById(`type-${k}`).innerHTML;
            const vente_u = parseInt(document.getElementById(`${k}-0-${i}`).value)||0;
            const vente_ca =parseFormattedNumber(document.getElementById(`${k}-1-${i}`).value);
            const encaissement = parseFormattedNumber(document.getElementById(`${k}-2-${i}`).value);
            const recouvrement = parseFormattedNumber(document.getElementById(`${k}-3-${i}`).value);
            const mois = i+1;
            const annee = 2025;
            const obj = {
                code,
                projet,
                typologie,
                vente_u,
                vente_ca,
                encaissement,
                recouvrement,
                mois,
                annee,
                commentaire
            };
            objectifs.push(obj);
        }
        }  
  return objectifs   
}



function validerBudget(){
  if (document.getElementById("objectifs-project").innerHTML=="Société"){
    alert("Merci de choisir un projet")
    return
  }
  init_insert_budget()
  let maxTypologie=parseInt(document.getElementById("0-0-0").getAttribute('max-rows'))||0;
  let cLasses=4
  let mois=12
  const numberRegex = /^\d*\.?\d+$/;
  for (let i = 0; i < maxTypologie; i++){
    for (let j = 0; j < cLasses; j++){
      for (let k = 0; k < mois; k++){
        const input = document.getElementById(i+"-"+j+"-"+k)
        if (input.value !== "" && !numberRegex.test(input.value)) {
              input.classList.add("input-error")
        }else{
              input.classList.remove("input-error")
        }
      }
    }
  }
}
document.addEventListener('DOMContentLoaded', function () {
    let buttonsSoc = document.querySelectorAll('.btn-projet');
    buttonsSoc.forEach(function(button) {
        button.addEventListener('click', function() {
            getData(this.getAttribute('project'),this.getAttribute('soc'));
        });
    }); 
});

function getData(projet,soc){
    document.getElementById("objectifs-project").innerHTML=projet
    let profile="<?php echo $profile ;?>"
    $.ajax({
        type: 'GET',
        url: './src/screens/importObj.php', 
        data: { 
                action:"setproject",
                projet: projet,
                societe:soc,
                profile:profile
            },
        success: function(response) {
          let data = JSON.parse(response);
          document.getElementById("stock-projet-container").innerHTML=data.stock
          document.getElementById("saisie-objectif-container").innerHTML=data.saisie
          document.getElementById("section-comm").innerHTML=data.commenSection
          let maxTypologie=parseInt(document.getElementById("0-0-0").getAttribute('max-rows'))||0;
          for (let i = 0; i < maxTypologie; i++) {
            for (let j = 0; j <= 4; j++) {
              for (let col = 0; col < 12; col++) {
                const cellId = `${i}-${j}-${col}`;
                const cell = document.getElementById(cellId);
                if (cell) {
                  cell.addEventListener('input', function() {
                    updateTotal();
                    updateRowSum(i, j);
                  });
                  if( j==1){
                    cell.addEventListener('input', function() {
                      const cellId = `${i}-2-${col}`;
                      const enCell = document.getElementById(cellId);
                      let typo=document.getElementById(`type-${i}`).innerHTML
                      let percent=0.1
                      if( projet =='SH'){
                        percent=0.3
                      }else if(projet=='OP' && typo=='Bureau'){
                        percent=0.2
                      }
                      else if(projet=='MT'){
                        percent=0.15
                      }
                      if (enCell) {
                        let intVal=parseFormattedNumber(cell.value)
                        enCell.value = intVal * percent;
                        updateRowSum(i, 2);
                        document.getElementById(`B-${i}-${j}`).innerHTML=formatNumber(document.getElementById(`B-${i}-${j}`).innerHTML)
                      }
                      
                  });
                  }
                }
              }
            }
          }

            document.querySelectorAll(".table-obj td,td input").forEach(td => {
              const content = parseFloat(td.innerText);
              if (!isNaN(content)) {  // Check if content is a valid number
                  const formattedNumber = Number.isInteger(content)
                      ? content.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")
                      : content.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");  // Two decimal places for floats
                  td.innerText = formattedNumber;
              }
            })
          document.querySelectorAll("td input").forEach(input => {
              const content = parseFloat(input.value);
              if (!isNaN(content)) {  
                input.value = formatNumber(content);
              }
            }) 
          for (let i = 0; i < maxTypologie; i++) {
            updateRowSum(i, 2)
            updateRowSum(i, 3)

            for (let j = 0; j < 4; j++) {
                  updateRowSum(i, j)
            }
          }
          
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}

function formatNumber(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}
function parseFormattedNumber(formattedNumber) {
  return parseFloat(formattedNumber.replace(/\s/g, ''))||0;
}
function insertBudget(action="insert"){
  
    let x = document.getElementById("snackbar");

    if (document.getElementById("objectifs-project").innerHTML=="Société"){
        x.innerHTML="Merci de choisir un projet"
        x.className = "show error-message";
        setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
    return
  }
  
      const objectifs = formatTable();  
        const payload = JSON.stringify({
            action:action,
            objectifs: objectifs
        });
        fetch('./src/screens/importObj.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8'
            },
            body: payload
        })
        .then(response => {
            const contentType = response.headers.get('Content-Type');
            if (contentType && contentType.includes('application/json')) {
              return response.json();
            }
            return response.text();
          }
        )
        .then(data => {
            if (data["status"]) {
              if (data.status=="success"){
              x.innerHTML="Objectifs enregistrés avec succés!"
              x.className = "show success-message";
              setTimeout(function(){ x.className = x.className.replace("show success-message", ""); }, 3000);
            }else{
              x.innerHTML="Erreur lors de l'enregistrement des objectifs, concatctez vos administrateur pour recupérer les données perdues"
              x.className = "show error-message";
              setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 5000);
            }
          } else {
              x.innerHTML="Erreur lors de l'enregistrement des objectifs, concatctez vos administrateur pour recupérer les données perdues"
              x.className = "show error-message";
              setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 5000);
            }

            }
        )
        .catch(error => {
            console.error('Error sending data:', error);
        });
        return "success"
}


</script>


