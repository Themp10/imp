<?php
//include "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

// Fonction pour interroger les OIDs SNMP
function snmp_get_value($ip, $community, $oid) {
    $result = @snmp2_get($ip, $community, $oid);
    if ($result === false) {
        return false;
    }
    return str_replace(['Counter32: ', 'STRING: ', 'INTEGER: '], '', $result);
}

function generate_user_printers() {
    $conn = new mysqli("localhost", "sa", "MG+P@ssw0rd", "PRINTERS");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM imprimantes_users";
    $result = $conn->query($sql);

    $html = '<h3>Imprimantes Utilisateurs</h3>
             <div class="printers-grid" id="printers-grid">';

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['Utilisateur']);
        $ip   = $row['ip'];

        $levels = ['Black'=>0,'Cyan'=>0,'Magenta'=>0,'Yellow'=>0];
        $offline = false;

        $colors = [
            'Black'   => '#231F20',
            'Cyan'    => '#0098CD',
            'Magenta' => '#ED0973',
            'Yellow'  => '#FFD100'
        ];

        $session = @new SNMP(SNMP::VERSION_2C, $ip, 'public', 500000, 0); // 0.2s timeout, no retry
        if (!$session) {
            $offline = true;
        } else {
            $session->valueretrieval = SNMP_VALUE_PLAIN;
            $session->quick_print = true;
            $session->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;

            $cartridges = [
                'Black'   => ['current' => $row['tonerBlackLevel'], 'max' => $row['maxtonerBlackLevel']],
                'Cyan'    => ['current' => $row['tonerColorCyanLevel'], 'max' => $row['maxtonerColorCyanLevel']],
                'Magenta' => ['current' => $row['tonerColorMagentaLevel'], 'max' => $row['maxtonerColorMagentaLevel']],
                'Yellow'  => ['current' => $row['tonerColorYellowLevel'], 'max' => $row['maxtonerColorYellowLevel']],
            ];

            foreach ($cartridges as $label => $oids) {
                $current = @$session->get($oids['current']);
                $max     = @$session->get($oids['max']);

                if ($current === false || $max === false) {
                    $offline = true;
                    break;
                }

                $current = intval($current);
                $max     = intval($max);

                if ($max > 0) {
                    $levels[$label] = max(0, min(100, round(($current / $max) * 100)));
                } else {
                    $levels[$label] = max(0, min(100, $current));
                }
            }

            $session->close();
        }

        // Render card
        $html .= '<a href="http://' . htmlspecialchars($ip) . '" target="_blank" class="printer-link">
                  <div class="user-printer-card">';
        $html .= '<h4>' . $name . '</h4>';
        if ($offline) {
            $html .= '<div class="offline"><h4> '. $name .' </h4>
            <p>Imprimante Hors ligne</p></div>';
        }

        $html .= '<div class="bars">';
        foreach ($levels as $label => $percent) {
            $color = $colors[$label];
            $html .= '<div class="bar">
                        <div class="fill" style="height:'.$percent.'%;background:'.$color.';">'
                        . ($percent > 0 ? $percent.'%' : '') .
                      '</div>
                      </div>';
        }

        $html .= '</div></div></a>';
    }

    $html .= '</div>';
    return $html;
}



function generate_op(){
    $html='<h3>Imprimantes Open Spaces</h3>
            <div class="printers-container" id="printers-container">';
    $printerIPs = [
        '172.28.0.156', 
        '172.28.1.156', 
    ];

    $oids = [
        'sysDescr' => '1.3.6.1.2.1.1.1.0',
        'printerStatus' => '1.3.6.1.2.1.25.3.5.1.1.1',
        'tonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.9.1.1',
        'maxTonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.8.1.1',
        'totalPagesPrinted' => '1.3.6.1.2.1.43.10.2.1.4.1.1',
        'location' => '1.3.6.1.2.1.1.6.0',
    ];

    foreach ($printerIPs as $printerIP) {
        // Tester la connectivité en récupérant l'OID sysDescr
        $sysDescr = snmp_get_value($printerIP, 'public', $oids['sysDescr']);
        
        if ($sysDescr === false) {
            // Afficher un message d'erreur si l'imprimante est hors connexion
            $html .=  "
            <div class=\"printer-container\">
                <div class=\"printer-row\">
                    <p class=\"printer-text\">Erreur : Impossible de se connecter à l'imprimante {$printerIP}.</p>
                </div>
            </div>";
            continue; // Passer à l'imprimante suivante
        }

        // Interroger chaque OID et stocker les résultats
        $printerInfo = [];
        foreach ($oids as $key => $oid) {
            $printerInfo[$key] = snmp_get_value($printerIP, "public", $oid);
        }

        // Calculer le pourcentage du niveau de toner
        $printername = str_replace('"', '', explode(';', $printerInfo['sysDescr'])[0]);
        $printerlocation = str_replace('"', '', $printerInfo['location']);
        $tonerLevel = $printerInfo['tonerLevelBlack'];
        $maxTonerLevel = $printerInfo['maxTonerLevelBlack'];
        $tonerLevelPercentage = ($maxTonerLevel > 0) ? ($tonerLevel / $maxTonerLevel) * 100 : 0;

        // Modèle HTML
        $html .= "
        <div class=\"printer-container\">
            <div class=\"printer-row\">
                <div class=\"printer-column\">
                    <p class=\"printer-text\">{$printerIP}</p>
                    <p class=\"printer-text\">{$printername}</p>
                    <p class=\"printer-text\">{$printerlocation}</p>
                    <p class=\"printer-text\">Pages imprimées: {$printerInfo['totalPagesPrinted']}</p>
                </div>
                <div class=\"printer-column\">
                    <img src=\"./assets/3345.png\" alt=\"Logo\" class=\"printer-img\">
                </div>
            </div>
            <div class=\"printer-row\">
                <p class=\"printer-toner-level\">Toner : ".round($tonerLevelPercentage, 2)."%</p>
                <div class=\"printer-level-container\">
                    <div class=\"printer-level\" style=\"width: {$tonerLevelPercentage}%\"></div>
                </div>
            </div>
        </div>";
    }
     $html .= "</div>";
    return $html;
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="printers") {
            $op=generate_op();
            $users=generate_user_printers();
            echo $op.$users;
        }
        exit();
    }
        
    }  
?>

<h1>Liste des imprimantes</h1>
<button class="btn-switch" onclick="refreshPrinters()">Actualiser</button>
<div id="container-pr">
    <h2 id="printer-search-title" hidden>Collecting Printers Data ... </h2>
    <img src="./assets/spinner.gif" alt="spinner" class="spinner" id="spinnerp" hidden>
    <div id="printers-container">

    </div>
</div>    

<script>

function refreshPrinters(){
    document.getElementById("printers-container").innerHTML="";
    document.getElementById("container-pr").classList.add('centred-image');
    document.getElementById("spinnerp").hidden=false;
    document.getElementById("printer-search-title").hidden=false;
    $.ajax({
        type: 'GET',
        url: './src/screens/printers.php',
        headers: {
                'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
            action:"printers",
        },
        success: function (response) {
            document.getElementById("spinnerp").hidden=true;
            document.getElementById("printer-search-title").hidden=true;
            document.getElementById("container-pr").classList.remove('centred-image');
            document.getElementById("printers-container").innerHTML=response;      
        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
    
}
</script>