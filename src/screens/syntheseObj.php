<?php


function sql_from_Hana_Synthese($sql) {
    $dsn = "HANA";
    $username = "SYSTEM";
    $password = "Skatys2020";
    $Hanaconn = odbc_connect($dsn, $username, $password);

    $data = [];
    $setDb = odbc_exec($Hanaconn, "SET SCHEMA SYSTEM");
    $result = odbc_exec($Hanaconn, $sql);
    if (!$result) {
        echo "Error while sending SQL statement to the database server.\n";
        echo "ODBC error code: " . odbc_error() . ". Message: " . odbc_errormsg();
    } else {
        while ($row = odbc_fetch_array($result)) {
            $encoded_row = [];
            foreach ($row as $key => $value) {
                $encoded_row[utf8_encode($key)] = utf8_encode($value);
            }
            $data[] = $encoded_row;
        }
    }

    odbc_close($Hanaconn);
    return $data;
}
function getprojet(){
    $sql = '
    SELECT DISTINCT "projet"
    FROM "OBJECTIFS"';
    $data = sql_from_Hana_Synthese($sql);
    $projet = [];
    foreach ($data as $row) {
        $projet[] = $row['projet'];
    }
    return $projet;
}
function getSql($projet){
    $sql = '
    SELECT 
        \'Vente CA\' AS type, SUM("vente_ca") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    WHERE "projet" = \''.$projet.'\'
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Vente U\' AS type, SUM("vente_u") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    WHERE "projet" = \''.$projet.'\'
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Encaissement\' AS type, SUM("encaissement") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    WHERE "projet" = \''.$projet.'\'
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Recouvrement\' AS type, SUM("recouvrement") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    WHERE "projet" = \''.$projet.'\'
    GROUP BY "mois";
    ';
    return $sql;
}
function synthese() {
    $sql = '
    SELECT 
        \'Vente CA\' AS type, SUM("vente_ca") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Vente U\' AS type, SUM("vente_u") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Encaissement\' AS type, SUM("encaissement") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    GROUP BY "mois"
    UNION ALL
    SELECT 
        \'Recouvrement\' AS type, SUM("recouvrement") AS total, "mois"
    FROM "SYSTEM"."OBJECTIFS"
    GROUP BY "mois";
    ';

    $data = sql_from_Hana_Synthese($sql);

    $months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    
    $formattedData = [
        'Vente U' => array_fill(0, 12, 0),
        'Vente CA' => array_fill(0, 12, 0),
        'Encaissement' => array_fill(0, 12, 0),
        'Recouvrement' => array_fill(0, 12, 0)
    ];

    foreach ($data as $row) {
        $type = $row['TYPE'];
        $mois = (int) $row['mois'] - 1;
        $formattedData[$type][$mois] = $row['TOTAL'];
    }
    echo "<div class='synth-page'>";
    echo "<div class='synth-header'>Synthèse des objectifs</div>";

    echo "<table class='table-synthese' border='1' cellpadding='5' cellspacing='0'>";
    echo "<thead><tr>";
    echo "<th colspan='2' width='16%'>Projet</th>";
    foreach ($months as $month) {
        echo "<th width='7%'>$month</th>";
    }
    echo "</tr></thead>";
    echo "<tbody>";

    $k=0;
    foreach ($formattedData as $type => $values) {
        echo "<tr>";
        if($k == 0){
            echo "<td rowspan='4' width='8%'><strong>Groupe Mfadel</strong></td>";
        }
        echo "<td><strong>$type</strong></td>";
        foreach ($values as $value) {
            echo "<td>" . $value . "</td>";
        }
        echo "</tr>";
        $k++;
    }
    echo '<tr class="empty-row"><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
    $projets=["SH","OP", "KPC"];
    foreach($projets as $projet){
        $k=0;
        $sql=getSql($projet);
        $data = sql_from_Hana_Synthese($sql);
        $formattedData = [
            'Vente U' => array_fill(0, 12, 0),
            'Vente CA' => array_fill(0, 12, 0),
            'Encaissement' => array_fill(0, 12, 0),
            'Recouvrement' => array_fill(0, 12, 0)
        ];
        foreach ($data as $row) {
            $type = $row['TYPE'];
            $mois = (int) $row['mois'] - 1;
            $formattedData[$type][$mois] = $row['TOTAL'];
        }
        
        foreach ($formattedData as $type => $values) {
            echo "<tr>";
            if($k == 0){
                echo "<td rowspan='4'><strong>$projet</strong></td>";
            }
            echo "<td><strong>$type</strong></td>";
            foreach ($values as $value) {
                echo "<td>" .$value . "</td>";
            }
            echo "</tr>";
            $k++;
        }
    }
    

    echo "</tbody>";
    echo "</table>";
    echo "</div>";



    echo "<div class='synth-page'>";
    echo "<table class='table-synthese' border='1' cellpadding='5' cellspacing='0'>";
    echo "<thead><tr>";
    echo "<th colspan='2' width='16%'>Projet</th>";
    foreach ($months as $month) {
        echo "<th width='7%'>$month</th>";
    }
    echo "</tr></thead>";
    echo "<tbody>";

    $projets=["CP","ZT","MNO","MT"];

    foreach($projets as $projet){
        $k=0;
        $sql=getSql($projet);
        $data = sql_from_Hana_Synthese($sql);
        $formattedData = [
            'Vente U' => array_fill(0, 12, 0),
            'Vente CA' => array_fill(0, 12, 0),
            'Encaissement' => array_fill(0, 12, 0),
            'Recouvrement' => array_fill(0, 12, 0)
        ];
        foreach ($data as $row) {
            $type = $row['TYPE'];
            $mois = (int) $row['mois'] - 1;
            $formattedData[$type][$mois] = $row['TOTAL'];
        }
        
        foreach ($formattedData as $type => $values) {
            echo "<tr>";
            if($k == 0){
                echo "<td rowspan='4' width='8%'><strong>$projet</strong></td>";
            }
            echo "<td><strong>$type</strong></td>";
            foreach ($values as $value) {
                echo "<td>" .$value . "</td>";
            }
            echo "</tr>";
            $k++;
        }
    }
    

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    echo "<div class='synth-page'>";
    echo "<table class='table-synthese' border='1' cellpadding='5' cellspacing='0'>";
    echo "<thead><tr>";
    echo "<th colspan='2' width='16%'>Projet</th>";
    foreach ($months as $month) {
        echo "<th width='7%'>$month</th>";
    }
    echo "</tr></thead>";
    echo "<tbody>";

    $projets=["WL", "BA", "UP", "UPBC"];

    foreach($projets as $projet){
        $k=0;
        $sql=getSql($projet);
        $data = sql_from_Hana_Synthese($sql);
        $formattedData = [
            'Vente U' => array_fill(0, 12, 0),
            'Vente CA' => array_fill(0, 12, 0),
            'Encaissement' => array_fill(0, 12, 0),
            'Recouvrement' => array_fill(0, 12, 0)
        ];
        foreach ($data as $row) {
            $type = $row['TYPE'];
            $mois = (int) $row['mois'] - 1;
            $formattedData[$type][$mois] = $row['TOTAL'];
        }
        
        foreach ($formattedData as $type => $values) {
            echo "<tr>";
            if($k == 0){
                echo "<td rowspan='4' width='8%'><strong>$projet</strong></td>";
            }
            echo "<td><strong>$type</strong></td>";
            foreach ($values as $value) {
                echo "<td>" .$value . "</td>";
            }
            echo "</tr>";
            $k++;
        }
    }
    

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
}
?>

