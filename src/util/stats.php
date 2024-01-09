<?php
function sql_from_imp($sql){
    $servername = "172.28.0.22";
    $username = "sa";
    $password = "MG+P@ssw0rd";
    $dbname = "PRINTERS";
    // Create connection

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $result = $conn->query($sql);
    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}
function ssql_from_Hana($sql){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);

    $data=[];
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
            $data[]=$row;
        }
    }
    odbc_close($Hanaconn);
    return $data;
}



function fetchAllStatistics() {
    $dataBase="YASMINE_FONCIERE";
    $queries = [
        "SELECT SUM(stock) as 'Nombre de Toner' FROM cartridges WHERE TYPE='toner'",
        "SELECT SUM(stock) as 'Nombre de Cartouche' FROM cartridges WHERE TYPE='cartouche'",
        "SELECT status, COUNT(*) as total FROM v_da GROUP BY STATUS",
        "SELECT CONCAT(c.model,' ',c.color) AS 'Toner', SUM(m.qte) AS utilisation FROM mouvements m JOIN cartridges c ON m.id_cartridge = c.id GROUP BY c.type, c.model, c.color",
        "SELECT m.user, SUM(m.qte) as utilisation FROM mouvements m  where m.type='s' GROUP BY m.user",
        "SELECT MONTH(mvt_date) as month, YEAR(mvt_date) as year, SUM(qte) as total_quantity FROM mouvements GROUP BY YEAR(mvt_date), MONTH(mvt_date) ORDER BY YEAR(mvt_date),MONTH(mvt_date)",
        "SELECT SUM(nb_printer) AS total FROM (SELECT distinct NAME,nb_printer FROM cartridges) AS tt",
        "SELECT COUNT(*) AS total FROM cartridges WHERE stock=0",

    ];
    $hanaQueries=[
        'SELECT "OPOR"."CardName",YEAR("POR1"."DocDate") as "year",MONTH("POR1"."DocDate") as "month",SUM("POR1"."Price") as "Total"
        FROM "YASMINE_FONCIERE"."POR1" as "POR1","YASMINE_FONCIERE"."OPOR" as "OPOR" 
        WHERE "POR1"."DocEntry"="OPOR"."DocEntry" and "POR1"."ItemCode"=\'DEX00203\' and "POR1"."BaseType"=\'1470000113\'  
        group by YEAR("POR1"."DocDate"),MONTH("POR1"."DocDate"),"OPOR"."CardName", "OPOR"."DocNum" order by  "OPOR"."DocNum"'
    ];
    $statistics = [];
    $a=0;
    // Loop through each query, execute it, and add the results to the statistics array
    foreach ($queries as $index => $query) {
        $result = sql_from_imp($query);
        $statistics['table' . ($index + 1)] = $result;
        $a=$index+1;
    }
    foreach ($hanaQueries as $index => $query) {
        $result = ssql_from_Hana($query);
        $statistics['table' . ($a+$index + 1)] = $result;
    }
    // Return the statistics as a JSON string
    return json_encode($statistics);
}

?>