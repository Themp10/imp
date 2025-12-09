<?php
require __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
$ADVconn = new mysqli("localhost", "sa", "MG+P@ssw0rd", "BC");
if ($ADVconn->connect_error) {
    die("Connection failed: " . $ADVconn->connect_error);
}
function sql($sql) {
    global $ADVconn;

    $result = $ADVconn->query($sql);
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
function formatNumber($number) {
    if (!isset($number) || $number === '') {
        return 0;
    }
    return number_format(round($number), 0, '', ' ');
}
function excelHeader(){
    return '        
        <table id ="adv-table-excel" class="table-synthese" border="1" cellpadding="5" cellspacing="0">
            <thead style="background-color:#547471; color:#FFFFFF;">
                <tr style="background-color:#547471; color:#FFFFFF;">
                    <th colspan="18">UPTOWN  - SITUATION RECOUVEREMENT A DATE</th>
                </tr>
                <tr style="background-color:#547471; color:#FFFFFF;">
                    <th rowspan="2">Imm</th>
                    <th colspan="4" class="box-border">STOCK</th>
                    <th colspan="4" class="box-border">C.A</th>
                    <th colspan="3" class="box-border">REGLEMENT CLIENT</th>
                    <th colspan="2" class="box-border">MAIN LEVEE</th>
                    <th colspan="4" class="box-border">RELIQUAT</th>
                </tr>
                <tr style="background-color:#547471; color:#FFFFFF;">
                    <th class="box-border-left" width="3%">STOCK</th>
                    <th width="3%">BLOQUE</th>
                    <th width="3%">RESERVEES</th>
                    <th class="box-border-right" width="3%">SOLDEES</th>
                    <th width="8%">STOCK</th>
                    <th width="8%">BLOQUE</th>
                    <th width="8%">RESERVE</th>
                    <th class="box-border-right" width="9%">SOLDE</th>
                    <th width="6%">TOTAL AVANCES ENCAISSEES</th>
                    <th width="6%">REGLEMENT ENTRE LES MAINS DES NOTAIRES POUR BIENS RESERVES</th>
                    <th class="box-border-right" width="6%">REGLEMENT ENTRE LES MAINS DES NOTAIRES POUR BIENS SOLDES</th>
                    <th width="8%">MLV PAYEES</th>
                    <th class="box-border-right" width="8%">MLV A PAYER</th>
                    <th width="6%">ENCAISSE ANFA REA</th>
                    <th width="6%">A VERSER AUX NOTAIRES</th>
                    <th width="6%">VERSE AUX NOTAIRES</th>
                    <th class="box-border-right" width="5%">A RECEUPERER</th>
                </tr>
            </thead>';
}
function uptownHeader(){
    return '        
        <table id ="adv-table-recouvrement" class="table-synthese" border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th colspan="18">UPTOWN  - SITUATION RECOUVEREMENT A DATE</th>
                </tr>
                <tr>
                    <th rowspan="2">Imm</th>
                    <th colspan="4" class="box-border">STOCK</th>
                    <th colspan="4" class="box-border">C.A</th>
                    <th colspan="3" class="box-border">REGLEMENT CLIENT</th>
                    <th colspan="2" class="box-border">MAIN LEVEE</th>
                    <th colspan="4" class="box-border">RELIQUAT</th>
                </tr>
                <tr class="adv-header-titles">
                    <th class="box-border-left" width="3%">STOCK</th>
                    <th width="3%">BLOQUE</th>
                    <th width="3%">RESERVEES</th>
                    <th class="box-border-right" width="3%">SOLDEES</th>
                    <th width="8%">STOCK</th>
                    <th width="8%">BLOQUE</th>
                    <th width="8%">RESERVE</th>
                    <th class="box-border-right" width="9%">SOLDE</th>
                    <th width="6%">TOTAL AVANCES ENCAISSEES</th>
                    <th width="6%">REGLEMENT ENTRE LES MAINS DES NOTAIRES POUR BIENS RESERVES</th>
                    <th class="box-border-right" width="6%">REGLEMENT ENTRE LES MAINS DES NOTAIRES POUR BIENS SOLDES</th>
                    <th width="8%">MLV PAYEES</th>
                    <th class="box-border-right" width="8%">MLV A PAYER</th>
                    <th width="6%">ENCAISSE ANFA REA</th>
                    <th width="6%">A VERSER AUX NOTAIRES</th>
                    <th width="6%">VERSE AUX NOTAIRES</th>
                    <th class="box-border-right" width="5%">A RECEUPERER</th>
                </tr>
            </thead>';
}

function uptownNotaireHeader(){
        return '        
        <table id ="adv-table-2" class="table-synthese" border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th colspan="13">UPTOWN  - SITUATION PAR NOTAIRE</th>
                </tr>
                <tr class="adv-header-titles">
                    <th width="10%">NOTAIRES</th>
                    <th width="6%">U SOLDEES</th>
                    <th width="7%">CA SOLDES</th>
                    <th width="7%">CA REMBOURSEMENT 70%</th>
                    <th width="6%">U MLV 70% PAYEES</th>
                    <th width="7%">CA MLV 70% PAYEES</th>
                    <th width="6%">U MLV 70% A PAYER</th>
                    <th width="7%">CA MLV 70% A PAYER</th>
                    <th width="8%">AVANCES ENCAISSEES</th>
                    <th width="8%">RELIQUAT >70% ENCAISSE</th>
                    <th width="8%">RELIQUAT >70% A ENCAISSER</th>
                    <th width="10%">RELIQUAT COMPLEMENTS 70% VERSES AUX NOTAIRES</th>
                    <th width="10%">RELIQUAT COMPLEMENTS 70% A VERSER AUX NOTAIRES</th>
                </tr>
            </thead>';
}
function calculateUptownNotaire(){
    $liste_notaire=sql("SELECT DISTINCT notaire FROM situation_uptown ORDER BY notaire");
    $table_ca=sql('SELECT notaire, COUNT(*) AS "U",ROUND(SUM(prix_vente)) AS "CA",ROUND(SUM(0.7*prix_vente))  AS "CA 70%",
                ROUND(SUM(mlv_payees)) AS "CA mlv payees",ROUND(SUM(mlv_a_payer)) AS "CA mlv a payer",ROUND(SUM(avances)) AS "avances"
                ,ROUND(SUM(encaisse_anfa_rea)) AS "encaissement anfa rea",ROUND(SUM(verse)) AS "verses"
                FROM situation_uptown GROUP BY notaire ORDER BY notaire');
    $table_mlv_payees=sql('SELECT notaire,COUNT(*) AS "U mlv payees"  FROM situation_uptown where mlv_payees>0 GROUP BY notaire ORDER BY notaire');
    $table_mlv_a_payer=sql('SELECT notaire,COUNT(*) AS "U mlv a payer"  FROM situation_uptown where mlv_a_payer>0 GROUP BY notaire ORDER BY notaire');
    $table_a_encaisser=sql('SELECT notaire,ROUND(SUM(reste_du)) AS "reliquat a encaisser"  FROM situation_uptown where reste_du>0 GROUP BY notaire ORDER BY notaire');
    $table_a_verser=sql('SELECT notaire,ROUND(SUM(reste_du)) AS "a verser"  FROM situation_uptown where reste_du<0 GROUP BY notaire ORDER BY notaire');  
    $totalR = [];
    $html_notaire_row='<tbody class="adv-table-body" id="adv-table-body">';
    foreach ($liste_notaire as $not) {
        $notaire=$not["notaire"];
        $filtered_table_ca = array_values(array_filter($table_ca, function($item) use ($notaire) {return $item['notaire'] == $notaire;}));
        $filtered_table_mlv_payees = array_values(array_filter($table_mlv_payees, function($item) use ($notaire) {return $item['notaire'] == $notaire;}));
        $filtered_table_mlv_a_payer = array_values(array_filter($table_mlv_a_payer, function($item) use ($notaire) {return $item['notaire'] == $notaire;}));
        $filtered_table_a_encaisser = array_values(array_filter($table_a_encaisser, function($item) use ($notaire) {return $item['notaire'] == $notaire;}));
        $filtered_table_a_verser = array_values(array_filter($table_a_verser, function($item) use ($notaire) {return $item['notaire'] == $notaire;}));

        $html_notaire_row.='
                <tr>
                    <td>'.$notaire.'</td>
                    <td>'.($filtered_table_ca[0]["U"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["CA"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["CA 70%"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv_payees[0]["U mlv payees"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["CA mlv payees"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv_a_payer[0]["U mlv a payer"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["CA mlv a payer"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["avances"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["encaissement anfa rea"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_a_encaisser[0]["reliquat a encaisser"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_ca[0]["verses"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_a_verser[0]["a verser"] ?? 0).'</td>
                </tr>';
        $totalR['U_stock']   = ($totalR['U_stock']   ?? 0) + ($filtered_table_ca[0]["U"]  ?? 0);
        $totalR['CA_stock']   = ($totalR['CA_stock']   ?? 0) + ($filtered_table_ca[0]["CA"]  ?? 0);

        $totalR['CA 70%']   = ($totalR['CA 70%']   ?? 0) + ($filtered_table_ca[0]["CA 70%"]  ?? 0);

        $totalR['U mlv payees']   = ($totalR['U mlv payees']   ?? 0) + ($filtered_table_mlv_payees[0]["U mlv payees"]  ?? 0);
        $totalR['CA mlv payees']   = ($totalR['CA mlv payees']   ?? 0) + ($filtered_table_ca[0]["CA mlv payees"]  ?? 0);

        $totalR['U mlv a payer']   = ($totalR['U mlv a payer']   ?? 0) + ($filtered_table_mlv_a_payer[0]["U mlv a payer"] ?? 0);
        $totalR['CA mlv a payer']   = ($totalR['CA mlv a payer']   ?? 0) + ($filtered_table_ca[0]["CA mlv a payer"] ?? 0);

        $totalR['avances'] = ($totalR['avances'] ?? 0) + ($filtered_table_ca[0]["avances"] ?? 0);

        $totalR['encaissement anfa rea'] = ($totalR['encaissement anfa rea'] ?? 0) + ($filtered_table_ca[0]["encaissement anfa rea"] ?? 0);
        $totalR['reliquat a encaisser'] = ($totalR['reliquat a encaisser'] ?? 0) + ($filtered_table_a_encaisser[0]["reliquat a encaisser"] ?? 0);

        $totalR['verses'] = ($totalR['verses'] ?? 0) + ($filtered_table_ca[0]["verses"] ?? 0);
        $totalR['a verser'] = ($totalR['a verser'] ?? 0) + ($filtered_table_a_verser[0]["a verser"] ?? 0);
    }
    $html_notaire_row .= '
        <tr class="box-border">
            <td>TOTAL GENERAL</td>
            <td>'.formatNumber($totalR['U_stock']).'</td>
            <td>'.formatNumber($totalR['CA_stock']).'</td>
            <td>'.formatNumber($totalR['CA 70%']).'</td>
            <td>'.formatNumber($totalR['U mlv payees']).'</td>
            <td>'.formatNumber($totalR['CA mlv payees']).'</td>
            <td>'.formatNumber($totalR['U mlv a payer']).'</td>
            <td>'.formatNumber($totalR['CA mlv a payer']).'</td>
            <td>'.formatNumber($totalR['avances']).'</td>
            <td>'.formatNumber($totalR['encaissement anfa rea']).'</td>
            <td>'.formatNumber($totalR['reliquat a encaisser']).'</td>
            <td>'.formatNumber($totalR['verses']).'</td>
            <td>'.formatNumber($totalR['a verser']).'</td>
        </tr>';
    return $html_notaire_row.'</tbody></table>';
}
function calculateUptown(){
    global $ADVconn;
    $liste_imm=sql("SELECT DISTINCT immeuble FROM base_uptown ORDER BY immeuble");
    //$result = sql("SELECT * FROM base_uptown");

    $table_stock=sql('SELECT immeuble,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM base_uptown GROUP BY immeuble ORDER BY immeuble');
    $table_Stock_CA_Imm=sql('SELECT immeuble,statut,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM base_uptown GROUP BY immeuble,statut ORDER BY immeuble');
    $table_avances_encaissees=sql('SELECT immeuble,SUM(avances) AS "avances_encaissees" FROM base_uptown GROUP BY immeuble ORDER BY immeuble');
    $table_chez_notaire_reserve=sql('SELECT immeuble,SUM(chez_notaires) AS "chez_notaires" FROM base_uptown WHERE statut="RESERVE" GROUP BY immeuble ORDER BY immeuble');

    $table_mlv=sql('SELECT immeuble,SUM(chez_notaires) AS "chez_notaires",SUM(mlv_payees) AS "mlv_payees",SUM(mlv_a_payer) AS "mlv_a_payer", SUM(encaisse_anfa_rea) AS "encaisse_anfa_rea",SUM(verse)  AS "verse" FROM situation_uptown GROUP BY immeuble ORDER BY immeuble');
    $table_a_verser=sql('SELECT immeuble,SUM(reste_du) AS "a_verser" FROM situation_uptown WHERE reste_du<0 GROUP BY immeuble ORDER BY immeuble');
    $table_a_recuperer=sql('SELECT immeuble,SUM(reste_du) AS "a_recuperer" FROM situation_uptown WHERE reste_du>0 GROUP BY immeuble ORDER BY immeuble');



    $html_imm_row='<tbody class="adv-table-body" id="adv-table-body">';
    $html_imm_D='';
    $totalR = [];
    $total = [];
    foreach ($liste_imm as $immCell) {
        $imm=$immCell["immeuble"];
        
        $filtered_table_stock = array_values(array_filter($table_stock, function($item) use ($imm) {return $item['immeuble'] == $imm;}));
        $filtered_table_Stock_CA_Imm = array_filter($table_Stock_CA_Imm, function($item) use ($imm) {return $item['immeuble'] == $imm;});
        $filtered_table_Stock_CA_Imm_status = array_reduce($filtered_table_Stock_CA_Imm, function($carry, $item) {
            $carry[$item['statut']] = [
                'U'  => $item['U'],
                'CA' => $item['CA']
            ];
            return $carry;
        }, []);
        $filtered_table_avances_encaissees = array_values(array_filter($table_avances_encaissees, function($item) use ($imm) {return $item['immeuble'] == $imm;}));
        $filtered_table_chez_notaire_reserve = array_values(array_filter($table_chez_notaire_reserve, function($item) use ($imm) {return $item['immeuble'] == $imm;}));

        $filtered_table_mlv = array_values(array_filter($table_mlv, function($item) use ($imm) {return $item['immeuble'] == $imm;}));
        $filtered_table_a_verser = array_values(array_filter($table_a_verser, function($item) use ($imm) {return $item['immeuble'] == $imm;}));
        $filtered_table_a_recuperer = array_values(array_filter($table_a_recuperer, function($item) use ($imm) {return $item['immeuble'] == $imm;}));

        if($imm!="D"){    
            $html_imm_row.='
                <tr>
                    <td>'.$imm.'</td>
                    <td class="box-border-left">'.($filtered_table_stock[0]["U"] ?? 0).'</td>
                    <td>'.$filtered_table_Stock_CA_Imm_status["BLOQUE"]["U"].'</td>
                    <td>'.$filtered_table_Stock_CA_Imm_status["RESERVE"]["U"].'</td>
                    <td class="box-border-right">'.$filtered_table_Stock_CA_Imm_status["SOLDE"]["U"].'</td>
                    <td>'.formatNumber($filtered_table_stock[0]["CA"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_Stock_CA_Imm_status["BLOQUE"]["CA"]).'</td>
                    <td>'.formatNumber($filtered_table_Stock_CA_Imm_status["RESERVE"]["CA"]).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_Stock_CA_Imm_status["SOLDE"]["CA"]).'</td>
                    <td>'.formatNumber($filtered_table_avances_encaissees[0]["avances_encaissees"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_chez_notaire_reserve[0]["chez_notaires"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_mlv[0]["chez_notaires"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["mlv_payees"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_mlv[0]["mlv_a_payer"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["encaisse_anfa_rea"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_a_verser[0]["a_verser"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["verse"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_a_recuperer[0]["a_recuperer"] ?? 0).'</td>
                </tr>';
            $totalR['U_stock']   = ($totalR['U_stock']   ?? 0) + ($filtered_table_stock[0]["U"] ?? 0);
            $totalR['U_bloque']  = ($totalR['U_bloque']  ?? 0) + $filtered_table_Stock_CA_Imm_status["BLOQUE"]["U"];
            $totalR['U_reserve'] = ($totalR['U_reserve'] ?? 0) + $filtered_table_Stock_CA_Imm_status["RESERVE"]["U"];
            $totalR['U_solde']   = ($totalR['U_solde']   ?? 0) + $filtered_table_Stock_CA_Imm_status["SOLDE"]["U"];

            $totalR['CA_stock']   = ($totalR['CA_stock']   ?? 0) + ($filtered_table_stock[0]["CA"] ?? 0);
            $totalR['CA_bloque']  = ($totalR['CA_bloque']  ?? 0) + $filtered_table_Stock_CA_Imm_status["BLOQUE"]["CA"];
            $totalR['CA_reserve'] = ($totalR['CA_reserve'] ?? 0) + $filtered_table_Stock_CA_Imm_status["RESERVE"]["CA"];
            $totalR['CA_solde']   = ($totalR['CA_solde']   ?? 0) + $filtered_table_Stock_CA_Imm_status["SOLDE"]["CA"];

            $totalR['avances'] = ($totalR['avances'] ?? 0) + ($filtered_table_avances_encaissees[0]["avances_encaissees"] ?? 0);
            $totalR['notaires'] = ($totalR['notaires'] ?? 0) + ($filtered_table_chez_notaire_reserve[0]["chez_notaires"] ?? 0);
            $totalR['chez_notaires'] = ($totalR['chez_notaires'] ?? 0) + ($filtered_table_mlv[0]["chez_notaires"] ?? 0);

            $totalR['mlv_payees'] = ($totalR['mlv_payees'] ?? 0) + ($filtered_table_mlv[0]["mlv_payees"] ?? 0);
            $totalR['mlv_a_payer'] = ($totalR['mlv_a_payer'] ?? 0) + ($filtered_table_mlv[0]["mlv_a_payer"] ?? 0);

            $totalR['encaisse_anfa_rea'] = ($totalR['encaisse_anfa_rea'] ?? 0) + ($filtered_table_mlv[0]["encaisse_anfa_rea"] ?? 0);
            $totalR['a_verser'] = ($totalR['a_verser'] ?? 0) + ($filtered_table_a_verser[0]["a_verser"] ?? 0);
            $totalR['verse'] = ($totalR['verse'] ?? 0) + ($filtered_table_mlv[0]["verse"] ?? 0);
            $totalR['a_recuperer'] = ($totalR['a_recuperer'] ?? 0) + ($filtered_table_a_recuperer[0]["a_recuperer"] ?? 0);
        }else{
            $html_imm_D.='
                <tr>
                    <td>'.$imm.'</td>
                    <td class="box-border-left">'.($filtered_table_stock[0]["U"] ?? 0).'</td>
                    <td>'.$filtered_table_Stock_CA_Imm_status["BLOQUE"]["U"].'</td>
                    <td>'.$filtered_table_Stock_CA_Imm_status["RESERVE"]["U"].'</td>
                    <td class="box-border-right">'.$filtered_table_Stock_CA_Imm_status["SOLDE"]["U"].'</td>
                    <td>'.formatNumber($filtered_table_stock[0]["CA"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_Stock_CA_Imm_status["BLOQUE"]["CA"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_Stock_CA_Imm_status["RESERVE"]["CA"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_Stock_CA_Imm_status["SOLDE"]["CA"]).'</td>
                    <td>'.formatNumber($filtered_table_avances_encaissees[0]["avances_encaissees"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_chez_notaire_reserve[0]["chez_notaires"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_mlv[0]["chez_notaires"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["mlv_payees"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_mlv[0]["mlv_a_payer"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["encaisse_anfa_rea"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_a_verser[0]["a_verser"] ?? 0).'</td>
                    <td>'.formatNumber($filtered_table_mlv[0]["verse"] ?? 0).'</td>
                    <td class="box-border-right">'.formatNumber($filtered_table_a_recuperer[0]["a_recuperer"] ?? 0).'</td>
                </tr>';
            $total['U_stock']   = ($totalR['U_stock']   ?? 0) + ($filtered_table_stock[0]["U"] ?? 0);
            $total['U_bloque']  = ($totalR['U_bloque']  ?? 0) + $filtered_table_Stock_CA_Imm_status["BLOQUE"]["U"];
            $total['U_reserve'] = ($totalR['U_reserve'] ?? 0) + $filtered_table_Stock_CA_Imm_status["RESERVE"]["U"];
            $total['U_solde']   = ($totalR['U_solde']   ?? 0) + $filtered_table_Stock_CA_Imm_status["SOLDE"]["U"];

            $total['CA_stock']   = ($totalR['CA_stock']   ?? 0) + ($filtered_table_stock[0]["CA"] ?? 0);
            $total['CA_bloque']  = ($totalR['CA_bloque']  ?? 0) + $filtered_table_Stock_CA_Imm_status["BLOQUE"]["CA"];
            $total['CA_reserve'] = ($totalR['CA_reserve'] ?? 0) + $filtered_table_Stock_CA_Imm_status["RESERVE"]["CA"];
            $total['CA_solde']   = ($totalR['CA_solde']   ?? 0) + $filtered_table_Stock_CA_Imm_status["SOLDE"]["CA"];

            $total['avances'] = ($totalR['avances'] ?? 0) + ($filtered_table_avances_encaissees[0]["avances_encaissees"] ?? 0);
            $total['notaires'] = ($totalR['notaires'] ?? 0) + ($filtered_table_chez_notaire_reserve[0]["chez_notaires"] ?? 0);
            $total['chez_notaires'] = ($totalR['chez_notaires'] ?? 0) + ($filtered_table_mlv[0]["chez_notaires"] ?? 0);

            $total['mlv_payees'] = ($totalR['mlv_payees'] ?? 0) + ($filtered_table_mlv[0]["mlv_payees"] ?? 0);
            $total['mlv_a_payer'] = ($totalR['mlv_a_payer'] ?? 0) + ($filtered_table_mlv[0]["mlv_a_payer"] ?? 0);

            $total['encaisse_anfa_rea'] = ($totalR['encaisse_anfa_rea'] ?? 0) + ($filtered_table_mlv[0]["encaisse_anfa_rea"] ?? 0);
            $total['a_verser'] = ($totalR['a_verser'] ?? 0) + ($filtered_table_a_verser[0]["a_verser"] ?? 0);
            $total['verse'] = ($totalR['verse'] ?? 0) + ($filtered_table_mlv[0]["verse"] ?? 0);
            $total['a_recuperer'] = ($totalR['a_recuperer'] ?? 0) + ($filtered_table_a_recuperer[0]["a_recuperer"] ?? 0);

        }
        }
    $html_imm_row .= '
    <tr class="box-border">
        <td>TOTAL RESIDENTIEL</td>
        <td class="box-border-left">'.$totalR['U_stock'].'</td>
        <td>'.$totalR['U_bloque'].'</td>
        <td>'.$totalR['U_reserve'].'</td>
        <td class="box-border-right">'.$totalR['U_solde'].'</td>
        <td>'.formatNumber($totalR['CA_stock']).'</td>
        <td>'.formatNumber($totalR['CA_bloque']).'</td>
        <td>'.formatNumber($totalR['CA_reserve']).'</td>
        <td class="box-border-right">'.formatNumber($totalR['CA_solde']).'</td>
        <td>'.formatNumber($totalR['avances']).'</td>
        <td>'.formatNumber($totalR['notaires']).'</td>
        <td class="box-border-right">'.formatNumber($totalR['chez_notaires']).'</td>
        <td>'.formatNumber($totalR['mlv_payees']).'</td>
        <td class="box-border-right">'.formatNumber($totalR['mlv_a_payer']).'</td>
        <td>'.formatNumber($totalR['encaisse_anfa_rea']).'</td>
        <td>'.formatNumber($totalR['a_verser']).'</td>
        <td>'.formatNumber($totalR['verse']).'</td>
        <td class="box-border-right">'.formatNumber($totalR['a_recuperer']).'</td>
    </tr>';
        $html_imm_row .=$html_imm_D;
        $html_imm_row .= '
    <tr class="box-border">
        <td>TOTAL GENERAL</td>
        <td class="box-border-left">'.$total['U_stock'].'</td>
        <td>'.$total['U_bloque'].'</td>
        <td>'.$total['U_reserve'].'</td>
        <td class="box-border-right">'.$total['U_solde'].'</td>
        <td>'.formatNumber($total['CA_stock']).'</td>
        <td>'.formatNumber($total['CA_bloque']).'</td>
        <td>'.formatNumber($total['CA_reserve']).'</td>
        <td class="box-border-right">'.formatNumber($total['CA_solde']).'</td>
        <td>'.formatNumber($total['avances']).'</td>
        <td>'.formatNumber($total['notaires']).'</td>
        <td class="box-border-right">'.formatNumber($total['chez_notaires']).'</td>
        <td>'.formatNumber($total['mlv_payees']).'</td>
        <td class="box-border-right">'.formatNumber($total['mlv_a_payer']).'</td>
        <td>'.formatNumber($total['encaisse_anfa_rea']).'</td>
        <td>'.formatNumber($total['a_verser']).'</td>
        <td>'.formatNumber($total['verse']).'</td>
        <td class="box-border-right">'.formatNumber($total['a_recuperer']).'</td>
    </tr>';
    return $html_imm_row.'</tbody></table>';
}
function generate_project_list() {
    $sql = "SELECT * FROM projets";
    $result = sql($sql);
    $html = '';
    foreach ($result as $row) {
        $projectName = htmlspecialchars($row['nom_projet']); 
        $projectCode = htmlspecialchars($row['code_projet']); 
        $lastSync = htmlspecialchars($row['last_update']); 
        $html .= '<option value="' . $projectCode . '"  last-sync="' . $lastSync . '">' . $projectName . '</option>
        ';
    }
    echo $html;
}
function importBaseCommerciale($fileToImport,$table){
    ini_set('memory_limit', '4G');

    global $ADVconn;
    if ($ADVconn->connect_error) {
        die("Connection failed: " . $ADVconn->connect_error);
    }

    $reader = new Xlsx();
    $reader->setReadDataOnly(true);
    $reader->setIncludeCharts(false); 
    $reader->setReadEmptyCells(false);
    $sheetNames = $reader->listWorksheetNames($fileToImport);
    $spreadsheet = $reader->load($fileToImport);

    if($table=="base_kpc"){
        $sheet = $spreadsheet->getSheetByName('ETAT GENERAL KPC');
    }else{
        $sheet = $spreadsheet->getSheetByName('BASE');
    }
    // Get highest row/column to limit iteration
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);


    // Read header row
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $headers[] = $sheet->getCell($colLetter . '1')->getValue();
    }

    $total=0;
    // Loop through each data row
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $cell = $sheet->getCell($colLetter . $row);
            $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getValue();
            $rowData[] = $value;
        }
        // Skip empty rows
        if (count(array_filter($rowData)) === 0) continue;
        if($table=="base_uptown"){
            $codesap = isset($rowData[array_search('CODE SAP', $headers)]) ? $rowData[array_search('CODE SAP', $headers)] : '';
            $numero = isset($rowData[array_search('N° BIEN', $headers)]) ? $rowData[array_search('N° BIEN', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('ETAGE', $headers)]) ? $rowData[array_search('ETAGE', $headers)] : '';
            if($immeuble=="D"){
                $typologie="Bureau";
                $prix_au_m2= isset($rowData[array_search('PRIX / M²', $headers)]) ? $rowData[array_search('PRIX / M²', $headers)] : 0;
            }else{
                $typologie="Appartement";
                $prix_au_m2= isset($rowData[array_search('PRIX / m²', $headers)]) ? $rowData[array_search('PRIX / m²', $headers)] : 0;
            }
            $pv = isset($rowData[array_search('PRIX DE VENTE', $headers)]) ? $rowData[array_search('PRIX DE VENTE', $headers)] : 0;
            $av_en = isset($rowData[array_search('AVANCES ENCAISSEES', $headers)]) ? $rowData[array_search('AVANCES ENCAISSEES', $headers)] : 0;
            $etat_mlv = isset($rowData[array_search('ETAT MLV', $headers)]) ? $rowData[array_search('ETAT MLV', $headers)] : 0;
            $rest_du_encai = isset($rowData[array_search('RESTE Dû ENCAISSE', $headers)]) ? $rowData[array_search('RESTE Dû ENCAISSE', $headers)] : 0;
            $chez_notaires = isset($rowData[array_search('REGLEMENT ENTRE LES MAINS DES NOTAIRES', $headers)]) ? $rowData[array_search('REGLEMENT ENTRE LES MAINS DES NOTAIRES', $headers)] : 0;
            $date = isset($rowData[array_search('DATE DE RESERVATION', $headers)]) ? $rowData[array_search('DATE DE RESERVATION', $headers)] : 1000000;
            $newDate=convertDate($date);
            $av=$av_en+$etat_mlv+$rest_du_encai;
            
            $sql = "INSERT INTO ".$table." (projet, code_sap, numero, statut, immeuble,etage,typologie,prix_vente,avances,avances_encaissees,chez_notaires,date_res,prix_au_m2) 
            VALUES ('UP','$codesap','$numero','$statut','$immeuble','$etage','$typologie','$pv','$av_en','$av','$chez_notaires','$newDate','$prix_au_m2')";
        }elseif ($table=="base_kpc") {
            $codesap = isset($rowData[array_search('CodeSAP', $headers)]) ? $rowData[array_search('CodeSAP', $headers)] : '';
            $numero = isset($rowData[array_search('N°', $headers)]) ? $rowData[array_search('N°', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $tranche = isset($rowData[array_search('TRANCHE', $headers)]) ? $rowData[array_search('TRANCHE', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('ETAGE', $headers)]) ? $rowData[array_search('ETAGE', $headers)] : '';
            $typologie = isset($rowData[array_search('TYPE', $headers)]) ? $rowData[array_search('TYPE', $headers)] : '';
            if($typologie=="MAG"){
                $typologie="Magasin";
            }elseif($typologie=="BUR"){
                $typologie="Bureau";
            }elseif($typologie=="APPT"){
                $typologie="Appartement";
            }
            $pv = isset($rowData[array_search('PRIX DE VENTE REMISE', $headers)]) ? $rowData[array_search('PRIX DE VENTE REMISE', $headers)] : 0;
            $avances = isset($rowData[array_search('TOTAL AVANCES', $headers)]) ? $rowData[array_search('TOTAL AVANCES', $headers)] : 0;
            $date = isset($rowData[array_search('DATE COMPROMIS DE VENTE', $headers)]) ? $rowData[array_search('DATE COMPROMIS DE VENTE', $headers)] : 1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('PRIX REMISE /m²', $headers)]) ? $rowData[array_search('PRIX REMISE /m²', $headers)] : 0;
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,tranche,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('KPC','$codesap','$numero','$statut','$immeuble','$tranche','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }elseif ($table=="base_mno") {
            $codesap = isset($rowData[array_search('SAP', $headers)]) ? $rowData[array_search('SAP', $headers)] : '';
            $numero = isset($rowData[array_search('N°', $headers)]) ? $rowData[array_search('N°', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('ETAGE', $headers)]) ? $rowData[array_search('ETAGE', $headers)] : '';
            $typologie = isset($rowData[array_search('TYPE', $headers)]) ? $rowData[array_search('TYPE', $headers)] : '';
            if($typologie=="MAG"){
                $typologie="Magasin";
            }elseif($typologie=="BUR"){
                $typologie="Bureau";
            }
            $pv = isset($rowData[array_search('PRIX DE VENTE DEF', $headers)]) ? $rowData[array_search('PRIX DE VENTE DEF', $headers)] : 0;
            $avances = isset($rowData[array_search('TOTAL AVANCE', $headers)]) ? $rowData[array_search('TOTAL AVANCE', $headers)] : 0;
            $date = isset($rowData[array_search('DATE DE RESERVATION', $headers)]) ? $rowData[array_search('DATE DE RESERVATION', $headers)] : 1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('PRIX/M² DEF', $headers)]) ? $rowData[array_search('PRIX/M² DEF', $headers)] : 0;
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('MNO','$codesap','$numero','$statut','$immeuble','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }elseif ($table=="base_mt") {
            $codesap = isset($rowData[array_search('Code SAP', $headers)]) ? $rowData[array_search('Code SAP', $headers)] : '';
            $numero = isset($rowData[array_search('N° BIEN', $headers)]) ? $rowData[array_search('N° BIEN', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $etage = isset($rowData[array_search('Etage', $headers)]) ? $rowData[array_search('Etage', $headers)] : '';
            $typologie = isset($rowData[array_search('Typologie', $headers)]) ? $rowData[array_search('Typologie', $headers)] : '';
            $pv = isset($rowData[array_search('PRIX DE VENTE DEF', $headers)]) ? $rowData[array_search('PRIX DE VENTE DEF', $headers)] : 0;
            $avances = isset($rowData[array_search('AVANCES', $headers)]) ? $rowData[array_search('AVANCES', $headers)] : 0;
            $date = isset($rowData[array_search('DATE DE RESERVATION', $headers)]) ? $rowData[array_search('DATE DE RESERVATION', $headers)] : 1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('Prix/M² DEF', $headers)]) ? $rowData[array_search('Prix/M² DEF', $headers)] : 0;
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('MT','$codesap','$numero','$statut','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }elseif ($table=="base_op") {
            $codesap = isset($rowData[array_search('SAP', $headers)]) ? $rowData[array_search('SAP', $headers)] : '';
            $numero = isset($rowData[array_search('NUMEROTATION COMMERCIALE', $headers)]) ? $rowData[array_search('NUMEROTATION COMMERCIALE', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $tranche = isset($rowData[array_search('TRANCHE', $headers)]) ? $rowData[array_search('TRANCHE', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('NIVEAU', $headers)]) ? $rowData[array_search('NIVEAU', $headers)] : '';
            $typologie = isset($rowData[array_search('TYPE', $headers)]) ? $rowData[array_search('TYPE', $headers)] : '';
            if($typologie=="APPT"){
                $typologie="Appartement";
            }elseif($typologie=="BURE"){
                $typologie="Bureau";
            }elseif($typologie=="MAG"){
                $typologie="Magasin";
            }
            $pv = isset($rowData[array_search('PRIX TOPO DEF', $headers)]) ? $rowData[array_search('PRIX TOPO DEF', $headers)] : 0;
            $avances = isset($rowData[array_search('TOTAL AVANCES', $headers)]) ? $rowData[array_search('TOTAL AVANCES', $headers)] : 0;
            $date = isset($rowData[array_search('DATE DE RESERVATIONS', $headers)]) ? $rowData[array_search('DATE DE RESERVATIONS', $headers)] :1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('PRIX / m² DEF', $headers)]) ? $rowData[array_search('PRIX / m² DEF', $headers)] : 0;
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,tranche,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('OP','$codesap','$numero','$statut','$immeuble','$tranche','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }elseif ($table=="base_sh") {
                $codesap = isset($rowData[array_search('CodeSAP', $headers)]) ? $rowData[array_search('CodeSAP', $headers)] : '';
                $numero = isset($rowData[array_search('N° BIEN', $headers)]) ? $rowData[array_search('N° BIEN', $headers)] : '';
                $date = isset($rowData[array_search('DATE DE RESERVATION', $headers)]) ? $rowData[array_search('DATE DE RESERVATION', $headers)] :1000000;
                $newDate=convertDate($date);
                
            if($fileToImport=="/mnt/adv/Bases Commerciales/SOHAUS/BASE COMMERCIALE SOHAUS source.xlsx"){
                $bloc = isset($rowData[array_search('Bloc', $headers)]) ? $rowData[array_search('Bloc', $headers)] : '';
                $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
                $etage = isset($rowData[array_search('Etage', $headers)]) ? $rowData[array_search('Etage', $headers)] : '';
                $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
                $typologie = "Appartement";
                $pv = isset($rowData[array_search('PRIX DEF', $headers)]) ? $rowData[array_search('PRIX DEF', $headers)] : 0;
                $avances = isset($rowData[array_search('AVANCES', $headers)]) ? $rowData[array_search('AVANCES', $headers)] : 0;
                $prix_au_m2= isset($rowData[array_search('Prix/m² DEF', $headers)]) ? $rowData[array_search('Prix/m² DEF', $headers)] : 0;
                $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,bloc,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
                VALUES ('SH','$codesap','$numero','$statut','$immeuble','$bloc','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
            }else{
                $pv = isset($rowData[array_search('PRIX DE VENTE DEF', $headers)]) ? $rowData[array_search('PRIX DE VENTE DEF', $headers)] : 0;
                $avances = isset($rowData[array_search('AVANCE', $headers)]) ? $rowData[array_search('AVANCE', $headers)] : 0;
                $statut = isset($rowData[array_search('Etat', $headers)]) ? $rowData[array_search('Etat', $headers)] : '';
                $typologie = "TownHaus";
                $bloc = "";
                $prix_au_m2= isset($rowData[array_search('PRIX/m² DEF', $headers)]) ? $rowData[array_search('PRIX/m² DEF', $headers)] : 0;
                $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
                VALUES ('SH','$codesap','$numero','$statut','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
            }
            if($bloc=="D") continue;
        }elseif ($table=="base_wl") {
            $codesap = isset($rowData[array_search('CODE SAP', $headers)]) ? $rowData[array_search('CODE SAP', $headers)] : '';
            $numero = isset($rowData[array_search('N° BIEN', $headers)]) ? $rowData[array_search('N° BIEN', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('ETAGE', $headers)]) ? $rowData[array_search('ETAGE', $headers)] : '';
            $pv = isset($rowData[array_search('PRIX DE VENTE', $headers)]) ? $rowData[array_search('PRIX DE VENTE', $headers)] : 0;
            $avances = isset($rowData[array_search('AVANCE', $headers)]) ? $rowData[array_search('AVANCE', $headers)] : 0;
            $date = isset($rowData[array_search('DATE DE RESERVATION', $headers)]) ? $rowData[array_search('DATE DE RESERVATION', $headers)] :1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('PRIX / M²', $headers)]) ? $rowData[array_search('PRIX / M²', $headers)] : 0;
            if($fileToImport=="/mnt/adv/Bases Commerciales/WELIVE/BASE COMMERCIALE COLIVING source.xlsx"){
                $typologie = "Coliving";
            }else{
                $typologie = "Conventionne";
            }
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('WL','$codesap','$numero','$statut','$immeuble','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }elseif ($table=="base_zt") {
            $codesap = isset($rowData[array_search('SAP', $headers)]) ? $rowData[array_search('SAP', $headers)] : '';
            $numero = isset($rowData[array_search('N°', $headers)]) ? $rowData[array_search('N°', $headers)] : '';
            $statut = isset($rowData[array_search('ETAT', $headers)]) ? $rowData[array_search('ETAT', $headers)] : '';
            $tranche = isset($rowData[array_search('TR', $headers)]) ? $rowData[array_search('TR', $headers)] : '';
            $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
            $etage = isset($rowData[array_search('ETAGE', $headers)]) ? $rowData[array_search('ETAGE', $headers)] : '';
            $typologie = isset($rowData[array_search('TYPO', $headers)]) ? $rowData[array_search('TYPO', $headers)] : '';
            if($typologie=="APPT"){
                $typologie="Appartement";
            }elseif($typologie=="MAG"){
                $typologie="Magasin";
            }
            $pv = isset($rowData[array_search('PRIX DEFINITIF AVEC PARK', $headers)]) ? $rowData[array_search('PRIX DEFINITIF AVEC PARK', $headers)] : 0;
            $avances = isset($rowData[array_search('TOTAL AVANCES', $headers)]) ? $rowData[array_search('TOTAL AVANCES', $headers)] : 0;
            $date = isset($rowData[array_search('DATE COMPROMIS DE VENTE', $headers)]) ? $rowData[array_search('DATE COMPROMIS DE VENTE', $headers)] :1000000;
            $newDate=convertDate($date);
            $prix_au_m2= isset($rowData[array_search('PRIX/m² 30/09/22', $headers)]) ? $rowData[array_search('PRIX/m² 30/09/22', $headers)] : 0;
            $sql = "INSERT INTO ".$table." (projet, code_sap,numero, statut, immeuble,tranche,etage,typologie,prix_vente,avances_encaissees,date_res,prix_au_m2) 
            VALUES ('ZT','$codesap','$numero','$statut','$immeuble','$tranche','$etage','$typologie','$pv','$avances','$newDate','$prix_au_m2')";
        }
        
        $ADVconn->query($sql);
        $total+=1;
    }

    return $fileToImport;
}
function convertDate($dateValue){
    if (is_numeric($dateValue)) {
        // Excel base date is 1900-01-01
        $unix_date = ($dateValue - 25569) * 86400; // convert to UNIX timestamp
        $date = gmdate("Y-m-d", $unix_date);
    } else {
        // If already string, try parsing it
        $date = date("Y-m-d", strtotime($dateValue));
    }
    return $date;
}
function logMessage($message) {
    $logFile = '/var/log/imp/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function importSituation($fileToImport,$table){
    ini_set('memory_limit', '4G');
    global $ADVconn;
    if ($ADVconn->connect_error) {
        die("Connection failed: " . $ADVconn->connect_error);
    }

    $reader = new Xlsx();
    $reader->setReadDataOnly(true);

    $spreadsheet = $reader->load($fileToImport);
    $sheet = $spreadsheet->getSheetByName('SUIVI');
    if (!$sheet) {
        die('Sheet "SUIVI" not found.');
    }

    // Get highest row/column to limit iteration
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
    // Read header row
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $headers[] = $sheet->getCell($colLetter . '1')->getValue();
    }
    $total=0;
    // Loop through each data row
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $cell = $sheet->getCell($colLetter . $row);
            $value = $cell->isFormula() ? $cell->getCalculatedValue() : $cell->getValue();
            $rowData[] = $value;
        }
        // Skip empty rows
        if (count(array_filter($rowData)) === 0) continue;

        $bien = isset($rowData[array_search('N° BIEN', $headers)]) ? $rowData[array_search('N° BIEN', $headers)] : '';
        $immeuble = isset($rowData[array_search('IMM', $headers)]) ? $rowData[array_search('IMM', $headers)] : '';
        $chez_notaires = isset($rowData[array_search('REGLEMENT ENTRE LES MAINS DES NOTAIRES', $headers)]) ? $rowData[array_search('REGLEMENT ENTRE LES MAINS DES NOTAIRES', $headers)] : 0;
        $mlv_payees = isset($rowData[array_search('MLV PAYEES 70%', $headers)]) ? $rowData[array_search('MLV PAYEES 70%', $headers)] : 0;
        $mlv_a_payer = isset($rowData[array_search('MLV A PAYER 70%', $headers)]) ? $rowData[array_search('MLV A PAYER 70%', $headers)] : 0;
        $enc_anfra_rea = isset($rowData[array_search('RESTE Dû ENCAISSE ANFA REA', $headers)]) ? $rowData[array_search('RESTE Dû ENCAISSE ANFA REA', $headers)] : 0;
        $reste_du = isset($rowData[array_search('RESTE Dû', $headers)]) ? $rowData[array_search('RESTE Dû', $headers)] : 0;
        $verse = isset($rowData[array_search('RESTE Dû VERSE AUX NOTAIRES', $headers)]) ? $rowData[array_search('RESTE Dû VERSE AUX NOTAIRES', $headers)] : 0;

        $avances = isset($rowData[array_search('TOTAL AVANCES', $headers)]) ? $rowData[array_search('TOTAL AVANCES', $headers)] : 0;
        $prix_vente = isset($rowData[array_search('PRIX DE VENTE', $headers)]) ? $rowData[array_search('PRIX DE VENTE', $headers)] : 0;
        $notaire = isset($rowData[array_search('NOTAIRE', $headers)]) ? $rowData[array_search('NOTAIRE', $headers)] : 0;

        $sql = "INSERT INTO ".$table." (projet, bien, immeuble,chez_notaires,mlv_payees,mlv_a_payer,encaisse_anfa_rea,reste_du,verse,prix_vente,notaire,avances) 
                VALUES ('UP','$bien','$immeuble','$chez_notaires','$mlv_payees','$mlv_a_payer','$enc_anfra_rea','$reste_du','$verse','$prix_vente','$notaire','$avances')";
        $ADVconn->query($sql);
        $total+=1;
    }

    return $total;
}
function import_data($projet){
    global $ADVconn;
    $res='';
    $resultArray = [];
    $sql = "SELECT nom_projet,import,tables FROM projets where code_projet ='".$projet."'";
    $result = sql($sql);
    $dossier=$result[0]["nom_projet"];
    $tables=$result[0]["tables"];
    $basefolder = "/mnt/adv/Bases Commerciales/" . $dossier . "/";
    $liste_fichiers = explode(",", $result[0]["import"]);
    clearTables($tables);
    foreach ($liste_fichiers as $entry) {
        $liste_2 = explode(":", $entry);
        $fichier=$liste_2[0].".xlsx";
        $table=$liste_2[1];
        $fileToimport=$basefolder.$fichier;
        if(str_starts_with($table, "base_")){
            $total=importBaseCommerciale($fileToimport,$table);
        }else{
            $total=importSituation($fileToimport,$table);
        }
        $res= $fichier. " : ".$total." lignes importées !";
        $resultArray[$fichier] = $total;
    }
    
    return $resultArray;
}
function getProjectName($projet){
    $sql = "SELECT nom_projet,import FROM projets where code_projet ='".$projet."'";
    $result = sql($sql);
    return $result[0]["nom_projet"];
}
function clearTables($tables){
    global $ADVconn;
    $tablesListe=explode(",", $tables);
    foreach ($tablesListe as $table) {
        $sql = "DELETE FROM ".$table;
        $ADVconn->query($sql);
    }
}
function updateDate($projet){
    global $ADVconn;
    $nowValue = date("Y-m-d H:i:s");
    $sql = "UPDATE projets SET last_update = ? WHERE code_projet = ?";
    $stmt = $ADVconn->prepare($sql);
    $stmt->bind_param("ss", $nowValue, $projet);
    $stmt->execute();
    return $nowValue;
}

