<?php
$projets=["WL", "SH", "KPC", "MNO", "OP", "CP", "BA", "UP", "UPBC","ZT"];
$projects = [
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



function generateProjectSelector(){
    global $projects;
    $soc="";
$projets=["WL", "SH", "KPC", "MNO", "OP", "CP", "BA", "UP", "UPBC","ZT"];

    foreach (array_keys($projects) as $project) {
      switch ($project) {
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

  foreach ($projects[$projet] as $typology) {
    $tmp=0;
    $sql='select "StatutBien","U_StatutBien",count(*) as "U",TO_DECIMAL(sum("Price"),18,2) as "CA"   from "V_OITM"
          where  "U_Projet"=\''.$projet.'\'   and "TypeBien"=\''.$typology.'\'
          group by "StatutBien","U_StatutBien" order by "U_StatutBien"';
    $data=sql_from_Hana_queryStock($sql);

    $sql='select * from  "V_ORDR" 
          where "DocEntry" in(  select "DocEntry" from  "V_RDR1" where "ItemCode" in(select "ItemCode" from  "V_OITM" where  "U_Projet"='WL' and "U_StatutBien"='2'))
          and "Societe"='CASA_COLIVING' and "CANCELED"='N'';


    $TU=$TCA=$uL=$caL=$uLO=$caLO=$uR=$caR=$uS=$caS=$uB=$caB=0;  
    foreach ($data as $row) {
      if($row["U_StatutBien"]=='0'){
        $uL=$row["U"];
        $caL=$row["CA"];
      }elseif ($row["U_StatutBien"]=='1') {
        $uLO=$row["U"];
        $caLO=$row["CA"];
      }elseif ($row["U_StatutBien"]=='2') {
        $uR=$row["U"];
        $caR=$row["CA"];
      }elseif ($row["U_StatutBien"]=='6') {
        $uS=$row["U"];
        $caS=$row["CA"];
      }elseif ($row["U_StatutBien"]=='8') {
        $uB=$row["U"];
        $caB=$row["CA"];
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
function generateSaisieTableContent($projet){
  global $projects;
  $k=0;
  foreach ($projects[$projet] as $typology) {
    $max=sizeof($projects[$projet]);
  $html.='<tr>';
  $html.='<td rowspan="4">'.$typology.'</td>
          <td class="smaller-td">Ventes U</td>
          <td>
            <div class="td-input" type="text" id="B-'.$k.'-0" name="B-'.$k.'-0" >
            </div>
          </td>';
  for ($i=0; $i < 12; $i++) { 
    $html.='<td class="td-input-container">
              <input class="td-input" type="text" id="'.$k.'-0-'.$i.'" name="'.$k.'-0-'.$i.'" max-rows="'.$max.'" placeholder="0">
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
    $html.='<td class="td-input-container">
              <input class="td-input" type="text" id="'.$k.'-1-'.$i.'" name="'.$k.'-1-'.$i.'" placeholder="0">
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
    $html.='<td class="td-input-container">
              <input class="td-input" type="text" id="'.$k.'-2-'.$i.'" name="'.$k.'-2-'.$i.'" placeholder="0">
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
    $html.='<td class="td-input-container">
              <input class="td-input" type="text" id="'.$k.'-3-'.$i.'" name="'.$k.'-3-'.$i.'" placeholder="0">
            </td>';
  }          
  $html.='</tr>';
  $k++;
  }


  return $html;
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
  if (isset($_GET["action"])) {
      if ($_GET["action"]=="setproject") {
        $stock= generateStockTableContent($_GET["projet"],$_GET["societe"]);
        $saisie= generateSaisieTableContent($_GET["projet"]);
        $response = array("stock" => $stock,"sasie" => $saisie);
        echo json_encode($response);
      }
      exit();
  }
  
}
?>

<h1>Saisie des Objectifs </h1>
<h2 id="objectifs-project">Société</h2>
<div class="projet-container">
        <?php  generateProjectSelector();?> 
</div>   
<table border="1"  style="border-collapse:collapse; width: 100%; table-layout: fixed;">
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
<table border="1" style="border-collapse:collapse; width: 100%; table-layout: fixed;">
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
<div class="button-val-container">
  <button class='btn-switch' onclick="validerBudget()" >Valider</button>
</div> 




<script>

function updateRowSum(rowIndex, section) {
  let sum = 0;
  console.log("Im here : ",`B-${rowIndex}-${section}`)
  // Loop through each cell in the row to calculate the sum
  for (let col = 0; col < 12; col++) {
    const cellId = `${rowIndex}-${section}-${col}`;
    const cell = document.getElementById(cellId);

    if (cell && !isNaN(parseFloat(cell.value))) {
      sum += parseFloat(cell.value);
    }
  }

  // Update the B-rowIndex-section cell with the sum
  const sumCell = document.getElementById(`B-${rowIndex}-${section}`);
  console.log(sumCell)
  if (sumCell) {
    
    sumCell.innerHTML = sum;
  }
}

function formatTable(){

}

function validerBudget(){
  if (document.getElementById("objectifs-project").innerHTML=="Société"){
    alert("Merci de choisir un projet")
    return
  }
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
            getApprovalDA(this.getAttribute('project'),this.getAttribute('soc'));
        });
    }); 
});

function getApprovalDA(projet,soc){
    document.getElementById("objectifs-project").innerHTML=projet
    console.log(soc)
    $.ajax({
        type: 'GET',
        url: './src/screens/importObj.php', 
        data: { 
                action:"setproject",
                projet: projet,
                societe:soc   
            },
        success: function(response) {
          let data = JSON.parse(response);
          document.getElementById("stock-projet-container").innerHTML=data.stock
          document.getElementById("saisie-objectif-container").innerHTML=data.sasie
          let maxTypologie=parseInt(document.getElementById("0-0-0").getAttribute('max-rows'))||0;
          // Loop through each row group (0 to k)
          for (let i = 0; i < maxTypologie; i++) {
            // Loop through each section in the row group (U or CA)
            for (let j = 0; j <= 4; j++) {
              // Loop through each cell in the row group
              for (let col = 0; col < 12; col++) {
                const cellId = `${i}-${j}-${col}`;
                const cell = document.getElementById(cellId);

                // Add event listener to each cell
                if (cell) {
                  cell.addEventListener('input', function() {
                    updateRowSum(i, j);
                  });
                }
              }
            }
          }
 
            document.querySelectorAll("td").forEach(td => {
            const content = parseFloat(td.innerText);
            if (!isNaN(content)) {  // Check if content is a valid number
                const formattedNumber = Number.isInteger(content)
                    ? content.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ")
                    : content.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");  // Two decimal places for floats
                td.innerText = formattedNumber;
            }
          });
        
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}





</script>


