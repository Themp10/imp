<?php
$base=["AM_PROINVEST", "AM_PROINVEST_TEST", "ANFA_19", "ANFA_REALISATION", "CASA_COLIVING", "HOLDING_TARIK", "M_PROPERTIES", "PROBAT_INVEST", "RMM_BUILDING", "YASMINE_FONCIERE"];
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
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


function list_approval($base){
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
    $list=sql_from_Hana($sql);
    $html="";
    $htmlRefus="<div class='approval-cat'><h3 class='header-approval'>Refusée : </h3>";
    $cRefus=0;
    $htmlWaiting="<div class='approval-cat'><h3 class='header-approval'>En cours : </h3>";
    $cWaiting=0;
    $htmlTocreate="<div class='approval-cat'><h3 class='header-approval'>A créer : </h3>";
    $cTocreate=0;
    foreach ($list as $item) {
        $htmlTmp="";
        $status="";
        $docType="";
        $badge="";
        // $date = new DateTime($item["CreateDate"]);
        // $formattedDate = $date->format('d-m-Y');
        $formattedDate = $item["DocDate"];

        switch ($item["Status"]) {
            case "W":
                $status="Approbation en attente par : ".$item["USER_CODE"];
                $badge="warning-badge";
                $cWaiting+=1;

                break;
            case "N":
                $status="Refusée";
                $badge="danger-badge";
                $cRefus+=1;

                break;
            case "Y":
                $status="Approuvée";
                $badge="success-badge";
                $cTocreate+=1;
                break;
        }
        switch ($item["ObjType"]) {
            case "1470000113":
                $docType="Demande d'achat";
                break;
            case "22":
                $docType="Bon de commande";
                break;
        }
        

        $htmlTmp.="<div class='card-approval-status ".$badge."' data-docentry='".$item["DraftEntry"]."'>";
        $htmlTmp.="<p class='p-app-da'>".$docType." : ".$item["DraftEntry"]."</p>";
        $htmlTmp.="<p class='p-app-da'>".$status."</p>";
        $htmlTmp.="<p class='p-app-da'>".$formattedDate."</p>";
        $htmlTmp.="</div>";

        switch ($item["Status"]) {
            case "W":
                $htmlWaiting.=$htmlTmp;
                break;
            case "N":
                $htmlRefus.=$htmlTmp;
                break;
            case "Y":
                $htmlTocreate.=$htmlTmp;
                break;
        }
    }
    $htmlRefus.="</div>";
    $htmlWaiting.="</div>";
    $htmlTocreate.="</div>";
    if ($cTocreate==0)$htmlTocreate="";
    if ($cRefus==0)$htmlRefus="";
    if ($cWaiting==0)$htmlWaiting="";
    return $htmlRefus.$htmlWaiting.$htmlTocreate;
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
            $response = array("listApproval" => $listApproval,"c_da" =>$listDa[0] , "listDa" => $listDa[1], "listyears" => $listYears);
        
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
<h2>Société : <span id="societe-span"></span></h2>
    <div class="soc-list-container">
        <?php generateSocieteSelector();?> 
    </div>   
<h2 id="appro-header">Etat d'approbation</h2>
    <div class="approval-list-container" id="approval-list-container">
    Choisir une société !
    </div>  
<h2 class="list-title">Liste des DA <span id="count-list-da"></span></h2>
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

function getapprovalDetails(code){
    base=document.getElementById("societe-span").innerHTML;

    $.ajax({
        type: 'GET',
        url: './src/screens/myDA.php', 
        data: { 
                action:"getapprovalDetails",
                base: base,
                docEntry:code
            },
        success: function(response) {

            let tipContainer = document.getElementById("tool-tip");

            if(response!=""){
                tipContainer.innerHTML=response;
            }          
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}

function getDabyYear(year){
    base=document.getElementById("societe-span").innerHTML;
    $.ajax({
        type: 'GET',
        url: './src/screens/myDA.php', 
        data: { 
                action:"getdabyyear",
                base: base,
                year:year
            },
        success: function(response) {
            let data = JSON.parse(response);
            let listDA = document.getElementById("da-left-container");
            let countListDA = document.getElementById("count-list-da");

            if(data.dayear==""){
                listDA.innerHTML="Aucune donnée";
                countListDA.innerHTML="( 0 )"
            }else{
                listDA.innerHTML=data.dayear;
                countListDA.innerHTML="( "+data.c_da+" )"
            }
            
            let buttons = document.querySelectorAll('.btn-get-detail');
            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    fetchDaDetails(this.getAttribute('data-docnum'),document.getElementById("societe-span").innerHTML);
                });
            });
            
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
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
            let listApproval = document.getElementById("approval-list-container");
            let listDA = document.getElementById("da-left-container");
            let countListDA = document.getElementById("count-list-da");
            let yearsList = document.getElementById("btn-date-container");

            
            if(data.listApproval==""){
                document.getElementById("appro-header").innerHTML=""
                listApproval.innerHTML="";
            }else{
                listApproval.innerHTML=data.listApproval;
                document.getElementById("appro-header").innerHTML="Etat d'approbation"
            }
            if(data.listDa==""){
                listDA.innerHTML="Aucune donnée";
                countListDA.innerHTML="( 0 )"
            }else{
                listDA.innerHTML=data.listDa;
                countListDA.innerHTML="( "+data.c_da+" )"
            }
            yearsList.innerHTML=data.listyears;
            let buttons = document.querySelectorAll('.btn-get-detail');
            let buttonsYear = document.querySelectorAll('.btn-set-year');
            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    fetchDaDetails(this.getAttribute('data-docnum'),document.getElementById("societe-span").innerHTML);
                });
            });

            buttonsYear.forEach(function(button) {
                button.addEventListener('click', function() {
                    getDabyYear(this.getAttribute('data-year'));
                });
            });


            $(document).ready(function() {
            $('.card-approval-status').hover(
                function() {
                    getapprovalDetails(this.getAttribute('data-docentry'));
                    let mouseX = event.pageX;
                    let mouseY = event.pageY;
                    $('#tool-tip').css({
                                'left': mouseX+10 + 'px',
                                'top': mouseY+10 + 'px',
                                'z-index':2,
                                'opacity':1
        });
                    },
                function() { 
                    let tipContainer = document.getElementById("tool-tip");
                    tipContainer.innerHTML=""
                    $('#tool-tip').css({
                                'left': '0',
                                'top': '0',
                                'z-index':0,
                                'opacity':0
                    });
                    },
                ) 
        });
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}