function getFilesWithLastUpdate($projet) {
    $dir = "/mnt/adv/Bases Commerciales/" . $projet . "/";

    $lockedFiles = [];
    $htmlLocked = '';   
    $htmlNormal = '';  

    foreach (scandir($dir) as $file) {
        $filePath = $dir . $file;

        if (is_file($filePath) && strpos($file, '~$') === 0) {
            $realFile = substr($file, 2); 
            $lockedFiles[$realFile] = true;

            $htmlLocked .= '<div class="file-data">';
            $htmlLocked .= '<img src="./assets/xlsx.png" alt="" width="32">';
            $htmlLocked .= '<p>'.$realFile.'</p>';
            $htmlLocked .= '<p class="fileDate">par : '.  getLockFileUser($filePath).'</p>';
            $htmlLocked .= '</div>';
        }
    }

    foreach (scandir($dir) as $file) {
        $filePath = $dir . $file;

        if (is_file($filePath) && strpos($file, '~$') !== 0) {
            if (!isset($lockedFiles[$file])) {
                $htmlNormal .= '<div class="file-data">';
                $htmlNormal .= '<img src="./assets/xlsx.png" alt="" width="32">';
                $htmlNormal .= '<p>'.$file.'</p>';
                $htmlNormal .= '<p class="fileDate">'. date("Y-m-d H:i:s", filemtime($filePath)).'</p>';
                $htmlNormal .= '</div>';
            }
        }
    }

    $finalHtml = '';

    if ($htmlNormal !== '') {
        $finalHtml .= '<div class="file-update">';
        $finalHtml .= '<p class="files-header">Dernière Modification</p>';
        $finalHtml .= '<div class="files-container">'.$htmlNormal.'</div>';
        $finalHtml .= '</div>';
    }

    if ($htmlLocked !== '') {
        $finalHtml .= '<div class="file-update">';
        $finalHtml .= '<p class="files-header">Modification En cours</p>';
        $finalHtml .= '<div class="files-container">'.$htmlLocked.'</div>';
        $finalHtml .= '</div>';
    }

    return $finalHtml;
}
function getLockFileUser($lockFilePath) {
    if (!file_exists($lockFilePath)) return null;

    $output = [];
    exec('strings ' . escapeshellarg($lockFilePath), $output);

    foreach ($output as $line) {
        $line = trim($line);
        if ($line !== '') {
            return $line; 
        }
    }

    return null;
}
function generateTable($projet){
    $html='';
    if ($projet=='UP') {
        $html='<div id="table-recouvrement">'.uptownHeader().calculateUptown().'</div>';
        $html.='<div id="table-noatires">'.uptownNotaireHeader().calculateUptownNotaire().'</div>';
    }else {
        $html='No data found';
    }
    return $html;
}


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        $result = [];
        if ($_GET["action"]=="import") {
            
            $projet=$_GET["projet"];
            $nom_projet=getProjectName($projet);
            $result = import_data($projet);
            $updateDate=updateDate($projet);
            $filesDate=getFilesWithLastUpdate($nom_projet);
            $data=generateTable($projet);
        }elseif ($_GET["action"]=="data") {
            $projet=$_GET["projet"];
            $nom_projet=getProjectName($projet);
            $result="none";
            $updateDate="none";
            $filesDate=getFilesWithLastUpdate($nom_projet);
            $data=generateTable($projet);   
        }elseif ($_GET["action"]=="export") {
            $projet=$_GET["projet"];
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=situation_" . date("Y-m-d") . ".xls");
            echo excelHeader().calculateUptown();
            exit();
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "imported" => $result,
            "lastUpdate" => $updateDate,
            "data" => $data,
            "filesDate" => $filesDate,
        ]);
        exit();
    }
}

