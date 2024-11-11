
<?php
$oids = [
    'sysDescr' => '1.3.6.1.2.1.1.1.0',
    'printerStatus' => '1.3.6.1.2.1.25.3.5.1.1.1',
    'tonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.9.1.1',
    'maxTonerLevelBlack' => '1.3.6.1.2.1.43.11.1.1.8.1.1',
    'totalPagesPrinted' => '1.3.6.1.2.1.43.10.2.1.4.1.1',
    'location' => '1.3.6.1.2.1.1.6.0',
    // Example placeholders for paper levels
    'tray1PaperLevel' => '1.3.6.1.2.1.43.8.2.1.10.1',
    'tray2PaperLevel' => '1.3.6.1.2.1.43.8.2.1.10.2',
    'tray3PaperLevel' => '1.3.6.1.2.1.43.8.2.1.10.3',
];

$printerIP = "172.28.0.156"; // Replace with actual printer IP
$community = "public"; // SNMP community string

foreach ($oids as $name => $oid) {
    $result = snmpget($printerIP, $community, $oid);
    echo "{$name}: {$result}\n";
    echo "<br>";
}
?>