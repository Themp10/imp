<?php
$BCconn = new mysqli("localhost", "sa", "MG+P@ssw0rd", "BC");
$projets=["MT","WL", "SH", "KPC", "MNO", "OP", "CP", "BA", "UP","ZT"];
$projects = [
    //"MT" =>  ["Bureau", "Commerce", "Archive"],
    "MT" =>  ["Bureau"],
    "WL" =>  ["Conventionne", "Coliving"],
    "SH" =>  ["Appartement", "TownHaus"],
    "KPC" => ["Appartement", "Magasin", "Bureau"],
    "MNO" => ["Magasin", "Bureau"],
    "OP" =>  ["Appartement", "Magasin", "Bureau"],
    "CP" =>  ["Appartement", "Magasin", "Bureau"],
    "BA" =>  ["Appartement", "Magasin"],
    "UP" =>  ["Appartement","Bureau"],
    
    "ZT" =>  ["Appartement", "Magasin"]
];
$statuts = [
    "Disponible" =>  0,
    "Réservé" =>  2,
    "Soldé" => 6,
    "Bloqué" => 8,
    "Loué" =>  5
];
function getSoc($projet){
    $soc="";
    switch ($projet) {
        case "MT":
        $soc="ANFA_69";
        break;
        case "WL":
        $soc="CASA_COLIVING";
        break;
        case "SH":
        $soc="NAVIS_PROPERTY";
        break;
        case "KPC":
        $soc="YASMINE_FONCIERE";
        break;
        case "MNO":
        $soc="YASMINE_FONCIERE";
        break;
        case "OP":
        $soc="RMM_BUILDING";
        break;
        case "CP":
        $soc="RMM_BUILDING";
        break;
        case "BA":
        $soc="AM_PROINVEST";
        break;
        case "UP":
        $soc="ANFA_REALISATION";
        break;
        case "UPBC":
        $soc="ANFA_REALISATION";
        break;
        case "ZT":
        $soc="M_PROPERTIES";
        break;
        default:
        $soc="";
        break;
        }
    return $soc;
}
function Hana_Reporting($sql){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);
 
    $data=[];
    //$setCharset = odbc_exec($Hanaconn, "SET NAMES UTF8");
    //$setCharset = odbc_exec($Hanaconn, "SET CHARACTER SET UTF8");
    $setDb = odbc_exec($Hanaconn, "SET SCHEMA " . "SYSTEM");
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
function getProjets($id){
    
    switch ($id) {
    case "WL":
        $soc="WE LIVE";
        break;
    case "SH":
        $soc="SOHAÜS";
        break;
    case "KPC":
        $soc="KPC";
        break;
    case "MNO":
        $soc="MNO";
        break;
    case "OP":
        $soc="OCEAN PARK";
        break;
    case "CP":
        $soc="CENTRAL PARK";
        break;
    case "BA":
        $soc="BEL AIR";
        break;
    case "UP":
        $soc="UPTOWN";
        break;
    case "UPBC":
        $soc="UPTOWN BC";
        break;
    case "ZT":
        $soc="ZENATA TOWER";
        break;
    case "MT":
        $soc="MTOWER";
        break;
    default:
        $soc="";
        break;
    }
    return $soc;
}
function generateProjectList($projets){
    $html="";
    foreach($projets as $projet){
        $soc=getProjets($projet);
        $html.="<button class='project-btn' id='project-$projet' onclick='selecteProject(this)'>$soc</button>";
    }
    return $html;
}

