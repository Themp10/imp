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
    "UP" =>  ["Appartement"],
    "UPBC"=> ["Bureau"],
    "ZT" =>  ["Appartement", "Magasin"]
];
$statuts = [
    "Disponible" =>  0,
    "Réservé" =>  2,
    "Soldé" => 6,
    "Bloqué" => 8,
    "Loué" =>  5
];
function getSoc($projet){
    $soc="";
    switch ($projet) {
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
    return $soc;
}
function Hana_Reporting($sql){
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
function getProjets($id){
    
    switch ($id) {
    case "WL":
        $soc="WE LIVE";
        break;
    case "SH":
        $soc="SOHAÜS";
        break;
    case "KPC":
        $soc="KPC";
        break;
    case "MNO":
        $soc="MNO";
        break;
    case "OP":
        $soc="OCEAN PARK";
        break;
    case "CP":
        $soc="CENTRAL PARK";
        break;
    case "BA":
        $soc="BEL AIR";
        break;
    case "UP":
        $soc="UPTOWN";
        break;
    case "UPBC":
        $soc="UPTOWN BC";
        break;
    case "ZT":
        $soc="ZENATA TOWER";
        break;
    case "MT":
        $soc="MTOWER";
        break;
    default:
        $soc="";
        break;
    }
    return $soc;
}
function generateProjectList($projets){
    $html="";
    foreach($projets as $projet){
        $soc=getProjets($projet);
        $html.="<button class='project-btn' id='project-$projet' onclick='selecteProject(this)'>$soc</button>";
    }
    return $html;
}
function getCaSTock($projet){
    global $projects;
    $html1="";
    $html2="";
    $TtU=$TtCA=$TuL=$TuR=$TuS=$TuB=0;
    $societe=getSoc($projet);
    foreach ($projects[$projet] as $typology) {
        $tmp=0;
        $sql='select "StatutBien","U_StatutBien",count(*) as "U",TO_DECIMAL(sum("Price"),18,2) as "CA"   from "V_OITM"
            where  "U_Projet"=\''.$projet.'\'   and "TypeBien"=\''.$typology.'\'
            group by "StatutBien","U_StatutBien" order by "U_StatutBien"';
        $data=Hana_Reporting($sql);

        $sql='select "V_OITM"."U_StatutBien",count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" 
                from "V_ORDR" "V_ORDR" 
                LEFT OUTER JOIN  "V_RDR1" "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
                LEFT OUTER JOIN  "V_OITM" "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and "V_RDR1"."LineNum"=\'0\' and "V_OITM"."U_Projet"=\''.$projet.'\' and  "TypeBien"=\''.$typology.'\'
                where   "V_ORDR"."CANCELED"=\'N\'
                group by "V_OITM"."U_StatutBien" order by "V_OITM"."U_StatutBien"';
        $data2=Hana_Reporting($sql);

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
        $TCA=($caL+$caR+$caS+$caB+$caLO)/1000000;
        $TCA = round($TCA, 2);
        $html1.='<tr>
                    <td class="table-row-header">'.$typology.'</td> 
                    <td>'.$TU.'</td>
                    <td>'.$TCA.'</td>
                    <td>15</td>
                </tr>';
        $html2.='<tr>
                    <td class="table-row-header">'.$typology.'</td> 
                    <td>'.$uB.'</td>
                    <td>'.$uR.'</td>
                    <td>'.$uS.'</td>
                    <td>'.$uL.'</td>
                    <td>287</td>
                </tr>';    
        $TtU=$TtU+$TU;
        $TtCA=$TtCA+$TCA;
        $TuL=$TuL+$uL;
        $TuR=$TuR+$uR;
        $TuS=$TuS+$uS;
        $TuB=$TuB+$uB;
    }
    $TtCA = number_format($TtCA, 2); 
    $html1.='<tr class="table-total-row">
                <td>Total</td> 
                <td>'.$TtU.'</td>
                <td>'.$TtCA.'</td>
                <td>15</td>
            </tr>';
    $html2.='<tr class="table-total-row">
                <td>Total</td> 
                <td>'.$TuB.'</td>
                <td>'.$TuR.'</td>
                <td>'.$TuS.'</td>
                <td>'.$TuL.'</td>
                <td>287</td>
            </tr>';    
    return [$html1, $html2];           
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="stock") {
            $projet=$_GET["projet"];
            [$programme, $stock] = getCaSTock($projet);
            $response = array("programme" => $programme,"stock" => $stock);
            echo json_encode($response);
        }
        exit();
    }
    
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<h1>Situation Commerciale</h1>
<div class="search">
    <fieldset class="projects-box">
        <legend class="filter-type-title">Projet</legend>
        <div class="projects-list">
            <?= generateProjectList($projets);?>
        </div>
                <div class="date-from-to-container">
                    <div class="fromto-container">
                        <label for="dateFrom">Du</label>
                        <input type="date" class="select-filter" id="comm-date-from" name="dateFrom"  onchange="handledSelectChange()">
                    </div>
                    <div class="fromto-container">
                        <label for="dateTo">Au</label>
                        <input type="date" class="select-filter" id="comm-date-to" name="dateTo"  onchange="handledSelectChange()">
                    </div>

                </div>
    </fieldset>
</div>
<h3 class="critere-search">*Filtres selectionnés : Projet <span id="selected-pro"> Aucun</span> Période : <span id="selected-date"> Aucune</span></h3>
<div class="first-row">
    <div id="table-left">
        <table id="table-programme">
            <thead>
                <tr><th rowspan="2"></th><th colspan="3">Programme</th></tr>
                <tr><th># d'unités</th><th>CA prévisionnel (MDH)</th><th>Prix au M² moyen</th></tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="table-right">
        <table id="table-stck">
            <thead>
                <tr><th colspan="6">Etat du stock</th></tr>
                <tr><th></th><th>Stock Bloqué</th><th>Stock Réservé</th><th>Sotck Soldé</th><th>Sotck Disponible</th><th>Sotck Livré</th></tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="second-row">
    <table id="table-synth">
        <thead>
            <tr><th colspan="17">Suivi de la commercialisation</th></tr>
            <tr><th rowspan="2">Synthèse</th><th colspan="5">Rapport P-1</th><th colspan="9">Réalisation P</th><th colspan="2">Prévision P+1</th></tr>
            <tr><th>Unités vendues</th><th>Unités désistées du mois</th><th>Unités vendues cumulées</th><th>en % du programme</th><th>CA cumulé</th>
                <th>Unités vendues</th><th>Unités désitées</th><th>Unités vendues cumulées</th><th>Objectif en U</th><th>CA Réalisé (MDH)</th><th>Ecart VS Objectif (U)</th><th>CA Cumulé à data</th><th>Encaissé à date</th><th>Taux d'encaissement</th>
                <th>Objectif en U</th><th>CA (MDH)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="table-row-header">Appartement</td><td>6</td><td>0</td><td>419</td><td>98%</td><td>366.5</td>
                <td>3</td><td>2</td><td>420</td><td>1</td><td>2.3</td><td>2</td><td>367.58</td><td>287.7</td><td>78%</td><td>2</td><td>2.05</td>
            </tr>
            <tr><td class="table-row-header">Magasin</td><td>6</td><td>0</td><td>419</td><td>98%</td><td>366.5</td>
                <td>3</td><td>2</td><td>420</td><td>1</td><td>2.3</td><td>2</td><td>367.58</td><td>287.7</td><td>78%</td><td>2</td><td>2.05</td>
            </tr>
            <tr><td class="table-row-header">Bureau</td><td>6</td><td>0</td><td>419</td><td>98%</td><td>366.5</td>
                <td>3</td><td>2</td><td>420</td><td>1</td><td>2.3</td><td>2</td><td>367.58</td><td>287.7</td><td>78%</td><td>2</td><td>2.05</td>
            </tr>
            <tr class="table-total-row"><td>Total</td><td>6</td><td>0</td><td>419</td><td>98%</td><td>366.5</td>
                <td>3</td><td>2</td><td>420</td><td>1</td><td>2.3</td><td>2</td><td>367.58</td><td>287.7</td><td>78%</td><td>2</td><td>2.05</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="third-row">
    <div id="graphe1"><canvas id="stockChart"></canvas></div>
    <div id="graphe2"><canvas id="objrealChart"></canvas></div>
    <div id="graphe3"><canvas id="resteChart"></canvas></div>
</div>
<script>
function handledSelectChange(){
    const selectedProject = document.querySelector(".selectedProject");
    if (selectedProject) {
        projet= selectedProject.id.split("-")[1];
    } else {
        alert("Merci de selectionner un projet");
        return 
    }
    let dateFrom=(document.getElementById('comm-date-from').valueAsDate.getMonth()+1) + "/"+ document.getElementById('comm-date-from').valueAsDate.getFullYear() 
    let dateTo=(document.getElementById('comm-date-to').valueAsDate.getMonth()+1) + "/"+ document.getElementById('comm-date-to').valueAsDate.getFullYear() 

    document.getElementById('selected-date').textContent=(dateFrom==dateTo)?dateFrom:dateFrom+" - "+dateTo
}
function selecteProject(clickedBtn) {
    const allButtons = document.querySelectorAll('.project-btn');
    allButtons.forEach(btn => btn.classList.remove('selectedProject'));
    clickedBtn.classList.add('selectedProject');
    let projet=clickedBtn.id.split("-")[1]
    document.getElementById('selected-pro').textContent=projet
    getStock(projet)
}
function getStock(projet){
        $.ajax({
        type: 'GET',
        url: './src/screens/avancement.php',
        dataType: 'json',
        headers: {
                'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
            action:"stock",
            projet:projet
        },
        success: function (response) {
            console.log(response)
            document.querySelector("#table-programme tbody").innerHTML=response.programme
            document.querySelector("#table-stck tbody").innerHTML=response.stock

        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}
    let stockChartInstance = null;
    let objvsreaChartInstance = null;
    let resteencChartInstance = null;
function generateChart1(){
        if (stockChartInstance) {
            stockChartInstance.destroy();
        }
        let data={
            "labels":[ "Appartement", "Magasin", "Bureau"],
            "qte":[ "65", "19", "18" ]
        }
        let labels=data.labels
        let values=data.qte
        let ctx = document.getElementById('stockChart').getContext('2d');
        const colorMap = {'Appartement': '#547471','Magasin': '#A09D92','Bureau': '#90b1ad'}
        const barColors = labels.map(label => colorMap[label] || '#cccccc')
        // Create the new chart with the data labels and size control
        stockChartInstance = new Chart(ctx, {
            type: 'doughnut',  // Use 'doughnut' for a donut chart (similar to pie)
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: barColors
                }]
            },
            options: {
                plugins: {
                    datalabels: {
                        anchor: 'start',
                        align:'end',
                        color: '#000',
                        font: {weight: 'bold'},
                    },
                    title: {
                        display: true,
                        text: 'Unités vendues',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }  
                }
            },
            plugins:[ChartDataLabels]
        });
}
generateChart1()

function generateChart2(){
    if (objvsreaChartInstance) {
            stockChartInstance.destroy();
        }
    const labels=["Appartement","Bureau"]
    const data = {
            labels: labels,
            datasets: [
                {
                label: 'Objectif',
                data: [5,6],
                backgroundColor:'#547471'
                },
                {
                label: 'Réalisé',
                data: [2,1],
                backgroundColor:'#A09D92'
                }
            ]
            };
        const colorMap = {'Objectif': '#547471','Réalisé': '#A09D92'}
        const barColors = labels.map(label => colorMap[label] || '#cccccc')
        let ctx = document.getElementById('objrealChart').getContext('2d');

        const newChart = new Chart(ctx, {
        type: "bar",
        data: data,
         options: {
            responsive: true,
            maintainAspectRatio: false, 
            plugins: {
                datalabels: {
                    anchor: 'start',
                    align:'end',
                    color: '#000',
                    font: {weight: 'bold'},
                } ,
                title: {
                    display: true,
                    text: 'Réalisé vs Budget',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                } 
            }
            
        },
        plugins:[ChartDataLabels]
        });
}
generateChart2()
function generateChart3() {
    if (resteencChartInstance) {
        resteencChartInstance.destroy();
    }

    let data = {
        labels: ["Appartement", "Magasin", "Bureau"],
        qte: [65, 19, 18]
    };

    let labels = data.labels;
    let values = data.qte.map(Number);
    let ctx = document.getElementById('resteChart').getContext('2d');
    const colorMap = {
        'Appartement': '#547471',
        'Magasin': '#A09D92',
        'Bureau': '#90b1ad'
    };
    const barColors = labels.map(label => colorMap[label] || '#cccccc');

    resteencChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: barColors
            }]
        },
        options: {
            animations: {
                radius: {
                    duration: 400,
                    easing: 'linear',
                    loop: (context) => context.active
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    color: '#fff',
                    backgroundColor:'#070707a6',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: (value, ctx) => {
                        const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${value}\n(${percentage}%)`;
                    }
                },
                title: {
                    display: true,
                    text: 'Reste à encaisser',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

generateChart3()
</script>