?>

<h1>Situation recouverement à date</h1>
<div class="adv-header-container">
    <button class ="sync-btn all-projects" onclick="refreshALL()">Actualiser Tous les projets</button>

    <div class="adv-projects">
        <fieldset class="item-box">
            <legend class="filter-type-title">Projet</legend>
            <select class ="item-select" name="adv-project" id="adv-project" onChange="getAdvData()">
                <option value="NON">-- Sélectionnez --</option>
                <?php generate_project_list(); ?>
            </select>
        </fieldset>
    </div>
    <button class ="sync-btn" onclick="printADV()"><img src="./assets/printer.png" alt="" width="24"></button>
    <button class ="sync-btn" onclick="exportExcel()"><img src="./assets/excel.png" alt="" width="24"></button>
    <button class ="sync-btn" onclick="refreshData()"><img src="./assets/refresh.png" alt="" width="24"></button>
    <div class="sync-date">Dernière Synchronisation le : <span id="syncDate"> </span></div>
</div>
<div id="update-container"></div>
<div class="adv-data-container">
    <h2 id="adv-search-title" hidden>Récupération des données ... </h2>
    <img src="./assets/spinner.gif" alt="spinner" class="spinner" id="spinneradv" hidden>
    <div id="adv-data">

    </div>
</div>
<div class="refresh-overlay" id="refresh-overlay">
    <div class="ref-pro-list">
        <div class="projet-refresh"><img src="./assets/loading.gif" alt="spinner"><span>Projet 1</span></div>
        <div class="projet-refresh"><img src="./assets/check.png" alt="spinner"><span>Projet 2</span></div>
        <div class="projet-refresh"><img src="./assets/no.png" alt="spinner"><span>Projet 3</span></div>
    </div>
    <button class ="sync-btn all-projects" onclick="closeRefreshOverlay()">Fermer</button>
