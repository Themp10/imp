<?php
$glpiconn = new mysqli("172.28.0.9", "glpi", "MG+P@ssw0rd", "glpi");

function getFromHana($sql,$base){
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

function get_mp($base){
    $sql='SELECT "PayMethCod" FROM "OPYM" where "Type"=\'O\'';
    $result=getFromHana($sql,$base);
    return $result;
}
function getDelaip($delai,$base){
    $sql='SELECT "GroupNum" FROM "OCTG" WHERE "PymntGroup" = \''.$delai.'\'';
    $result=getFromHana($sql,$base);
    return $result[0];
}
function execsql($sql) {
    global $glpiconn;

    $result = $glpiconn->query($sql);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->free();
    } else {
        $data = [];
    }
    return $data;
}

function loadForms(){
    $sql="SELECT f.id,CASE f.plugin_formcreator_forms_id WHEN 1 THEN 'création' WHEN 5 THEN 'duplication'  END AS 'Mode',f.name as 'Demande',CONCAT(u.firstname, ' ', u.realname) as 'Demandeur' , f.request_date as 'Date' 
        FROM glpi_plugin_formcreator_formanswers AS f, glpi_users AS u 
        WHERE f.requester_id = u.id AND f.plugin_formcreator_forms_id IN (1, 5) AND f.status = 101";
    $result=execsql($sql);
    $html='';
    if(count($result)==0){
        $html.='<div class="no-formulaire">Aucun formulaire à traiter</div>';
    }else{
        foreach ($result as $row) {
            $html.='<div class="formulaire-card" onclick="getDataFromGlpi('.htmlspecialchars($row['id']).',\''.htmlspecialchars($row['Mode']).'\')">
            <p>Id : <span>'.htmlspecialchars($row['id']).'</span> </p>
            <p>Mode: <span id="form-mode">'.htmlspecialchars($row['Mode']).'</span></p>
            <p>Demande : <span>'.htmlspecialchars($row['Demande']).'</span></p>
            <p>Demandeur : <span>'.htmlspecialchars($row['Demandeur']).'</span></p>
            </div>';
        }

    }
    return $html;
}   
function getFormFromGlpi($id){
    $sql='SELECT q.id,q.name,a.answer FROM glpi_plugin_formcreator_answers AS a,glpi_plugin_formcreator_questions AS q
        WHERe a.plugin_formcreator_questions_id = q.id
        AND plugin_formcreator_formanswers_id='.$id;
    $result=execsql($sql);
    return $result;
}

