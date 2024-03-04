<?php
$base=["AM_PROINVEST", "AM_PROINVEST_TEST", "ANFA_19", "ANFA_REALISATION", "CASA_COLIVING", "HOLDING_TARIK", "M_PROPERTIES", "PROBAT_INVEST", "RMM_BUILDING", "YASMINE_FONCIERE"];
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
$years=[2021,2022,2023,2024,2025,2026];
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
function sql_from_Hana($sql){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);
 
    $data=[];
    // $setCharset = odbc_exec($Hanaconn, "SET CHARACTER SET UTF8");
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

function getDraftData($DocEntry,$base){
    $sql='SELECT "Dscription","Quantity" from "'.$base.'"."DRF1" where "DocEntry"=\''.$DocEntry.'\'';
    $list=sql_from_Hana($sql);
    $html="<ul>";
    foreach ($list as $item) {
        $html.="<li>".$item["Dscription"]." - Qte :  ".(int)$item["Quantity"]."</li>";
    }
    $html.="</ul>";
    return $html;
}


function list_approval($base,$year=-1){
    session_start();
    $user=$_SESSION['user'];
    //$sql='SELECT "DocEntry","Status","IsDraft",TO_VARCHAR(TO_DATE("CreateDate"), \'DD-MM-YYYY\') as "DocDate" ,"ObjType","DraftEntry"
    // from "'.$base.'"."OWDD" WHERE "OwnerID"=(select "USERID" FROM "'.$base.'"."OUSR" where "IsDraft"=\'Y\' and "USER_CODE" =\''.$user.'\')';
    $sql='SELECT "OUSR"."USER_CODE","OWDD"."DocEntry","WDD1"."Status","OWDD"."IsDraft",TO_VARCHAR(TO_DATE("OWDD"."CreateDate"), \'DD-MM-YYYY\') as "DocDate" ,
            "OWDD"."ObjType","OWDD"."DraftEntry"
            from "'.$base.'"."WDD1" as "WDD1","'.$base.'"."OWDD" as "OWDD","'.$base.'"."OUSR" as "OUSR"
            WHERE "OWDD"."WddCode"="WDD1"."WddCode" and "WDD1"."UserID"="OUSR"."USERID" and
            "OWDD"."OwnerID"=(select "USERID" FROM "'.$base.'"."OUSR" where "IsDraft"=\'Y\' and "USER_CODE" =\''.$user.'\')
            and "WDD1"."StepCode"=(select max("StepCode") from "'.$base.'"."WDD1" where "WddCode"="OWDD"."WddCode" )';
    if ($year!=-1){
        $sql.= ' and YEAR("DocDate") =\''.$year.'\'';
    }
    $list=sql_from_Hana($sql);

    $cRefus=0;
    $cWaiting=0;
    $cTocreate=0;
    foreach ($list as $item) {
        switch ($item["Status"]) {
            case "W":
                $cWaiting+=1;
                break;
            case "N":
                $cRefus+=1;
                break;
            case "Y":
                $cTocreate+=1;
                break;
        }

    }
    return array("c_refus" => $cRefus,"c_waiting" =>$cWaiting , "c_success" => $cTocreate);
}

function generateSocieteSelector(){
    global $base;
    foreach ($base as $soc) {
        echo "<button class='btn-set-soc' data-base='".$soc."'>".$soc."</button>";
    }
}

function generateYearSelector($base,$user){
    $html="";
    $sql='SELECT DISTINCT YEAR("DocDate") as "years" FROM "'.$base.'"."OPRQ" WHERE "Requester"=\''.$user.'\' ORDER BY "years" ASC';

    $list=sql_from_Hana($sql);
    foreach ($list as $year) {
        $html.="<button class='btn-set-year' data-year='".$year["years"]."'>".$year["years"]."</button>";
    }
    return $html;
}

function getDaByYear($base,$user,$year){
    $html="";
    $status="";
    $sql='SELECT "DocNum",TO_VARCHAR(TO_DATE("DocDate"), \'DD-MM-YYYY\') as "DocDate","CANCELED","DocStatus" FROM "'.$base.'"."OPRQ" WHERE "Requester"=\''.$user.'\' AND YEAR("DocDate")=\''.$year.'\' ORDER BY "OPRQ"."DocDate" DESC';
    $list=sql_from_Hana($sql);
    foreach ($list as $item) {
        $badge="warning-da";
        $status="BC à créer";
        if($item["CANCELED"]=="N" && $item["DocStatus"]=="C"){
            $badge="succes-da";
            $status="BC OK";
        }elseif($item["CANCELED"]=="Y" && $item["DocStatus"]=="C"){
            $badge="danger-da";
            $status="DA annulée";
        }
        $html.= "<button class='btn-get-detail ".$badge."' data-docnum='".$item["DocNum"]."'> ".$status."-".$item["DocNum"]." - ".$item["DocDate"]."</button>";
    }

    return array(count($list),$html);
}

function get_DA_lista($base,$user){
    $html="";
    $status="";
    $sql='SELECT "DocNum",TO_VARCHAR(TO_DATE("DocDate"), \'DD-MM-YYYY\') as "DocDate","CANCELED","DocStatus" FROM "'.$base.'"."OPRQ" WHERE "Requester"=\''.$user.'\' ORDER BY "OPRQ"."DocDate" DESC';
    $list=sql_from_Hana($sql);
    foreach ($list as $item) {
        $badge="warning-da";
        $status="BC à créer";
        if($item["CANCELED"]=="N" && $item["DocStatus"]=="C"){
            $badge="succes-da";
            $status="BC OK";
        }elseif($item["CANCELED"]=="Y" && $item["DocStatus"]=="C"){
            $badge="danger-da";
            $status="DA annulée";
        }
        $html.= "<button class='btn-get-detail ".$badge."' data-docnum='".$item["DocNum"]."'> ".$status."-".$item["DocNum"]." - ".$item["DocDate"]."</button>";
    }
    return array(count($list),$html);
    return $html;
}
function get_DA_details($numDA,$base){
    $sql='SELECT * FROM "'.$base.'"."ETAT_ACHAT" WHERE "Num DA"=\''.$numDA.'\'';
    $list=sql_from_Hana($sql);
    if (count($list)==0){
        $sql='SELECT "ItemCode","Dscription","Quantity","FreeTxt" from "'.$base.'"."PRQ1" where "DocEntry" = (SELECT "DocEntry" FROM "'.$base.'"."OPRQ" WHERE "DocNum"=\''.$numDA.'\')';
        $list=sql_from_Hana($sql);
        return array(false,$list);
    }else{
        return array(true,$list);
    }
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="setsoc") {
            $listApproval = list_approval($_GET["base"]); 
            $user=$_SESSION['user'];
            $listDa = get_DA_lista($_GET["base"],$user); 
            $listYears=generateYearSelector($_GET["base"],$user);
            $response = array("count" => $listApproval,"listyears" => $listYears, "listDa" => $listDa);
        
            // Convert to JSON and output
            echo json_encode($response);
            //echo list_approval($_GET["base"]);
        }
        elseif ($_GET["action"]=="getda") {
            if (isset($_GET["DocNum"])) {
                $daNoJson = get_DA_details($_GET["DocNum"],$_GET["base"]);
                array_walk_recursive($daNoJson, function (&$item) {
                    if (is_string($item)) {
                        $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                    }
                });
                if ($daNoJson !== null) {
                    
                    $daDetails = json_encode($daNoJson);
                    echo $daDetails;
                } else {
                    echo "Data not found.";
                }
        

            }
        }
        elseif ($_GET["action"]=="getdabyyear") {
                session_start();
                $user=$_SESSION['user'];
                $listDa = getDaByYear($_GET["base"],$user,$_GET["year"]); 
                $response = array( "c_da" =>$listDa[0] ,"dayear" => $listDa[1]);
                echo json_encode($response);
        }
        elseif ($_GET["action"]=="getapprovalDetails") {
            $list = getDraftData($_GET["docEntry"],($_GET["base"]));
            
            echo $list;
    }
        exit();
    }
    
}