function checkType($type,$SAP,$BC){
    if($type=="SAP"){
        return $SAP;
    }elseif($type=="BC"){
        return $BC;
    }elseif($type=="All"){
        return $SAP.' / '.$BC;
    }
}
function getCaSTock($projet,$type,$current_from,$current_to,$previous_from,$previous_to){
    global $projects;
    $html1="";
    $html2="";
    $html3="";

    $labels = [];
    $labelsG2 = [];
    $labelsG3 = [];

    $qte = [];
    $objectif = [];
    $realise = [];
    $resteaEnc = [];

    $TtU=$TtCA=$TuL=$TuR=$TuS=$TuB=$TPPUCUM=$TPPU=$TCPU=$TCPCA=$TCPUCUM=$TCCACUM=$TPPCACUM=$TobjU=$Tecart=$TUObjN=$TCAObjN=$TenCaDate=0;
    $BCTtU=$BCTtCA=$BCTuL=$BCTuR=$BCTuS=$BCTuB=$BCTPPUCUM=$BCTPPU=$BCTCPU=$BCTCPCA=$BCTCPUCUM=$BCTCCACUM=$BCTPPCACUM=$BCTobjU=$BCTecart=$BCTUObjN=$BCTCAObjN=$BCTenCaDate=$TDesP=$TDesC=0;
    
    $societe=getSoc($projet);
    $sqlBCq='SELECT lecture FROM projets WHERE code_projet="'.$projet.'"';
    $table=sqlBC($sqlBCq)[0]?sqlBC($sqlBCq)[0]["lecture"]:"no_table";
    foreach ($projects[$projet] as $typology) {
        if($projet=='UP' and $typology=='Bureau'){
            $projet="UPBC";
        }
        $tmp=0;
        $sql='select "StatutBien","U_StatutBien",count(*) as "U",TO_DECIMAL(sum("Price"),18,2) as "CA"   from "V_OITM"
            where  "U_Projet"=\''.$projet.'\'   and "TypeBien"=\''.$typology.'\'
            group by "StatutBien","U_StatutBien" order by "U_StatutBien"';
        $data=Hana_Reporting($sql);

        $sql='select "V_OITM"."U_StatutBien",count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" 
                from "V_ORDR" "V_ORDR" 
                LEFT OUTER JOIN  "V_RDR1" "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
                LEFT OUTER JOIN  "V_OITM" "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and "V_RDR1"."LineNum"=\'0\' and "V_OITM"."U_Projet"=\''.$projet.'\' and  "TypeBien"=\''.$typology.'\'
                where   "V_ORDR"."CANCELED"=\'N\'
                group by "V_OITM"."U_StatutBien" order by "V_OITM"."U_StatutBien"';
        $data2=Hana_Reporting($sql);

        $sqlPM2='SELECT typologie,AVG(prix_au_m2) AS "avg" FROM '.$table.' WHERE typologie="'.$typology.'" and  prix_au_m2>0 GROUP BY typologie';
        if($table=="no_table"){
            $pm2=0;
        }else{
            $pm2=sqlBC($sqlPM2)[0]?round(sqlBC($sqlPM2)[0]["avg"]/1000,1):0;
        }
        //requete pour la BC
        $sql='SELECT statut,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM '.$table.' WHERE typologie="'.$typology.'" GROUP BY statut';
        $data3=sqlBC($sql);
        $BCTU=$BCTCA=$BCuL=$BCcaL=$BCuLO=$BCcaLO=$BCuR=$BCcaR=$BCuS=$BCcaS=$BCuB=$BCcaB=0;
        foreach ($data3 as $row) {
            if($row["statut"]=='DISPONIBLE'){
                $BCuL=$row["U"];
                $BCcaL=$row["CA"];
            }elseif ($row["statut"]=='LOCATION') {
                $BCuLO=$row["U"];
                $BCcaLO=$row["CA"];
            }elseif ($row["statut"]=='BLOQUE') {
                $BCuB=$row["U"];
                $BCcaB=$row["CA"];
            }elseif($row["statut"]=='RESERVE'){
                $BCuR=$row["U"];
                $BCcaR=$row["CA"];
            }elseif ($row["statut"]=='SOLDE' or $row["statut"]=='SOLDE C21') {
                $BCuS=$BCuS+$row["U"];
                $BCcaS=$BCcaS+$row["CA"];
            }
        }


        $TU=$TCA=$uL=$caL=$uLO=$caLO=$uR=$caR=$uS=$caS=$uB=$caB=0;  
        foreach ($data as $row) {
            if($row["U_StatutBien"]=='0'){
                $uL=$row["U"];
                $caL=$row["CA"];
            }elseif ($row["U_StatutBien"]=='1') {
                $uLO=$row["U"];
                $caLO=$row["CA"];
            }elseif ($row["U_StatutBien"]=='8') {
                $uB=$row["U"];
                $caB=$row["CA"];
            }elseif($row["U_StatutBien"]=='2'){
                $uR=$row["U"];
            }elseif ($row["U_StatutBien"]=='6' or $row["U_StatutBien"]=='12') {
                $uS=$uS+$row["U"];
            }
        }
        foreach ($data2 as $row) {
            if($row["U_StatutBien"]=='2'){
                $caR=$row["CA"];
            }elseif ($row["U_StatutBien"]=='6' or $row["U_StatutBien"]=='12') {
                $caS=$caS+$row["CA"];
            }
        }
        $TU=$uL+$uR+$uS+$uB+$uLO;
        $TCA=($caL+$caR+$caS+$caB+$caLO)/1000000;
        $TCA = round($TCA, 2);

        $BCTU = $BCuL + $BCuR + $BCuS + $BCuB + $BCuLO;
        $BCTCA = ($BCcaL + $BCcaR + $BCcaS + $BCcaB + $BCcaLO) / 1000000;
        $BCTCA = round($BCTCA, 2);

        $html1.='<tr>
                    <td class="table-row-header">'.$typology.'</td> 
                    <td>'.checkType($type,$TU,$BCTU).'</td>
                    <td>'.checkType($type,$TCA,$BCTCA).'</td>
                    <td>'.$pm2.'</td>
                </tr>';
        $html2.='<tr>
                    <td class="table-row-header">'.$typology.'</td> 
                    <td>'.checkType($type,$uB,$BCuB).'</td>
                    <td>'.checkType($type,$uR,$BCuR).'</td>
                    <td>'.checkType($type,$uS,$BCuS).'</td>
                    <td>'.checkType($type,$uL,$BCuL).'</td>
                    <td><input class="input-stock-livre" type="text" name="livre-'.$typology.'" id="livre-'.$typology.'"></td>
                </tr>';    
        $TtU=$TtU+$TU;
        $TtCA=$TtCA+$TCA;
        $TuL=$TuL+$uL;
        $TuR=$TuR+$uR;
        $TuS=$TuS+$uS;
        $TuB=$TuB+$uB;
        $BCTtU = $BCTtU + $BCTU;
        $BCTtCA = $BCTtCA + $BCTCA;
        $BCTuL = $BCTuL + $BCuL;
        $BCTuR = $BCTuR + $BCuR;
        $BCTuS = $BCTuS + $BCuS;
        $BCTuB = $BCTuB + $BCuB;

        [$PPU, $PPCA,$PPUCUM,$PPCACUM,$CPU, $CPCA,$CPUCUM,$CPCACUM,$objU,$UObjN,$CAObjN,$enCaDate,$BCPPU, $BCPPCA, $BCPPUCUM, $BCPPCACUM, $BCCPU, $BCCPCA, $BCCPUCUM, $BCCPCACUM,$DesP,$DesC]= getCommPeriode($societe,$projet,$table,$typology,$current_from,$current_to,$previous_from,$previous_to);
        $precent=$TU==0?0:round(($PPUCUM/$TU)*100);
        $BCprecent=$BCTU==0?0:round(($BCPPUCUM/$BCTU)*100);

        $encPrecent=$CPCACUM==0?0:round(($enCaDate/$CPCACUM)*100);
        $BCencPrecent=$BCCPCACUM==0?0:round(($enCaDate/$BCCPCACUM)*100);

        $ecart=$CPU-$objU;
        $BCecart=$BCCPU-$objU;

        if($ecart>0 or $BCecart>0){
            $colorClass=' class="pos-value"';
        }elseif($ecart<0 or $BCecart<0){
            $colorClass=' class="neg-value"';
        }else{
            $colorClass='';
        }
        $html3.='<tr>
                    <td class="table-row-header">'.$typology.'</td> 
                    <td class="bleu-highlight">'.checkType($type,$PPU,$BCPPU).'</td>
                    <td class="bleu-highlight">'.$DesP.'</td>
                    <td class="bleu-highlight">'.checkType($type,$PPUCUM,$BCPPUCUM).'</td>
                    <td>'.checkType($type,$precent,$BCprecent).'%</td>
                    <td class="bleu-highlight">'.checkType($type,$PPCACUM,$BCPPCACUM).'</td>
                    <td class="bleu-highlight">'.checkType($type,$CPU,$BCCPU).'</td>
                    <td class="bleu-highlight">'.$DesC.'</td>
                    <td>'.checkType($type,$CPUCUM,$BCCPUCUM).'</td>
                    <td class="bleu-highlight">'.$objU.'</td>
                    <td class="bleu-highlight">'.checkType($type,$CPCA,$BCCPCA).'</td>
                    <td'.$colorClass.'>'.checkType($type,abs($ecart),abs($BCecart)).'</td>
                    <td>'.checkType($type,$CPCACUM,$BCCPCACUM).'</td>
                    <td class="bleu-highlight">'.$enCaDate.'</td>
                    <td>'.checkType($type,$encPrecent,$BCencPrecent).'%</td>
                    <td class="bleu-highlight">'.$UObjN.'</td>
                    <td>'.$CAObjN.'</td>
                </tr>'; 
        $TPPU=$TPPU+$PPU;
        $TPPUCUM=$TPPUCUM+$PPUCUM;
        $TPPCACUM=$TPPCACUM+$PPCACUM;

        $TCPU=$TCPU+$CPU;
        $TCPCA=$TCPCA+$CPCA;
        $TCPUCUM=$TCPUCUM+$CPUCUM;
        $TCCACUM=$TCCACUM+$CPCACUM;
        $TobjU=$TobjU+$objU;
        $TUObjN=$TUObjN+$UObjN;
        $TCAObjN=$TCAObjN+$CAObjN;
        $Tecart=$Tecart+$ecart;
        $TenCaDate=$TenCaDate+$enCaDate;
        //Totaux BC
        $BCTPPU=$BCTPPU+$BCPPU;
        $BCTPPUCUM=$BCTPPUCUM+$BCPPUCUM;
        $BCTPPCACUM=$BCTPPCACUM+$BCPPCACUM;

        $BCTCPU=$BCTCPU+$BCCPU;
        $BCTCPCA=$BCTCPCA+$BCCPCA;
        $BCTCPUCUM=$BCTCPUCUM+$BCCPUCUM;
        $BCTCCACUM=$BCTCCACUM+$BCCPCACUM;
        $TobjU=$TobjU+$objU;
        $TUObjN=$TUObjN+$UObjN;
        $TCAObjN=$TCAObjN+$CAObjN;
        $BCTecart=$BCTecart+$BCecart;
        

        $TDesP=$TDesP+$DesP;
        $TDesC=$TDesC+$DesC;
        if ($CPU!=0) {
            $labels[] = $typology;
            $qte[] = $CPU;
        }
        

        if ($objU != 0 || $CPU != 0) {
            $labelsG2[] = $typology;
            $objectif[] = $objU;
            $realise[] = $CPU;
        };
        
        if(round($CPCACUM-$enCaDate,2)!=0 ){
            $labelsG3[] = $typology;
            $resteaEnc[] = round($CPCACUM-$enCaDate,2);
        }
        
    }
    $TtCA = number_format($TtCA, 2); 
    $html1.='<tr class="table-total-row">
                <td>Total</td> 
                <td>'.checkType($type,$TtU,$BCTtU).'</td>
                <td>'.checkType($type,$TtCA,$BCTtCA).'</td>
                <td></td>
            </tr>';
    $html2.='<tr class="table-total-row">
                <td>Total</td> 
                <td>'.checkType($type,$TuB,$BCTuB).'</td>
                <td>'.checkType($type,$TuR,$BCTuR).'</td>
                <td>'.checkType($type,$TuS,$BCTuS).'</td>
                <td>'.checkType($type,$TuL,$BCTuL).'</td>
                <td><input type="text" name="stock-livre-total" id="stock-livre-total"></td>
            </tr>';   
    $Tpercent=round(($TPPUCUM/$TtU)*100);
    $BCTpercent=round(($BCTPPUCUM/$BCTtU)*100);

    $TEncPercent=round(($TenCaDate/$TCCACUM)*100);
    $BCTEncPercent=round(($TenCaDate/$BCTCCACUM)*100);
    if($Tecart>0 || $BCTecart>0){
        $colorClass=' class="pos-value"';
    }elseif($Tecart<0 || $BCTecart<0){
        $colorClass=' class="neg-value"';
    }else{
        $colorClass='';
    }
    $html3.='<tr class="table-total-row">
                <td>Total</td> 
                <td>'.checkType($type,$TPPU,$BCTPPU).'</td>
                <td>'.$TDesP.'</td>
                <td>'.checkType($type,$TPPUCUM,$BCTPPUCUM).'</td>
                <td>'.checkType($type,$Tpercent,$BCTpercent).'%</td>
                <td>'.checkType($type,$TPPCACUM,$BCTPPCACUM).'</td>
                <td>'.checkType($type,$TCPU,$BCTCPU).'</td>
                <td>'.$TDesC.'</td>
                <td>'.checkType($type,$TCPUCUM,$BCTCPUCUM).'</td>
                <td>'.$TobjU.'</td>
                <td>'.checkType($type,$TCPCA,$BCTCPCA).'</td>
                <td'.$colorClass.'>'.checkType($type,abs($Tecart),abs($BCTecart)).'</td>
                <td>'.checkType($type,$TCCACUM,$BCTCCACUM).'</td>
                <td>'.$TenCaDate.'</td>
                <td>'.checkType($type,$TEncPercent,$BCTEncPercent).'%</td>
                <td>'.$TUObjN.'</td>
                <td>'.$TCAObjN.'</td>
            </tr>'; 
    $graph1 = [
        "labels" => $labels,
        "qte" => $qte
    ];
    $graph2 = [
        "labels" => $labelsG2,
        "datasets" => [
            [
                "label" => "Objectif",
                "data" => $objectif,
                "backgroundColor" => "#547471"
            ],
            [
                "label" => "Réalisé",
                "data" => $realise,
                "backgroundColor" => "#A09D92"
            ]
        ]
    ];
    $graph3 = [
        "labels" => $labelsG3,
        "qte" => $resteaEnc
    ];
    return [$html1, $html2,$html3,$graph1,$graph2,$graph3];           
}

