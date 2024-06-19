<?php
// Configuration
$switchIps = ['192.168.10.10', '192.168.10.11','192.168.10.12', '192.168.10.13','192.168.10.14']; 
$community = 'public'; 
$oids = [
    'sysName' => '.1.3.6.1.2.1.1.5.0', // System name
    'sysDescr' => '.1.3.6.1.2.1.1.1.0', // System description (includes model)
    'ifName' => '.1.3.6.1.2.1.31.1.1.1.1', // Interface name
    'ifIndex' => '.1.3.6.1.2.1.2.2.1.1', // Interface index
    'dot1dTpFdbPort' => '.1.3.6.1.2.1.17.4.3.1.2', // Port associated with MAC
    'dot1dTpFdbAddress' => '.1.3.6.1.2.1.17.4.3.1.1' // MAC addresses
];


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["action"])) {
        if ($_GET["action"]=="etat") {
            foreach ($switchIps as $tab =>$switchIp) {
                // Fetch SNMP data
                $sysName = snmpget($switchIp, $community, $oids['sysName']);
                $ifName = snmpwalk($switchIp, $community, $oids['ifName']);
                $ifIndex = snmpwalk($switchIp, $community, $oids['ifIndex']);
                $dot1dTpFdbPort = snmpwalk($switchIp, $community, $oids['dot1dTpFdbPort']);
                $dot1dTpFdbAddress = snmpwalk($switchIp, $community, $oids['dot1dTpFdbAddress']);
            
                // Parse SNMP data
                $switchName = str_replace('STRING: ', '', $sysName);
            
                $ports = [];
                foreach ($ifName as $index => $name) {
                    $indexValue = str_replace('INTEGER: ', '', $ifIndex[$index]);
                    $ports[$indexValue] = str_replace('STRING: ', '', $name);
                }
            
                $macToPortMapping = [];
                foreach ($dot1dTpFdbAddress as $index => $mac) {
                    $mac = preg_replace('/\s+/', '', str_replace('Hex-STRING: ', '', $mac));
                    $portIndex = str_replace('INTEGER: ', '', $dot1dTpFdbPort[$index]);
                    if (!isset($macToPortMapping[$portIndex])) {
                        $macToPortMapping[$portIndex] = [];
                    }
                    $macToPortMapping[$portIndex][] = $mac;
                }
            
                // HTML Output for each switch
                echo "
                <h2>$switchName : $switchIp </h2>
                <table id='the-table-".$tab."'>
                    <tr>
                        <th>Port</th>
                        <th>MAC Addresses</th>
                    </tr>";
            
                foreach ($macToPortMapping as $portIndex => $macAddresses) {
                    $portName = isset($ports[$portIndex]) ? $ports[$portIndex] : $portIndex;
                    echo "<tr>
                            <td>$portName</td>
                            <td>";
                    foreach ($macAddresses as $mac) {
                        echo "$mac / ";
                    }
                    echo "</td></tr>";
                }
            
                echo "</table>";
            }
        }
        exit();
    }
        
    }  


?>


<h1>Ports / Switch</h1>
<div class="header-switch">
    <input type="text" id="mac-search" class="mac-search" placeholder="Addresse Mac" oninput="searchTables()">
    <button class="btn-switch" onclick="refreshSwitch()">Actualiser</button>

</div>


<div id="container-switch">
    <h2 id="switch-search-title" hidden>Collecting SNMP Data ... </h2>
    <img src="./assets/spinner.gif" alt="spinner" class="spinner" id="spinner" hidden>
    <div id="table-switch">

    </div>

</div>

<script>

function refreshSwitch(){
    document.getElementById("table-switch").innerHTML="";
    document.getElementById("container-switch").classList.add('centred-image');
    document.getElementById("spinner").hidden=false;
    document.getElementById("switch-search-title").hidden=false;

    $.ajax({
        type: 'GET',
        url: './src/screens/switchs.php',
        data: { 
            action:"etat",
        },
        success: function (response) {
            document.getElementById("spinner").hidden=true;
            document.getElementById("switch-search-title").hidden=true;
            document.getElementById("container-switch").classList.remove('centred-image');
            document.getElementById("table-switch").innerHTML=response;   
        },
        error: function (xhr, status, error) {

            console.error('AJAX Error: ' + status + ' ' + error);
        }
    });
    
}
function searchTables() {
    const searchInput = document.getElementById('mac-search').value.toUpperCase();
    const tables = document.querySelectorAll('#table-switch table');
    
    tables.forEach(table => {
        const rows = table.getElementsByTagName('tr');
        let tableHasMatch = false;

        for (let row of rows) {
            let rowHasMatch = false;
            const cells = row.getElementsByTagName('td');

            for (let cell of cells) {
                const cellText = cell.textContent.toUpperCase();
                if (cellText.includes(searchInput)) {
                    cell.innerHTML = highlightText(cellText, searchInput);
                    rowHasMatch = true;
                } else {
                    cell.innerHTML = escapeHtml(cellText);
                }
            }

            if (rowHasMatch) {
                row.style.display = '';
                tableHasMatch = true;
            } else {
                row.style.display = 'none';
            }
        }

        // Optionally hide the entire table if no matches are found
        if (!tableHasMatch) {
            table.style.display = 'none';
        } else {
            table.style.display = '';
        }
    });
}

function highlightText(text, search) {
    const regex = new RegExp(`(${search})`, 'gi');
    return text.replace(regex, `<span class="highlight">$1</span>`);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}


</script>        