</div>

<script>
function closeRefreshOverlay(){
    document.getElementById('refresh-overlay').style.display="none"
}
function refreshALL() {
    document.getElementById('refresh-overlay').style.display = "flex";

    const select = document.getElementById('adv-project');
    const container = document.querySelector('.ref-pro-list');
    container.innerHTML = '';

    // 1️⃣ First loop — create loading divs
    for (let i = 1; i < select.options.length; i++) {
        const option = select.options[i];

        const div = document.createElement('div');
        div.className = 'projet-refresh';

        const img = document.createElement('img');
        img.src = './assets/loading.gif';
        img.alt = 'spinner';

        const span = document.createElement('span');
        span.textContent = option.text;

        div.appendChild(img);
        div.appendChild(span);

        div.dataset.projetValue = option.value;
        container.appendChild(div);
    }

    // 2️⃣ Sequential AJAX requests
    async function processProjectsSequentially() {
        for (let i = 1; i < select.options.length; i++) {
            const option = select.options[i];
            const projetValue = option.value;
            const projetDiv = container.querySelector(`[data-projet-value="${projetValue}"]`);
            const img = projetDiv.querySelector('img');

            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: './src/screens/adv.php',
                    dataType: 'json',
                    headers: { 'Content-Type': 'application/json; charset=UTF-8' },
                    data: { 
                        action: "import",
                        projet: projetValue
                    }
                });

                img.src = './assets/check.png'; // ✅ success
                document.getElementById('syncDate').textContent = response.lastUpdate;   
                option.setAttribute('last-sync', response.lastUpdate);
                document.getElementById("update-container").innerHTML = response.filesDate;

            } catch (error) {
                img.src = './assets/no.png'; // ❌ error
                console.error('AJAX Error:', error);
            }
        }

        // 3️⃣ When all done
        //document.getElementById('refresh-overlay').style.display = "none";
    }

    processProjectsSequentially();
}