function getCommPeriode($societe,$projet,$table,$typology,$current_from,$current_to,$previous_from,$previous_to){
    //curren perdiode        
    $currentMonthFrom = date('m', strtotime($current_from));
    $currentYearFrom = date('Y', strtotime($current_from));
    $currentMonthTo = date('m', strtotime($current_to));
    $currentYearTo = date('Y', strtotime($current_to));
    //previous periode
    $previousMonthFrom = date('m', strtotime($previous_from));
    $previousYearFrom = date('Y', strtotime($previous_from));
    $previousMonthTo = date('m', strtotime($previous_to));
    $previousYearTo = date('Y', strtotime($previous_to));

    $NextpreviousMonthTo=$previousMonthTo+1;
    $NextcurrentMonthTo=$currentMonthTo+1;
    $NextpreviousYearTo=$previousYearTo;
    $NextcurrentYearTo=$currentYearTo;

    if($previousMonthTo==12){
        $NextpreviousMonthTo=1;
        $NextpreviousYearTo=$previousYearTo+1;
    }
    if($currentMonthTo==12){
        $NextcurrentMonthTo=1;
        $NextcurrentYearTo=$currentYearTo+1;
    }
    if($projet=='UP' and $typology=='Bureau'){
        $projet="UPBC";
    }
    //unité vendues période précédente
    $sqluvpp=' select "V_OITM"."TypeBien", count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" from "V_ORDR"  
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where "V_ORDR"."CANCELED"=\'N\' and "V_OITM"."U_Projet"=\''.$projet.'\' 
        and "DocDate"<(TO_DATE(\'1/'.$NextpreviousMonthTo.'/'.$NextpreviousYearTo.'\', \'DD/MM/YYYY\')) and "DocDate">=(TO_DATE(\'1/'.$previousMonthFrom.'/'.$previousYearFrom.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $sqldespp=' select "V_OITM"."TypeBien", count(*) as "Des" from "V_ORDR"  
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where  "V_OITM"."U_Projet"=\''.$projet.'\' 
        and "DateDesistement"<(TO_DATE(\'1/'.$NextpreviousMonthTo.'/'.$NextpreviousYearTo.'\', \'DD/MM/YYYY\')) and "DateDesistement">=(TO_DATE(\'1/'.$previousMonthFrom.'/'.$previousYearFrom.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $BCsqluvpp='SELECT typologie,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM '.$table.' 
            WHERE typologie="'.$typology.'" AND date_res<"'.$NextpreviousYearTo.'-'.$NextpreviousMonthTo.'-01" AND date_res>="'.$previousYearFrom.'-'.$previousMonthFrom.'-1"
            GROUP BY typologie';
    //unité vendues cumulées  précendent
    
    $sqluvpCum=' select "V_OITM"."TypeBien", count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" from "V_ORDR"  
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where "V_OITM"."U_Projet"=\''.$projet.'\'  and "V_ORDR"."CANCELED"=\'N\'and "DocDate"<(TO_DATE(\'1/'.$NextpreviousMonthTo.'/'.$NextpreviousYearTo.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $BCsqluvpCum='SELECT typologie,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM '.$table.' 
            WHERE typologie="'.$typology.'" AND date_res<"'.$NextpreviousYearTo.'-'.$NextpreviousMonthTo.'-01"
            GROUP BY typologie';
    
    //unité vendues période en cours
    $sqluvcp=' select "V_OITM"."TypeBien", count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" from "V_ORDR"  
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where "V_ORDR"."CANCELED"=\'N\' and "V_OITM"."U_Projet"=\''.$projet.'\' 
        and "DocDate"<(TO_DATE(\'1/'.$NextcurrentMonthTo.'/'.$NextcurrentYearTo.'\', \'DD/MM/YYYY\')) and "DocDate">=(TO_DATE(\'1/'.$currentMonthFrom.'/'.$currentYearFrom.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $sqldescp=' select "V_OITM"."TypeBien", count(*) as "Des" from "V_ORDR"   
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where "V_OITM"."U_Projet"=\''.$projet.'\' 
        and "DateDesistement"<(TO_DATE(\'1/'.$NextcurrentMonthTo.'/'.$NextcurrentYearTo.'\', \'DD/MM/YYYY\')) and "DateDesistement">=(TO_DATE(\'1/'.$currentMonthFrom.'/'.$currentYearFrom.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $BCsqluvcp='SELECT typologie,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM '.$table.' 
            WHERE typologie="'.$typology.'" AND date_res<"'.$NextcurrentYearTo.'-'.$NextcurrentMonthTo.'-1" AND date_res>="'.$currentYearFrom.'-'.$currentMonthFrom.'-1"
            GROUP BY typologie';
    //unité vendues cumulées en cours
    $sqluvcCum=' select "V_OITM"."TypeBien", count(*) as "U",TO_DECIMAL(sum("V_ORDR"."DocTotal"),18,2) as "CA" from "V_ORDR"  
        LEFT OUTER JOIN   "V_RDR1" ON "V_ORDR"."DocEntry"="V_RDR1"."DocEntry" and "V_ORDR"."Societe"=\''.$societe.'\'
        LEFT OUTER JOIN   "V_OITM" ON "V_RDR1"."ItemCode"="V_OITM"."ItemCode"  and  "TypeBien"=\''.$typology.'\'
        where "V_OITM"."U_Projet"=\''.$projet.'\'  and "V_ORDR"."CANCELED"=\'N\'and "DocDate"<(TO_DATE(\'1/'.$NextcurrentMonthTo.'/'.$NextcurrentYearTo.'\', \'DD/MM/YYYY\'))
        group by "V_OITM"."TypeBien"';
    $BCsqluvcCum='SELECT typologie,COUNT(*) AS "U",SUM(prix_vente) AS "CA" FROM '.$table.' 
            WHERE typologie="'.$typology.'" and date_res<"'.$NextcurrentYearTo.'-'.$NextcurrentMonthTo.'-1"
            GROUP BY typologie';

    $Up=$CAp=$UCUM=$CACUM=$Uc=$CAc=$UCUMc=$CACUMc=$DesP=$DesC=0;
    $BCUp=$BCCAp=$BCUCUM=$BCCACUM=$BCUc=$BCCAc=$BCUCUMc=$BCCACUMc=0;
    $data=Hana_Reporting($sqluvpp);
    foreach ($data as $row) {
        $Up=$row["U"];
        $CAp=round($row["CA"]/1000000,2);
    }
    $data2=Hana_Reporting($sqluvpCum);
    foreach ($data2 as $row) {
        $UCUM=$row["U"]; 
        $CACUM=round($row["CA"]/1000000,2);
    }
    $data3=Hana_Reporting($sqluvcp);
    foreach ($data3 as $row) {
        $Uc=$row["U"];
        $CAc=round($row["CA"]/1000000,2);
    }
    $data4=Hana_Reporting($sqluvcCum);
    foreach ($data4 as $row) {
        $UCUMc=$row["U"]; 
        $CACUMc=round($row["CA"]/1000000,2);
    }
    $data5=Hana_Reporting($sqldespp);
    foreach ($data5 as $row) {
        $DesP=$row["Des"]; 
    }
    $data6=Hana_Reporting($sqldescp);
    foreach ($data6 as $row) {
        $DesC=$row["Des"]; 
    }
    $BCdata=sqlBC($BCsqluvpp);
    foreach ($BCdata as $row) {
        $BCUp=$row["U"];
        $BCCAp=round($row["CA"]/1000000,2);
    }
    $BCdata2=sqlBC($BCsqluvpCum);
    foreach ($BCdata2 as $row) {
        $BCUCUM=$row["U"]; 
        $BCCACUM=round($row["CA"]/1000000,2);
    }
    $BCdata3=sqlBC($BCsqluvcp);
    foreach ($BCdata3 as $row) {
        $BCUc=$row["U"];
        $BCCAc=round($row["CA"]/1000000,2);
    }
    $BCdata4=sqlBC($BCsqluvcCum);
    foreach ($BCdata4 as $row) {
        $BCUCUMc=$row["U"]; 
        $BCCACUMc=round($row["CA"]/1000000,2);
    }

    //get Objectifs
    $monthsList=getMonthsBetween($current_from, $current_to);
    $objU=0;
    
    foreach ($monthsList as $month) {
        $sqlObj='select "vente_u" from "OBJECTIFS" where "projet"=\''.$projet.'\'  and "typologie"=\''.$typology.'\' and "mois"='.$month[0].' and "annee"='.$month[1];
        $execSql=Hana_Reporting($sqlObj);
        $dataObj=count($execSql)>0?$execSql[0]["vente_u"]:0;
        $objU=$objU+$dataObj;
    }
    $sqlObjN='select "vente_u","vente_ca" from "OBJECTIFS" where "projet"=\''.$projet.'\'  and "typologie"=\''.$typology.'\' and "mois"='.$NextcurrentMonthTo.' and "annee"='.$NextcurrentYearTo;
    $execSql=Hana_Reporting($sqlObjN);
    $UObjN=count($execSql)>0?$execSql[0]["vente_u"]:0;
    $CAObjN=count($execSql)>0?round($execSql[0]["vente_ca"]/1000000,2):0;
    if($projet=="UPBC"){
        $table="base_uptown";
    }
    if($table=="no_table"){
        $enCaDate=0;
    }else{
        $sqlEnc='SELECT typologie,SUM(avances_encaissees) AS "encaissement_a_date" FROM '.$table.'
            WHERE typologie="'.$typology.'" and date_res<"'.$NextcurrentYearTo.'-'.$NextcurrentMonthTo.'-1" GROUP BY typologie';
        $execSql=sqlBC($sqlEnc);
        $enCaDate=count($execSql)>0?round($execSql[0]["encaissement_a_date"]/1000000,2):0; 
    }
    return [$Up, $CAp,$UCUM,$CACUM,$Uc,$CAc,$UCUMc,$CACUMc,$objU,$UObjN,$CAObjN,$enCaDate,$BCUp,$BCCAp,$BCUCUM,$BCCACUM,$BCUc,$BCCAc,$BCUCUMc,$BCCACUMc,$DesP,$DesC];
}
function sqlBC($sql) {
    global $BCconn;

    $result = $BCconn->query($sql);
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
function getMonthsBetween($from, $to) {
    $start = new DateTime($from);
    $end = new DateTime($to);
    $end->modify('first day of next month');

    $months = [];
    while ($start < $end) {
        $months[] = [(int)$start->format('n'), (int)$start->format('Y')];
        $start->modify('+1 month');
    }
    return $months;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="stock") {
            $projet=$_GET["projet"];
            $type=$_GET["type"];
            [$programme, $stock,$situation,$graph1,$graph2,$graph3] = getCaSTock($projet,$type,$_GET['current_from'],$_GET['current_to'],$_GET['previous_from'],$_GET['previous_to']);
            $response = array("programme" => $programme,"stock" => $stock,"situation" => $situation,"graph1" => $graph1,"graph2" => $graph2,"graph3" => $graph3);
            echo json_encode($response);
        }
        if ($_GET["action"]=="situation") {
            $projet=$_GET["projet"];
            //[$data, $data2] = getCommPeriode("RMM_BUILDING",$projet,8,2025,8,2025,"Appartement");
            //$response = array("programme" => $data,"stock" => $data2);
           //echo json_encode($response);
        }
        exit();
    }
    
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<h1>Situation Commerciale</h1>
<div class="orverlay-reporting">
    <img src="./assets/spinner.gif" alt="spinner" class="spinner">
    <h2>Chargement des données en cours</h2>
</div>
<div class="search">
    <fieldset class="projects-box">
        <legend class="filter-type-title">Projet</legend>
        <div class="projects-list">
            <?= generateProjectList($projets);?>
        </div>
        <div class="date-from-to-container">
            <div class="fromto-container">
                <label for="dateFrom">Du</label>
                <input type="date" class="select-filter" id="comm-date-from" name="dateFrom"  onchange="handledSelectChange()">
                
            </div>
            <div class="fromto-container">
                <label for="dateTo">Au</label>
                <input type="date" class="select-filter" id="comm-date-to" name="dateTo"  onchange="handledSelectChange()">
            </div>

        </div>
        <div class="typeselector">
            <button class='btn-type-selector' id='btn-type-all' onclick='selecteType(this)'>All</button>
            <button class='btn-type-selector selectedType' id='btn-type-sap' onclick='selecteType(this)'>SAP</button>
            <button class='btn-type-selector' id='btn-type-bc' onclick='selecteType(this)'>BC</button>
        </div>
    </fieldset>
</div>
<h3 class="critere-search">*Filtres selectionnés : Projet <span id="selected-pro"> Aucun</span> Période : <span id="selected-date"> Aucune</span></h3>
<div class="first-row">
    <div id="table-left">
        <table id="table-programme">
            <thead>
                <tr><th rowspan="2"></th><th colspan="3">Programme</th></tr>
                <tr><th># d'unités</th><th>CA prévisionnel (MDH)</th><th>Prix au M² moyen</th></tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="table-right">
        <table id="table-stck">
            <thead>
                <tr><th colspan="6">Etat du stock</th></tr>
                <tr><th></th><th>Stock Bloqué</th><th>Stock Réservé</th><th>Stock Soldé</th><th>Sotck Disponible</th><th>Sotck Livré</th></tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="second-row">
    <table id="table-synth">
        <thead>
            <tr><th colspan="17">Suivi de la commercialisation</th></tr>
            <tr><th rowspan="2">Synthèse</th><th colspan="5" id="title_periode-1">Rapport P-1</th><th colspan="9" id="title_periode">Réalisation P</th><th colspan="2" class="title_periode1">Prévision P+1</th></tr>
            <tr><th class="title_periode-10">Unités vendues</th><th class="title_periode-10">Unités désistées du mois</th><th class="title_periode-1">Unités vendues cumulées</th>
            <th class="title_periode-1">en % du programme</th><th class="title_periode-1">CA cumulé</th>
                <th class="title_periode">Unités vendues</th><th class="title_periode">Unités désitées</th><th class="title_periode">Unités vendues cumulées</th>
                <th class="title_periode">Objectif en U</th><th class="title_periode">CA Réalisé (MDH)</th><th class="title_periode">Ecart VS Objectif (U)</th><th class="title_periode">CA Cumulé à date</th>
                <th class="title_encaisse">Encaissé à date</th><th class="title_encaisse">Taux d'encaissement</th>
                <th class="title_periode1">Objectif en U</th><th class="title_periode1">CA (MDH)</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<div class="third-row">
    <div id="graphe1"><canvas id="stockChart"></canvas></div>
    <div id="graphe2"><canvas id="objrealChart"></canvas></div>
    <div id="graphe3"><canvas id="resteChart"></canvas></div>
</div>
<script>
function selecteType(clickedBtn){
    const allButtons = document.querySelectorAll('.btn-type-selector');
    allButtons.forEach(btn => btn.classList.remove('selectedType'));
    clickedBtn.classList.add('selectedType');
    let projectBtn=document.querySelector('.selectedProject')
    let projet='';
    if(projectBtn){
        projet=projectBtn.id.split('-')[1]
    }else{
        alert("Merci de selectionner un projet");
        return 
    }
    getStock(projet)
}
function handledSelectChange(){
    let dateFrom=(document.getElementById('comm-date-from').valueAsDate.getMonth()+1) + "/"+ document.getElementById('comm-date-from').valueAsDate.getFullYear() 
    let dateTo=(document.getElementById('comm-date-to').valueAsDate.getMonth()+1) + "/"+ document.getElementById('comm-date-to').valueAsDate.getFullYear() 
    document.getElementById('selected-date').textContent=(dateFrom==dateTo)?dateFrom:dateFrom+" - "+dateTo
}
function selecteProject(clickedBtn) {
    const allButtons = document.querySelectorAll('.project-btn');
    allButtons.forEach(btn => btn.classList.remove('selectedProject'));
    clickedBtn.classList.add('selectedProject');
    let projet=clickedBtn.id.split("-")[1]
    document.getElementById('selected-pro').textContent=projet
    
}
function getStock(projet){
    const fromInput = document.getElementById('comm-date-from');
    const toInput = document.getElementById('comm-date-to');

    const dateFrom = fromInput.valueAsDate;
    const dateTo = toInput.valueAsDate;

    if (dateFrom && dateTo) {
    // Calculate difference in months
    const diffMonths =
        (dateTo.getFullYear() - dateFrom.getFullYear()) * 12 +
        (dateTo.getMonth() - dateFrom.getMonth());

    let current_from, current_to, previous_from, previous_to;

    if (diffMonths === 0) {
        current_from = new Date(dateFrom.getFullYear(), dateFrom.getMonth(), 1);
        current_to = new Date(dateFrom.getFullYear(), dateFrom.getMonth() + 1, 0);

        previous_from = new Date(dateFrom.getFullYear(), dateFrom.getMonth() - 1, 1);
        previous_to = new Date(dateFrom.getFullYear(), dateFrom.getMonth(), 0);
    } else {
        current_from = new Date(dateFrom);
        current_to = new Date(dateTo);

        previous_from = new Date(dateFrom);
        previous_to = new Date(dateTo);
        previous_from.setMonth(previous_from.getMonth() - (diffMonths + 1));
        previous_to.setMonth(previous_to.getMonth() - (diffMonths + 1));
    }

    const fmt = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    const currentFrom = fmt(current_from);
    const currentTo = fmt(current_to);
    const previousFrom = fmt(previous_from);
    const previousTo = fmt(previous_to);

    console.log({
        currentFrom,
        currentTo,
        previousFrom,
        previousTo
    });
    document.querySelector("#title_periode-1").innerHTML="Rapport : du "+previousFrom.split("-").reverse().join("/")+" au "+previousTo.split("-").reverse().join("/")
    document.querySelector("#title_periode").innerHTML="Réalisation : du "+currentFrom.split("-").reverse().join("/")+" au "+currentTo.split("-").reverse().join("/")
    document.querySelector(".orverlay-reporting").style.display="flex"
    let type=document.querySelector('.selectedType').textContent
    $.ajax({
        type: 'GET',
        url: './src/screens/avancement.php',
        dataType: 'json',
        headers: {
        'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
        action: "stock",
        projet: projet,
        type : type,
        current_from: currentFrom,
        current_to: currentTo,
        previous_from: previousFrom,
        previous_to: previousTo
        },
        success: function (response) {
        document.querySelector(".orverlay-reporting").style.display="none"
        document.querySelector("#table-programme tbody").innerHTML = response.programme;
        document.querySelector("#table-stck tbody").innerHTML = response.stock;
        document.querySelector("#table-synth tbody").innerHTML = response.situation;
        generateChart1(response.graph1)
        generateChart2(response.graph2)
        generateChart3(response.graph3)
        },
        error: function (xhr, status, error) {
        console.error('AJAX Error: ' + status + ' ' + error);
        document.querySelector(".orverlay-reporting").style.display="none"
        }
    });
    }

}
    let stockChartInstance = null;
    let objvsreaChartInstance = null;
    let resteencChartInstance = null;
function generateChart1(data){
        console.log(data)

        if (stockChartInstance) {
            stockChartInstance.destroy();
        }
        // let data={
        //     "labels":[ "Appartement", "Magasin", "Bureau"],
        //     "qte":[ "65", "19", "18" ]
        // }
        let labels=data.labels
        let values=data.qte
        let ctx = document.getElementById('stockChart').getContext('2d');
        const colorMap = {'Coliving': '#A09D92','Conventionne': '#547471','Appartement': '#547471','Magasin': '#A09D92','Bureau': '#90b1ad'}
        const barColors = labels.map(label => colorMap[label] || '#cccccc')
        // Create the new chart with the data labels and size control
        stockChartInstance = new Chart(ctx, {
            type: 'doughnut',  // Use 'doughnut' for a donut chart (similar to pie)
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: barColors
                }]
            },
            options: {
                plugins: {
                    datalabels: {
                        anchor: 'start',
                        align:'end',
                        color: '#000',
                        font: {weight: 'bold'},
                    },
                    title: {
                        display: true,
                        text: 'Unités vendues',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }  
                }
            },
            plugins:[ChartDataLabels]
        });
}


function generateChart2(data){
        console.log(data)

    if (objvsreaChartInstance) {
            objvsreaChartInstance.destroy();
        }
    // const labels=["Appartement","Bureau"]
    // const data = {
    //         labels: labels,
    //         datasets: [
    //             {
    //             label: 'Objectif',
    //             data: [5,6],
    //             backgroundColor:'#547471'
    //             },
    //             {
    //             label: 'Réalisé',
    //             data: [2,1],
    //             backgroundColor:'#A09D92'
    //             }
    //         ]
    //         };
        //const colorMap = {'Objectif': '#547471','Réalisé': '#A09D92'}
        //const barColors = labels.map(label => colorMap[label] || '#cccccc')
        let ctx = document.getElementById('objrealChart').getContext('2d');

        objvsreaChartInstance = new Chart(ctx, {
        type: "bar",
        data: data,
         options: {
            responsive: true,
            maintainAspectRatio: false, 
            plugins: {
                datalabels: {
                    anchor: 'start',
                    align:'end',
                    color: '#000',
                    font: {weight: 'bold'},
                } ,
                title: {
                    display: true,
                    text: 'Réalisé vs Budget',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                } 
            }
            
        },
        plugins:[ChartDataLabels]
        });
}
function generateChart3(data) {
    console.log(data)
    if (resteencChartInstance) {
        resteencChartInstance.destroy();
    }

    let labels = data.labels;
    let values = data.qte.map(Number);
    let ctx = document.getElementById('resteChart').getContext('2d');
    const colorMap = {'Coliving': '#A09D92','Conventionne': '#547471','Appartement': '#547471','Magasin': '#A09D92','Bureau': '#90b1ad'}
    const barColors = labels.map(label => colorMap[label] || '#cccccc');

    resteencChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: barColors
            }]
        },
        options: {
            animations: {
                radius: {
                    duration: 400,
                    easing: 'linear',
                    loop: (context) => context.active
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    color: '#fff',
                    backgroundColor:'#070707a6',
                    font: {
                        weight: 'bold',
                        size: 14
                    },
                    formatter: (value, ctx) => {
                        const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${value}\n(${percentage}%)`;
                    }
                },
                title: {
                    display: true,
                    text: 'Reste à encaisser',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

</script>
<script>
function getSituationData(){
    $.ajax({
        type: 'GET',
        url: './src/screens/avancement.php',
        dataType: 'json',
        headers: {
                'Content-Type': 'application/json; charset=UTF-8'
        },
        data: { 
            action:"situation",
            projet:"OP"
        },
        success: function (response) {
            console.log(response)
        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}
</script>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const today = new Date();

    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    document.getElementById('comm-date-from').value = formatDate(firstDay);
    document.getElementById('comm-date-to').value = formatDate(lastDay);
  });
  
</script>