?>
<h2 class="da-h2">Société : <span id="societe-span"></span> <span id="year-span"></span> </h2>
    <div class="soc-list-container">
        <?php generateSocieteSelector();?> 
    </div>   

<div class="years-container" id="years-container"></div>

<h2 class="da-h2" id="appro-header">Etat d'approbation</h2>
    <div class="approval-list-container" id="approval-list-container">
        <div class="card-approval-status danger-badge">
            <p class="p-app-da">Demandes refusées : </p>
            <p id="count-refus" class="count-approval">0</p>
        </div>
        <div class="card-approval-status warning-badge">
            <p class="p-app-da">Approbation en cours : </p>
            <p id="count-waiting" class="count-approval">0</p>
        </div>
        <div class="card-approval-status success-badge">
            <p class="p-app-da">Demandes à créer : </p>
            <p id="count-success" class="count-approval">0</p>
        </div>
    </div>  
<h2 class="da-h2">Liste des DA <span id="count-list-da"></span></h2>
<div class="btn-date-container" id="btn-date-container">
</div>
<div class="my-da-list-container">

    <div class="da-left-container" id="da-left-container">
        
    </div>
    <div class="da-right-container" id="da-detail-container">

    </div>

</div>
<script src="src/static/jquery-3.6.4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let buttonsSoc = document.querySelectorAll('.btn-set-soc');
    buttonsSoc.forEach(function(button) {
        button.addEventListener('click', function() {
            getApprovalDA(this.getAttribute('data-base'));
        });
    }); 
});

function applyYearFilter(year){
    
}
function getApprovalDA(base){
    document.getElementById("societe-span").innerHTML=base
    
    $.ajax({
        type: 'GET',
        url: './src/screens/myDA.php', 
        data: { 
                action:"setsoc",
                base: base   
            },
        success: function(response) {
            let data = JSON.parse(response);
            console.log(data.count)

            document.getElementById("count-refus").innerHTML=data.count.c_refus
            document.getElementById("count-waiting").innerHTML=data.count.c_waiting
            document.getElementById("count-success").innerHTML=data.count.c_success


            document.getElementById("years-container").innerHTML=data.listyears

            let buttonsYear = document.querySelectorAll('.btn-set-year');
            buttonsYear.forEach(function(button) {
                button.addEventListener('click', function() {
                    document.getElementById("year-span").innerHTML=this.getAttribute('data-year')
                    //fetchDaDetails(this.getAttribute('data-docnum'),document.getElementById("societe-span").innerHTML);
                });
            });


        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}





</script>