function refreshData(){
    const select = document.getElementById('adv-project');
    const selectedOption = select.options[select.selectedIndex]; 
    const lastSync = selectedOption.getAttribute('last-sync'); 
    const projet=selectedOption.value
    document.getElementById('syncDate').textContent = lastSync;
    if( projet =="NON") {
        alert("Séléctionner un projet!")
        return;
    }
    document.getElementById("adv-data").innerHTML="";
    document.getElementById("spinneradv").hidden=false;
    document.getElementById("adv-search-title").hidden=false;
    $.ajax({
        type: 'GET',
        url: './src/screens/adv.php',
        dataType: 'json',
        headers: {
                'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
            action:"import",
            projet:projet
        },
        success: function (response) {
            console.log(response)
            document.getElementById("spinneradv").hidden=true;
            document.getElementById("adv-search-title").hidden=true;
            document.getElementById("adv-data").innerHTML=response.data;  
            document.getElementById('syncDate').textContent = response.lastUpdate;   
            selectedOption.setAttribute('last-sync',response.lastUpdate)
            document.getElementById("update-container").innerHTML=response.filesDate;
        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}
function getAdvData() {
    const select = document.getElementById('adv-project');
    const selectedOption = select.options[select.selectedIndex]; 
    const lastSync = selectedOption.getAttribute('last-sync'); 

    const projet=selectedOption.value
    if( projet =="NON") {
        return;
    }
    document.getElementById('syncDate').textContent = lastSync;
    document.getElementById("adv-data").innerHTML="";
    document.getElementById("spinneradv").hidden=false;
    document.getElementById("adv-search-title").hidden=false;
    $.ajax({
        type: 'GET',
        url: './src/screens/adv.php',
        dataType: 'json',
        headers: {
                'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
            action:"data",
            projet:projet
        },
        success: function (response) {
            console.log(response)
            document.getElementById("spinneradv").hidden=true;
            document.getElementById("adv-search-title").hidden=true;
            document.getElementById("adv-data").innerHTML=response.data;  
            document.getElementById("update-container").innerHTML=response.filesDate;  
        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });

}

function printADV(){
        const page = document.getElementById("table-recouvrement");
    let printContent = '';
    printContent += '<img src="./assets/MG-logo.png" alt="Logo" width="200">';
    printContent += page.outerHTML ;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
        <head>
          <title>Print Preview</title>
          <style>
            @page { margin: 0; }
            body { font-family: Arial, sans-serif;print-color-adjust: exact;}
            #adv-table-recouvrement { width: 100%; border: 1;margin-top: 20px }
            #adv-data { padding: 0px; }
            #adv-table-recouvrement thead tr th{
                background-color: #547471;
                color: white;
            }
            #adv-table-recouvrement .adv-table tr td:nth-child(1){
            background-color: var(--primary-color);
            color: black;
            font-weight: bold;
            }
            #adv-data{
            width: 100%;
            }
            #adv-table-recouvrement thead .adv-header-titles{
            font-size: 8px;
            }
            .adv-table-body .box-border{
            border: 2px solid black;
            font-weight: bold;
            }
            .box-border-left{ border-left: 2px solid black;}
            .box-border-right{ border-right: 2px solid black;}
            .box-border-top{ border-top: 2px solid black;}
            .box-border-bottom{ border-bottom: 2px solid black;}
            .adv-table-body tr td{
            text-align: center;
            font-size: 9px;
            }
          </style>
        </head>
        <body>${printContent}
        
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}


function exportExcel(){
    // Build the URL with params
    let url = './src/screens/adv.php?action=export&projet=123';
    // Trigger download
    window.location.href = url;
}
</script>