<?php
$base=['AM_PROINVEST_TEST','AM_PROINVEST','YASMINE_FONCIERE','PROBAT_INVEST'];
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

function list_approval($base){
    session_start();
    $user=$_SESSION['user'];
    $sql='SELECT "DocEntry","Status","IsDraft",TO_VARCHAR(TO_DATE("CreateDate"), \'DD-MM-YYYY\') as "DocDate" ,"ObjType","DraftEntry"
     from "'.$base.'"."OWDD" WHERE "OwnerID"=(select "USERID" FROM "'.$base.'"."OUSR" where "IsDraft"=\'Y\' and "USER_CODE" =\''.$user.'\')';
    $list=sql_from_Hana($sql);
    $html="";
    foreach ($list as $item) {
        $status="";
        $docType="";
        $docentry=-1;
        $badge="";
        // $date = new DateTime($item["CreateDate"]);
        // $formattedDate = $date->format('d-m-Y');
        $formattedDate = $item["DocDate"];

        switch ($item["Status"]) {
            case "W":
                $status="Approbation en attente";
                $badge="warning-badge";
                break;
            case "N":
                $status="Refusée";
                $badge="danger-badge";
                break;
            case "Y":
                $status="Approuvée";
                $badge="success-badge";
                $docentry=$item["DocEntry"];
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
        

        // $html.="<div class='card-approval-status' data-docentry='".$docentry."'>".$status."</div>";
        $html.="<div class='card-approval-status ".$badge."' data-docentry='".$docentry."'>";
        $html.="<p class='p-app-da'>".$docType." : ".$item["DraftEntry"]."</p>";
        $html.="<p class='p-app-da'>".$status."</p>";
        $html.="<p class='p-app-da'>".$formattedDate."</p>";
        $html.="</div>";
    }
    return $html;
}

function generateSocieteSelector(){
    global $base;
    foreach ($base as $soc) {
        echo "<button class='btn-set-soc' data-base='".$soc."'>".$soc."</button>";
    }
}

function get_DA_lista($base,$user){
    $html="";
    $sql='SELECT "DocNum",TO_VARCHAR(TO_DATE("DocDate"), \'DD-MM-YYYY\') as "DocDate","CANCELED","DocStatus" FROM "'.$base.'"."OPRQ" WHERE "Requester"=\''.$user.'\' ORDER BY "OPRQ"."DocDate" DESC';
    $list=sql_from_Hana($sql);
    foreach ($list as $item) {
        $badge="warning-da";
        if($item["CANCELED"]=="N" && $item["DocStatus"]=="C"){
            $badge="succes-da";
        }
        $html.= "<button class='btn-get-detail ".$badge."' data-docnum='".$item["DocNum"]."'>".$item["DocNum"]." - ".$item["DocDate"]."</button>";
    }
    return array(count($list),$html);
    return $html;
}
function get_DA_details($numDA,$base){
    $sql='SELECT * FROM "'.$base.'"."ETAT_ACHAT" WHERE "Num_DA"=\''.$numDA.'\'';
    $list=sql_from_Hana($sql);
    if (count($list)==0){
        $sql='SELECT "ItemCode","Dscription","Quantity" from "'.$base.'"."PRQ1" where "DocEntry" = (SELECT "DocEntry" FROM "'.$base.'"."OPRQ" WHERE "DocNum"=\''.$numDA.'\')';
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
        
            $response = array("listApproval" => $listApproval,"c_da" =>$listDa[0] , "listDa" => $listDa[1]);
        
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
        exit();
    }
    
}


?>
<h2>Société : <span id="societe-span"></span></h2>
    <div class="soc-list-container">
        <?php generateSocieteSelector();?> 
    </div>   
<h2>Etat d'approbation</h2>
    <div class="approval-list-container" id="approval-list-container">
    Choisir une société !
    </div>  
<h2>Liste des DA <span id="count-list-da"></span></h2>
<div class="my-da-list-container">

    <div class="da-left-container" id="da-left-container">
        
    </div>
    <div class="da-right-container" id="da-detail-container">

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let buttonsSoc = document.querySelectorAll('.btn-set-soc');
    buttonsSoc.forEach(function(button) {
        button.addEventListener('click', function() {
            getApprovalDA(this.getAttribute('data-base'));
        });
    });
    

    
});

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

            if(data.listApproval==""){
                listApproval.innerHTML="Aucune donnée";
            }else{
                listApproval.innerHTML=data.listApproval;
            }
            if(data.listDa==""){
                listDA.innerHTML="Aucune donnée";
                countListDA.innerHTML="( 0 )"
            }else{
                listDA.innerHTML=data.listDa;
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
                [ 'Code Article','Article', 'Fournisseur', 'BC','Date BC', 'Status_BC', 'BR', 'Date_BR', 'Status_BR', 'Total'].forEach(headerText => {
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
                    [  'Code_Article','Article', 'Fournisseur', 'Num_BC','Date_BC', 'Status_BC', 'Num_BR', 'Date BR', 'Status_BR', 'Total'].forEach(key => {
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
                ['Code Article', 'Article ', 'Quantité'].forEach(headerText => {
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
                    ['ItemCode', 'Dscription', 'Quantity'].forEach(key => {
                        var cell = document.createElement('td');
                        cell.textContent = item[key];
                        row.appendChild(cell);
                    });
                    tbody.appendChild(row);
                });
                table.appendChild(tbody);
                tableDiv.appendChild(table);
            }

            // var tableBody = document.getElementById("table-body");

            // if (tableBody.hasChildNodes()) {
            //     while (tableBody.firstChild) {
            //         tableBody.removeChild(tableBody.firstChild);
            //     }
            // }
            // // Loop to create the rows
            // cartListe.forEach(function (row, index) {
            //     var tr = document.createElement("tr");
            //     tr.setAttribute("item-id", row.id);
            //     // For the first row, create and append the "Demandeur" input
            //     if (index === 0) {
            //         var demandeurTd = document.createElement("td");
            //         var demandeurInput = document.createElement("input");
            //         demandeurInput.type = "text";
            //         demandeurInput.placeholder = "Demandeur";
            //         demandeurInput.className = "input-bs-table";
            //         demandeurInput.id = "demandeur-bs-table"; // Use a consistent ID for the input
            //         demandeurTd.setAttribute("rowspan", cartListe.length);
            //         // demandeurInput.classList.add("center-vertically");
            //         demandeurTd.appendChild(demandeurInput);
            //         tr.appendChild(demandeurTd);
            //     }

            // });
        },
        error: function(xhr, status, error) {
            // Handle AJAX error
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}


</script>