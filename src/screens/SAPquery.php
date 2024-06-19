<?php
function sql_from_Hana_query($sql,$base){
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);
 
    $data=[];
    $setCharset = odbc_exec($Hanaconn, "SET NAMES UTF8");
    $setCharset = odbc_exec($Hanaconn, "SET CHARACTER SET UTF8");
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
function generateSqlTable($sql,$baseList){
    $html="";
    $bases = explode(";", $baseList);
    
    // Loop through the array (table)
    foreach ($bases as $base) {
        $html+=sql_from_Hana_query($sql,$base);
    }
    return $html;
}

function createHtmlTableFromSqlResult($sql, $baseList) {

    $html="";
    $csv="";
    $bases = explode(";", $baseList);
    $headerProcessed = false;
    $html = '<table border="1">';
    $resultArray=[];
    foreach ($bases as $base) {
        $resultArray = sql_from_Hana_query($sql, $base);
        
        if (!empty($resultArray)) {
            foreach ($resultArray as $rowIndex => $row) {
                if (!$headerProcessed) {
                    $html .= '<tr><th>Base</th>';
                    $csv .="base|";
                    foreach ($row as $key => $value) {
                        $csv .=$key."|";
                        $html .= '<th>' . htmlspecialchars($key) . '</th>';
                    }
                    $csv .="\n";
                    $html .= '</tr>';
                    $headerProcessed = true;
                }
                $html .= '<tr><td>' . htmlspecialchars($base) . '</td>'; 
                $csv .=$base."|";
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                    $csv .=str_replace(array("\r", "\n"), '', htmlspecialchars($value))."|";
                }
                $csv .="\n";
                $html .= '</tr>';
            }
            
        } else {
            $html = "No data found";
        }
    }
    $html .= '</table>';
    $filePath = "../../outputs/output.txt";
    file_put_contents($filePath, $csv);
    return $html;
}





if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["sql"])) {
        $resultTable = createHtmlTableFromSqlResult($_GET["sql"],$_GET["baseList"]); 
        echo $resultTable; 
        exit();
    }
        
}

?>
<div class="sql-container">
    <div class="base-group">
        <label for="AM_PROINVEST"><input type="checkbox" id="AM_PROINVEST" name="AM_PROINVEST" value="AM_PROINVEST"> AM_PROINVEST</label>
        <label for="AM_PROINVEST_TEST"><input type="checkbox" id="AM_PROINVEST_TEST" name="AM_PROINVEST_TEST" value="AM_PROINVEST_TEST"> AM_PROINVEST_TEST</label>
        <label for="ANFA_19"><input type="checkbox" id="ANFA_19" name="ANFA_19" value="ANFA_19"> ANFA_19</label>
        <label for="ANFA_REALISATION"><input type="checkbox" id="ANFA_REALISATION" name="ANFA_REALISATION" value="ANFA_REALISATION"> ANFA_REALISATION</label>
        <label for="CASA_COLIVING"><input type="checkbox" id="CASA_COLIVING" name="CASA_COLIVING" value="CASA_COLIVING"> CASA_COLIVING</label>
        <label for="M_PROPERTIES"><input type="checkbox" id="M_PROPERTIES" name="M_PROPERTIES" value="M_PROPERTIES"> M_PROPERTIES</label>
        <label for="PROBAT_INVEST"><input type="checkbox" id="PROBAT_INVEST" name="PROBAT_INVEST" value="PROBAT_INVEST"> PROBAT_INVEST</label>
        <label for="RMM_BUILDING"><input type="checkbox" id="RMM_BUILDING" name="RMM_BUILDING" value="RMM_BUILDING"> RMM_BUILDING</label>
        <label for="YASMINE_FONCIERE"><input type="checkbox" id="YASMINE_FONCIERE" name="YASMINE_FONCIERE" value="YASMINE_FONCIERE"> YASMINE_FONCIERE</label>
    </div>
    <textarea name="sql-query" id="sql-query" cols="30" rows="10"></textarea>
    <button class="sql-btn">Executer</button>
    <div id="sql-result">

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let button = document.querySelector('.sql-btn');
        button.addEventListener('click', function() {
            execsql();
        });
});




function execsql(){
    let baseList=[]
    let bases="";
    let listSoc = document.querySelectorAll('.base-group label input');
    let sql=document.getElementById('sql-query').value
    listSoc.forEach(function(element) {
        if(element.checked){
            baseList.push(element.getAttribute('value'))
        }
    }); 
    if (baseList.length==0){
        return alert("Merci de choisir une base")
    }else{
        bases=baseList.join(";")
    }
    $.ajax({
    type: 'GET',
        url: './src/screens/SAPquery.php', 
        data: { 
                baseList: bases,
                sql:sql
            },
        success: function(response) {
            //let data = JSON.parse(response);
            console.log(response)
            document.getElementById("sql-result").innerHTML=response

        },
        error: function(xhr, status, error) {
            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
}


</script>
        
        