<?php
$printerIPs = [
   // '172.28.0.156', 
    '172.28.1.156', 
];
$community = 'public'; // Remplacez par votre chaîne communautaire SNMP

// OIDs SNMP à interroger
$oids = [
    'sysDescr' => '1.3.6.1.2.1.1.1.0',
    'printerStatus' => '1.3.6.1.2.1.25.3.5.1.1.1',
    'tonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.9.1.1',
    'maxTonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.8.1.1',
    'totalPagesPrinted' => '1.3.6.1.2.1.43.10.2.1.4.1.1',
    'location' => '1.3.6.1.2.1.1.6.0',
];

// Fonction pour interroger les OIDs SNMP
function snmp_get_value($ip, $community, $oid) {
    $result = @snmp2_get($ip, $community, $oid);
    if ($result === false) {
        return false;
    }
    return str_replace(['Counter32: ', 'STRING: ', 'INTEGER: '], '', $result);
}

echo "<h1>Liste des imprimantes</h1>";
// Boucle sur chaque IP d'imprimante
echo '<div class="printers-container">';
foreach ($printerIPs as $printerIP) {
    // Tester la connectivité en récupérant l'OID sysDescr
    $sysDescr = snmp_get_value($printerIP, $community, $oids['sysDescr']);
    
    if ($sysDescr === false) {
        // Afficher un message d'erreur si l'imprimante est hors connexion
        echo "
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
        $printerInfo[$key] = snmp_get_value($printerIP, $community, $oid);
    }

    // Calculer le pourcentage du niveau de toner
    $printername = str_replace('"', '', explode(';', $printerInfo['sysDescr'])[0]);
    $printerlocation = str_replace('"', '', $printerInfo['location']);
    $tonerLevel = $printerInfo['tonerLevelBlack'];
    $maxTonerLevel = $printerInfo['maxTonerLevelBlack'];
    $tonerLevelPercentage = ($maxTonerLevel > 0) ? ($tonerLevel / $maxTonerLevel) * 100 : 0;

    // Modèle HTML
    echo "
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
echo '</div>';
?>