function insertToSAP($data){
    $mm=getDelaip($data["dp"],"AM_PROINVEST_TEST");
    $dataToSend = [
        "UserName" => "o.aboujaafar",
        "Password" => "thethepo",
        "CompanyDB" => "AM_PROINVEST_TEST",
        "CardName" => "Test Partner",
        "CardType" => "cSupplier",
        "Phone1" => "0660000000",
        "EmailAddress" => "test@example.com",
        "Series" => 105,
        "AdditionalID" => "0000000000000000",
        "UnifiedFederalTaxID" => "1111111",
        "PayTermsGrpCode" => 10,
        "PeymentMethodCode" => "Chèque",
        "BPPaymentMethods" => [
            [
                "PaymentMethodCode" => "Chèque"
            ]
        ],
        "FreeText" => "CREATION VIA GLPI",
        "BPAddresses" => [
            [
                "AddressType" => "bo_BillTo",
                "AddressName" => "Addresse",
                "Street" => "Carrera 23 apto 403 Edficio Pinar Navarra",
                "Block" => "Bogotá D.C.",
                "City" => "Bogotá D.C.",
                "Country" => "MA"
            ]
        ],
        "ContactEmployees" => [
            [
                "Name" => "Contact",
                "Phone1" => "0524120325",
                "FirstName" => "prénom",
                "LastName" => "nom"
            ]
        ]
    ];

    $ch = curl_init("http://10.10.5.34:5013/create-partner");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataToSend, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //Timeout settings
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        echo json_encode([
            "status" => "error",
            "message" => "Curl error: $error"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    curl_close($ch);
    if (empty($response)) {
        echo json_encode([
            "status" => "error",
            "message" => "No response from API (possibly offline or timeout)"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo $data;
    exit;
}

function getFrsFromSAP($base,$codeFrs){
    $sql='select "OCRD"."CardCode","OCRD"."CardName","OCRD"."Address","OCRD"."City","OCRD"."Phone1","OCRD"."E_Mail","OCRD"."AddID",
        "OCRD"."VatIdUnCmp", "OCRD"."PymCode","OCRD"."BillToDef","OCTG"."PymntGroup","OCPR"."FirstName","OCPR"."LastName"
        from "OCRD" "OCRD"
        LEFT OUTER JOIN "OCPR" "OCPR"  ON "OCRD"."CardCode"="OCPR"."CardCode"
        LEFT OUTER JOIN "OCTG" "OCTG"  ON "OCRD"."GroupNum"="OCTG"."GroupNum" where "OCRD"."CardCode" = \''. $codeFrs.'\'';
    $result=getFromHana($sql,$base);
    return $result[0];
}
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        $result = [];
        if ($_GET["action"]=="refreshFormulaire") {
            $data=loadForms();
            $mp="";
        }
        if ($_GET["action"]=="création") {
            $id=$_GET["id"];
            $data=getFormFromGlpi($id);
            $base="";
            foreach ($data as $answer) {
                if($answer["id"]==8){
                    $base=$answer["answer"];
                }
            }
            $mp=get_mp($base);
        }
        if ($_GET["action"]=="duplication") {
            $id=$_GET["id"];
            $dataGlpi=getFormFromGlpi($id);
            $baseSource="";
            $baseCible="";
            $codeFrs="";
            foreach ($dataGlpi as $answer) {
                if($answer["id"]==43){
                    $baseSource=$answer["answer"];
                }
                if($answer["id"]==42){
                    $baseCible=$answer["answer"];
                }
                if($answer["id"]==41){
                    $codeFrs=$answer["answer"];
                }
            }
            $data=getFrsFromSAP($baseSource,$codeFrs);
            $data["BaseCible"]=$baseCible;
            $mp=get_mp($baseCible);
            
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "data" => $data,
            "mp" => $mp,
        ]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
    $action = $data['action'] ?? '';
    $formData = $data['data'] ?? [];
    if ($action === 'insertSAP') {
        insertToSAP($formData);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Unknown action"
        ], JSON_UNESCAPED_UNICODE);
    }
    exit();
}

?>

<div id="loading-modal">
    <div class="modal-internior">
        <img src="./assets/spinner.gif" alt="spinner" class="spinner">
        <h1>Création SAP en cours</h1>
    </div>
</div>
<h1>Fournisseurs</h1>
<h2>Liste Formulaires</h2>
<div class="formulaire-btns">
<button class ="sync-btn" onclick="refreshFormulaire()"><img src="./assets/refresh.png" alt="" width="24"></button>
<fieldset class="form-item-box">
    <legend class="filter-type-title">Modalité de paiement</legend>
    <select class ="item-select" name="formulaire_mp" id="formulaire_mp">
        <option value="">-- Sélectionnez --</option>
    </select>
</fieldset>
<button class="btn-switch" type="button" onclick="FormulaireData()">Créer</button>
<fieldset class="form-item-box large-box">
    <legend class="filter-type-title">Log</legend>
    <input type="text" name="log_message" id="log_message">
</fieldset>
</div>

<div class="formulaire-container" id="formulaire-container">
<?php echo loadForms();?>
</div>
<h2>Détail Formulaire</h2>
<div class="formulaire-details">
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Base</legend>
        <input type="text" name="form_base" id="form_8">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Raison Sociale</legend>
        <input type="text" name="form_rs" id="form_3">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Nom Prénom</legend>
        <input type="text" name="form_fn" id="form_10">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Email</legend>
        <input type="text" name="form_email" id="form_11">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Tel</legend>
        <input type="text" name="form_tel" id="form_5">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Addresse</legend>
        <input type="text" name="form_addresse" id="form_4">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Ville</legend>
        <input type="text" name="form_ville" id="form_40">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Forme Juridique</legend>
        <input type="text" name="form_fj" id="form_9">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">N° RC</legend>
        <input type="text" name="form_rc" id="form_12">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">N° CNSS</legend>
        <input type="text" name="form_cnss" id="form_14">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">N° ICE</legend>
        <input type="text" name="form_email" id="form_6">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">N° IF</legend>
        <input type="text" name="form_if" id="form_7">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Délai de paiement</legend>
        <input type="text" name="form_dp" id="form_15">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Modalité de paiement</legend>
        <input type="text" name="form_mp" id="form_16">
    </fieldset>
    <fieldset class="form-item-box">
        <legend class="filter-type-title">Remarque</legend>
        <input type="text" name="form_note" id="form_note">
    </fieldset>
</div>
<script>
    function refreshFormulaire(){
        $.ajax({
            type: 'GET',
            url: './src/screens/sapFrs.php',
            dataType: 'json',
            headers: {
                    'Content-Type': 'application/json; charset=UTF-8'
            },
            data: { 
                action:"refreshFormulaire",
            },
            success: function (response) {
                document.getElementById("formulaire-container").innerHTML=response.data;  

            },
            error: function (xhr, status, error) {

                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }
    function getDataFromGlpi(id,mode){
        $.ajax({
            type: 'GET',
            url: './src/screens/sapFrs.php',
            dataType: 'json',
            headers: {
                    'Content-Type': 'application/json; charset=UTF-8'
            },
            data: { 
                action:mode,
                id:id,
            },
            success: function (response) {
                if(mode=="création"){
                    response.data.forEach(item => {
                        if(item.id==17 || item.id==13) return
                        const element = document.getElementById("form_" + item.id);
                        if (element) {
                            element.value = item.answer;
                        } else {
                            console.warn(`Element with id form_${item.id} not found.`);
                        }
                    });
                }else{
                    let frsData=response.data
                    document.getElementById("form_8").value=frsData["BaseCible"]
                    document.getElementById("form_3").value=frsData["CardName"]
                    document.getElementById("form_10").value=frsData["LastName"]+" "+frsData["FirstName"]
                    document.getElementById("form_11").value=frsData["E_Mail"]
                    document.getElementById("form_5").value=frsData["Phone1"]
                    document.getElementById("form_4").value=frsData["Address"]
                    document.getElementById("form_40").value=frsData["City"]
                    document.getElementById("form_9").value=""
                    document.getElementById("form_12").value=""
                    document.getElementById("form_14").value=""
                    document.getElementById("form_6").value=frsData["AddID"]
                    document.getElementById("form_7").value=frsData["VatIdUnCmp"]
                    document.getElementById("form_15").value=frsData["PymntGroup"]
                    document.getElementById("form_16").value=frsData["PymCode"]

                }
                const select = document.getElementById('formulaire_mp');
                select.innerHTML = '<option value="">-- Sélectionnez --</option>';
                response.mp.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.PayMethCod;     // value for the <option>
                    option.textContent = item.PayMethCod; // text shown to user
                    select.appendChild(option);
                });
            },
            error: function (xhr, status, error) {

                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }
    function FormulaireData(){
        if(document.getElementById("form_8").value==""){
            alert("Aucun formulaire séléctionné !")
            return
        }
        if(document.getElementById('formulaire_mp').selectedIndex==0){
            alert("Merci de choisir une modalité de paiement !")
            return
        }
        let data={
            "base":document.getElementById("form_8").value,
            "RS":document.getElementById("form_3").value,
            "fn":document.getElementById("form_10").value,
            "email":document.getElementById("form_11").value,
            "tel":document.getElementById("form_5").value,
            "addresse":document.getElementById("form_4").value,
            "ville":document.getElementById("form_40").value,
            "fj":document.getElementById("form_9").value,
            "rc":document.getElementById("form_12").value,
            "cnss":document.getElementById("form_14").value,
            "ice":document.getElementById("form_6").value,
            "if":document.getElementById("form_7").value,
            "dp":document.getElementById("form_15").value,
            "mp":document.getElementById('formulaire_mp').selectedOptions[0].value,
        }
        document.getElementById("loading-modal").style.display="flex" 
        $.ajax({
            type: 'POST',
            url: './src/screens/sapFrs.php',
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json',
            data: JSON.stringify({ 
                action: "insertSAP",
                data: data
            }),
            success: function (response) {
                console.log(response);
                let msg="problème de communication avec l'api SAP!"
                if(response.success){
                    msg=response.message+" Code : "+response.response.CardCode
                }else{
                    msg=response.message
                }
                document.getElementById("log_message").value=msg

                document.getElementById("loading-modal").style.display="none"
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }
</script>