<h1>Synthèse Totale</h1>
<button id="printButton" class="btn-switch large-btn">Imprimer</button>
<?php synthese(); ?>


<script>
    document.querySelectorAll(".table-synthese td").forEach(td => {
        const content = parseFloat(td.innerText.replace(/\s/g, ""));
        if (!isNaN(content)) {
            const formattedNumber = Number.isInteger(content)
                ? content.toLocaleString('fr-FR')
                : content.toFixed(2).toLocaleString('fr-FR');
            td.innerText = formattedNumber;
        }
    });

    document.getElementById('printButton').addEventListener('click', function () {
    const pages = document.querySelectorAll('.synth-page');
    let printContent = '';
    let k=1;
    let x=pages.length;
    pages.forEach(page => {
        printContent += '<p>Page '+k+'/'+x+'</p>';
        printContent += '<img src="./assets/MG-logo.png" alt="Logo" width="200">';
        printContent += page.outerHTML + '<div style="page-break-after: always;"></div>';
        k+=1;
        
    });

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
        <head>
          <title>Print Preview</title>
          <style>
    @page { margin: 0; }
            body { font-family: Arial, sans-serif;print-color-adjust: exact;}
            .table-synthese { width: 100%; border: 1;margin-top: 20px }
            .synth-page { padding: 20px; }
            .table-synthese tbody tr td{
                font-size: 12px;
            }
            .table-synthese thead tr th{
                background-color: #547471;
                color: white;
            }
            .synth-header{
                background-color: #547471;
                text-align: center;
                color: white;
                font-size: 20px;
                padding: 20px;
                margin-bottom: 50px;
            }
            .empty-row{
                    print-color-adjust: exact;
                background-color: var(--primary-color);
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
});
</script>