function fetchDaDetails(docNum,base) {
    $.ajax({
        type: 'GET',
        url: './src/screens/myDA.php', 
        data: { 
            action:"getda",
            DocNum: docNum,
            base: base 
        },
        success: function(response) {
            console.log(response)
            var data = JSON.parse(response);
            console.log(data)
            let tableDiv = document.getElementById("da-detail-container");
            tableDiv.innerHTML = '';
            var spanBC = document.createElement('div');
                spanBC.className = "span-bc-right";
            if(data[0]){
                //Bon de ocmmande saisie

                spanBC.innerHTML = 'Total Bon de commande : ';
                tableDiv.appendChild(spanBC); 
                var table = document.createElement('table');
                table.style.width = '100%';
                table.setAttribute('border', '1');

                // Create the header row
                var thead = document.createElement('thead');
                var headerRow = document.createElement('tr');
                [ 'Code Article','Article','Texte Libre', 'Quantité','Fournisseur', 'BC','Date BC', 'Status BC', 'BR', 'Date BR', 'Status BR', 'Total'].forEach(headerText => {
                    var header = document.createElement('th');
                    header.className = "bc-list-table";
                    header.textContent = headerText;
                    headerRow.appendChild(header);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Create the body of the table
                var tbody = document.createElement('tbody');
                data[1].forEach(item => {
                    var row = document.createElement('tr');
                    [  'Code Article','Article','FreeTxt', 'Qte BC','Fournisseur', 'Num_BC','Date_BC', 'Status_BC', 'Num_BR', 'Date BR', 'Status_BR', 'LinTotal BC'].forEach(key => {
                        var cell = document.createElement('td');
                        cell.className = "td-list-table";
                        cell.textContent = item[key];
                        row.appendChild(cell);
                    });
                    tbody.appendChild(row);
                });
                table.appendChild(tbody);

                // Append the table to the div
                tableDiv.appendChild(table);



            }else{
                //Bon de commande NONONONO saisie
                
                spanBC.innerHTML = 'Bon de commande non créé';
                tableDiv.appendChild(spanBC);
                var table = document.createElement('table');
                // table.style.width = '100%';
                // table.setAttribute('border', '1');
                var thead = document.createElement('thead');
                var headerRow = document.createElement('tr');
                ['Code Article', 'Article ', 'Texte libre', 'Quantité'].forEach(headerText => {
                    var header = document.createElement('th');
                    header.className = "da-list-table";
                    header.textContent = headerText;
                    headerRow.appendChild(header);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                var tbody = document.createElement('tbody');
                data[1].forEach(item => {
                    var row = document.createElement('tr');
                    ['ItemCode', 'Dscription','FreeTxt', 'Quantity'].forEach(key => {
                        var cell = document.createElement('td');
                        cell.textContent = item[key];
                        row.appendChild(cell);
                    });
                    tbody.appendChild(row);
                });
                table.appendChild(tbody);
                tableDiv.appendChild(table);
            }

        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}




</script>
