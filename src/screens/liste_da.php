<?php
include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."hana_connection.php";
function get_data_from_Hana($sql){
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
function get_approval_status($da){
    $sql='SELECT "Status" FROM "AM_PROINVEST_TEST"."OWDD" where "DraftEntry"='.$da;
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

function get_toner_list($da){
    global $conn; 
    $sql = "SELECT c.name,d.qte,d.demandeur,c.color FROM da_sap d, cartridges c WHERE d.toner=c.id and d.id_da=".$da;
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $toner_list = [];

    while ($row = $result->fetch_assoc()) {
        $toner_list[] = $row;
    }

    return $toner_list;
}
function get_da_list(){
    global $conn; 
    $sql = "SELECT * FROM v_da";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $da_list = [];

    while ($row = $result->fetch_assoc()) {
        $da_list[] = $row;
    }

    return $da_list;
}
function generate_da_html($da) {
    //$items=["0 : da_data","1 : da_color","2 : bc_data","3 : bc_color","","4 : br_data","5 : br_color","6 : da en cours ?"]
    $items=["A saisir sur SAP","idle-badge","A saisir sur SAP","idle-badge","A saisir sur SAP","idle-badge",""];
    $approbation= get_approval_status($da['doc_key']);

   if($da['n_br_sap']==""){
        $items[6]="da-en-cours";
   }
   if($da['da_ok']=='1'){
    $items[0]=$approbation;
    $items[2]="DA non saisie";
    $items[4]="BC non saisi";
    if($approbation=="Approbation en attente"){
        $items[1]="neutral-badge";
    }elseif ($approbation=="Refusée") {
        $items[1]="danger-badge";
    }else{
        $items[1]="success-badge";
        if($da['n_da_sap']==""){
            $items[0]=$da['n_da_sap']." : ".$da['date_da'];
           }
    }
    

   }

    
    $html = '<div class="da-container '.$items[6].'">';
    $html .='<div class="da-container-top">';
    $html .= '<div class="da-item-container">';
    $html .= '<p class="da-item-title">Id</p>';
    $html .= '<p class="da-item-data">' . $da['id_da'] . '</p>';
    $html .= ' </div>';
    $html .= '<div class="da-item-container">';
    $html .= '   <p class="da-item-title">Demande d achat</p>';
    $html .= '   <p class="da-item-data">' . $da['date'] . '</p>';
    $html .= ' </div>';
    //affichage DA
    $html .= '<div class="da-item-container">';
    $html .= '   <p class="da-item-title">Demande d achat</p>';
 
    $html .= '   <p class="da-item-data '.$items[1].'">'.$items[0].'</p>';
    $html .= ' </div>';
    //affichage BC
    $html .= '  <div class="da-item-container">';
    $html .= '     <p class="da-item-title">Bon de Commande</p>';
    $html .= '     <p class="da-item-data '.$items[3].'">'.$items[2].'</p>';
    $html .= ' </div>';
    //affichage BR
    $html .= ' <div class="da-item-container">';
    $html .= '   <p class="da-item-title">Réception</p>';
    $html .= '    <p class="da-item-data '.$items[5].'">'.$items[4].'</p>';
    $html .= ' </div>';
    $html .= '</div>';
    $html .='<div class="da-container-bottom">';
        $toner_da=get_toner_list($da['id_da']);
        foreach ($toner_da as $toner) {
            $html .=  generate_toners_html($toner);
        }
    $html .='</div>';
    $html .='</div>';
    


    return $html;
}

function generate_toners_html($toner) {


    $html = '<div class="da-cartridge-item">';
    $html .= '<div class="da-cartridge-container">';
    $html .= '<div class="rnd-class"><p class="da-item-name">' . $toner['name'] . '</p><span class="da-badge-color ' . $toner['color'] . '"></span></div>';
    $html .= '<span class="da-item-name">' . $toner['demandeur'] . '</span>';
    $html .= '<p class="da-item-name">En stock : <span>' . $toner['qte'] . '</span></p>';
    $html .= '</div>';
    $html .= '</div>';


    return $html;
}

function generate_all(){
    $list_da=get_da_list();
    foreach ($list_da as $da) {
        echo generate_da_html($da);
    }
}
?>

<div class="sortie-stock-header">
    <h2>Liste Demande d'Achat Toner </h2>
</div>

<div class="da-list-container">
    <?php echo generate_all(); ?>
    <!-- <div class="da-overlay"></div> -->
</div>