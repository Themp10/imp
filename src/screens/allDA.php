<?php
$base=["AM_PROINVEST", "ANFA_19", "ANFA_REALISATION", "CASA_COLIVING", "HOLDING_TARIK", "M_PROPERTIES", "PROBAT_INVEST", "RMM_BUILDING", "YASMINE_FONCIERE"];
//$base=["AM_PROINVEST", "AM_PROINVEST_TEST", "ANFA_19", "ANFA_REALISATION", "CASA_COLIVING", "HOLDING_TARIK", "M_PROPERTIES", "PROBAT_INVEST", "RMM_BUILDING", "YASMINE_FONCIERE"];
$yeadDA=2024;
function generateSocieteSelectorAchat(){
    global $base;
    global $yeadDA;
    $sql='select COUNT(*) as "total" from (
        SELECT "OPRQ"."DocNum",COALESCE(COUNT("PRQ1"."DocEntry"), 0) as "count"
        FROM "OPRQ"
        LEFT JOIN "PRQ1" ON "OPRQ"."DocEntry" = "PRQ1"."DocEntry" AND "PRQ1"."TrgetEntry" IS NULL
        where "OPRQ"."CANCELED"=\'N\' and YEAR("OPRQ"."DocDate")>='.$yeadDA.' 
        GROUP BY "OPRQ"."DocNum","OPRQ"."CANCELED") where "count" != \'0\'';
    foreach ($base as $soc) {
        $count=sql_from_Hana_queryAchat($sql,$soc)[0]["total"];
        echo "<button class='btn-soc-achat' data-base='".$soc."'>".$soc." (".$count.")</button>";
    }
}
function sql_from_Hana_queryAchat($sql,$base){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);
 
    $data=[];
    //$setCharset = odbc_exec($Hanaconn, "SET NAMES UTF8");
    //$setCharset = odbc_exec($Hanaconn, "SET CHARACTER SET UTF8");
    $setDb = odbc_exec($Hanaconn, "SET SCHEMA " . $base);
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

function logger($txt){
    $filePath = "../../outputs/log.txt";
    $fp = fopen($filePath, 'a');
    fwrite($fp, $txt."\n");
    fclose($fp);
}



function getDAtoBCCountAchat($base){
    global $yeadDA;
    $html='';
    $sql='select * from (
        SELECT "OPRQ"."Requester","OPRQ"."DocNum",TO_VARCHAR(TO_DATE("OPRQ"."DocDate"),\'DD-MM-YYYY\') as "date",COALESCE(COUNT("PRQ1"."DocEntry"), 0) as "count"
        FROM "OPRQ"
        LEFT JOIN "PRQ1" ON "OPRQ"."DocEntry" = "PRQ1"."DocEntry" AND "PRQ1"."TrgetEntry" IS NULL
        where "OPRQ"."CANCELED"=\'N\' and YEAR("OPRQ"."DocDate")>='.$yeadDA.' 
        GROUP BY "OPRQ"."Requester","OPRQ"."DocNum","OPRQ"."CANCELED","OPRQ"."DocDate") where "count" != \'0\'';
    $data=sql_from_Hana_queryAchat($sql,$base);
    foreach ($data as $item) { 
        $html.="<div class='btn-da-achat' data-base='".$base."'>";
        $html.="<div class='btn-da-achat-item'>".$item["Requester"]."</div>";
        $html.="<div class='btn-da-achat-item'>".$item["DocNum"]."</div>";
        $html.="<div class='btn-da-achat-item'>".$item["count"]." Article(s)</div>";
        $html.="<div class='btn-da-achat-item'>".$item["date"]."</div>";
        $html.="</div>";
    }
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="setsoc") {
            session_start();
            $date = date('Y/m/d H:i:s');
            $user = $_SESSION['user'];
            $txt=$date." --- ".$_SESSION['user']." --- ".$_GET["base"];
            logger($txt);
            $data = getDAtoBCCountAchat($_GET["base"]); 
            $response = array("data" => $data);
            // Convert to JSON and output
            echo json_encode($response);
        }
        exit();
    }
    
}

?>



<h2 class="da-h2">Société : <span id="societe-span-achat"></span> <span id="year-span"></span> </h2>
    <div class="soc-list-container">
        <?php  generateSocieteSelectorAchat();?> 
    </div>   

<div class="years-container" id="years-container"></div>

<h2 class="list-title">Liste des DA à copier en commandes </h2>
<div class="da-container-achat" id="da-container-achat">
</div>



<script>
document.addEventListener('DOMContentLoaded', function () {
    let BtnSoc = document.querySelectorAll('.btn-soc-achat');
    BtnSoc.forEach(function(button) {
        button.addEventListener('click', function() {
            getDaData(this.getAttribute('data-base'));
        });
    }); 
});
function getDaData(base){
    document.getElementById("societe-span-achat").innerHTML=base
    $.ajax({
        type: 'GET',
        url: './src/screens/allDA.php', 
        data: { 
                action:"setsoc",
                base: base   
            },
        success: function(response) {
            let data = JSON.parse(response);
            document.getElementById("da-container-achat").innerHTML=data.data
            console.log(response)
            

        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}
</script>