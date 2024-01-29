<?php
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

function get_devices(){

    global $conn; 
    $sql="SELECT * FROM imprimantes";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $printers = [];

    while ($row = $result->fetch_assoc()) {
        $printers[] = $row;
    }
    return $printers;
}
function get_data_from_oid($ip_address, $oid) {
    $data = snmpget($ip_address, "public", $oid);
    if ($data === false) {
        return "Error in SNMP request";
    }

    return explode(":",$data)[1];
}


?>

<?php foreach (get_devices() as $row): ?>
    <div>  <?= get_data_from_oid($row["ip_address"], $row["oid_model"]).get_data_from_oid($row["ip_address"], $row["oid_count"])?></div>
<?php endforeach; ?>
