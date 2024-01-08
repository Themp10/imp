<?php
$base=['AM_PROINVEST_TEST','AM_PROINVEST','YASSMINE_FONCIERE'];
include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."hana_connection.php";
function sql_from_Hana($sql){
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

function list_da_user($base){
    $user=$_SESSION['user'];
    $sql='SELECT "DocNum","DocEntry" FROM "'.$base.'"."OPRQ" where "Requester"=\''.$user.'\'';
    $list=sql_from_Hana($sql);
    return $list;
}
function check_appro($da,$base){
    global $dataBase;
    $sql='SELECT "Status" FROM "'.$base.'"."OWDD" where "DocEntry"='.$da;
    $a=get_data_from_Hana($sql);
    $status="";
    switch ($a[0]["Status"]) {
        case "W":
            $status="Approbation en attente";
            break;
        case "N":
            $status="Refusée";
            break;
        case "Y":
            $status="Approuvée";
            break;
    }
    return $status;
}
$a=list_da_user('YASMINE_FONCIERE');

foreach ($a as $item) {
    $docentry=$item["DocEntry"];
    $n=check_appro($docentry,'YASMINE_FONCIERE');
    echo $item["DocNum"]." : ".$n."<br>";
}



?>

<div class="da-list-container">
    
</div>