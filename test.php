<?php

function getTonerLevel($ip_address, $oid) {
    $tonerLevel = snmpget($ip_address, "public", $oid);
    if ($tonerLevel === false) {
        return "Error in SNMP request";
    }

    //return explode(":",$tonerLevel)[1];
    return $tonerLevel;
}

// Example usage
// $printer_ip = "172.28.0.156";  
// $toner_now = (int)getTonerLevel($printer_ip, "1.3.6.1.2.1.43.11.1.1.9.1.1");  
// $toner_max = (int)getTonerLevel($printer_ip, "1.3.6.1.2.1.43.11.1.1.8.1.1");  
// $percent=100*($toner_now / $toner_max);
// echo $percent." % ";

// $printer_ip = "172.28.1.156";  
// $toner_now = (int)getTonerLevel($printer_ip, "1.3.6.1.2.1.43.11.1.1.9.1.1");  
// $toner_max = (int)getTonerLevel($printer_ip, "1.3.6.1.2.1.43.11.1.1.8.1.1");  
// $percent=100*($toner_now / $toner_max);
// echo $percent." % ";

$printer_ip = "172.28.0.1";  
$total_printed = getTonerLevel($printer_ip, "1.3.6.1.4.1.674.10892.5.4.600.60.1.15.1.1");  

echo $total_printed;


?>



<H1>Hello</H1>