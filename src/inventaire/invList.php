<?php
// SQL connection
$servername = "172.28.0.22";
$username = "sa";
$password = "MG+P@ssw0rd";
$dbname = "INV";

$listconn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($listconn->connect_error) {
    die("Connection failed: " . $listconn->connect_error);
}

// SQL query
$sql = "SELECT CODE, utilisateur,designation, numero_serie, statut, etat FROM items WHERE TYPE='IT'";
$result = $listconn->query($sql);
?>

<h1>Liste des articles</h1>
<input type="text" id="searchInput" class="itemsearch" placeholder="Tapez pour filtrer..." oninput="filterTable()">

    <button class="btn-switch" type="button" onclick="getSelectedCodes()">Imprimer</button>
    <button class="btn-switch" type="button" onclick="genererDecharge()">Générer la décharge</button>

    <div id="qrRenderArea" style="display: none;"></div>

    <table class="mvt-table">
        <thead class="mvt-table-thead">
            <tr class="inv-table-tr">
                <th width="5%"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                <th>Code</th>
                <th>Utilisateur</th>
                <th>Désignation</th>
                <th>Numéro de Série</th>
                <th>Statut</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody class="mvt-table-tbody">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="mvt-table-tr" onclick="toggleRowCheckbox(this)">
                        <td><input type="checkbox" name="selected[]" value="<?= htmlspecialchars($row['CODE']) ?>" onclick="event.stopPropagation();"></td>
                        <td><?= htmlspecialchars($row['CODE']) ?></td>
                        <td><?= htmlspecialchars($row['utilisateur']) ?></td>
                        <td><?= htmlspecialchars($row['designation']) ?></td>
                        <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                        <td><?= htmlspecialchars($row['statut']) ?></td>
                        <td><?= htmlspecialchars($row['etat']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Aucun item trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>

<script>

async function getSelectedCodes() {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    const selectedCodes = Array.from(checkboxes).map(cb => cb.value);

    const qrContainer = document.getElementById('qrRenderArea');
    qrContainer.innerHTML = ""; // Clear previous

    for (const code of selectedCodes) {
        const div = document.createElement("div");
        div.id = code;
        qrContainer.appendChild(div);

        const qrCode = new QRCodeStyling({
            width: 150,
            height: 150,
            data: "http://172.28.0.22/imp/item/" + code,
            image: "./assets/MG-logoSM.png", 
            dotsOptions: {
                color: "#000",
                type: "rounded"
            },
            backgroundOptions: {
                color: "#fff"
            },
            imageOptions: {
                crossOrigin: "anonymous",
                margin: 1
            }
        });

        await qrCode.append(div); // proper rendering
    }

    // Wait a short delay to allow all canvases to render
    await new Promise(resolve => setTimeout(resolve, 200));

    printQrCodes();
}
function printQrCodes(){
    let html="";
    let qrs=document.getElementById("qrRenderArea").children
    for (const container of qrs) {
        const canvas = container.querySelector("canvas");
        const dataUrl = canvas.toDataURL("image/png");
        const qrText=container.id
        html += `
            <div class="oneqr">
                <img src="${dataUrl}" alt="QR Code" />
                <div class="oneqrtxt">
                    <div>${qrText}</div>
                    <center><div class="gmtaille">Groupe Mfadel</div></center>
                </div>
            </div>`;
    }

    const printWindow = window.open('', '', 'width=300,height=400');
    printWindow.document.write(`
        <html>
        <head>
            <style>
            @media print {
                body {
                margin: 0;
                padding: 0;
                }
            }
            body {

                font-size: 32px;
                font-family: monospace;
                margin: 0;
                padding: 0;
            }
            img {
                width: 150px;
                height: 150px;
                display: block;
                margin-bottom: 4px;
            }
            .oneqr{
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                page-break-after: always;
            }
            .oneqrtxt{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .gmtaille{
                font-size: 20px;    
            }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${html}
        </body>
        </html>
    `);
    printWindow.document.close();
}
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('input[name="selected[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function toggleRowCheckbox(row) {
    const checkbox = row.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
}


function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const designationCell = row.cells[3]; // 3rd column: "Désignation"
        const itemRow = row.innerText.toLowerCase();
        // if (!designationCell) {
        //     row.style.display = 'none'; // hide malformed or empty rows
        //     return;
        // }

        //const designationText = designationCell.textContent.toLowerCase();
        row.style.display = itemRow.includes(input) ? '' : 'none';
    });
}

async function genererDecharge(){
    const checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    const selectedCodes = Array.from(checkboxes).map(cb => cb.value);
    let x = document.getElementById("snackbar");

    const payload = JSON.stringify({
            key: "dechargeList",
            data: selectedCodes
        });
        await fetch('./src/inventaire/items.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: payload
        })
        .then(response => response.json())
        .then(data => {
            if (data.status="success"){
                x.innerHTML="Décharge(s) généré(s) avec succés!"
                x.className = "show success-message";
                setTimeout(function(){ 
                    x.className = x.className.replace("show success-message", ""); 
                }, 3000);
            }else{
                x.innerHTML="Problème survenu, Merci de contacter votre administrateur!"
                x.className = "show error-message";
                setTimeout(function(){ 
                    x.className = x.className.replace("show error-message", ""); 
                }, 3000);
            }
            document.getElementsByClassName("item-form")[1].reset()

        })
        .catch(error => {
            console.error('Error sending data:', error);
        });
}
</script>


<?php $listconn->close(); ?>
