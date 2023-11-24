<?php
include "db_connection.php";

function getCartridgeByIds($ids) {
    global $conn;

    // Sanitize input to prevent SQL injection
    $ids = mysqli_real_escape_string($conn, $ids);
    
    // Perform the database query
    
    if($ids==""){
        return [];
    }

    $sql = "SELECT * FROM cartridges WHERE id in ( $ids )";
    
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $cartListe = [];

    while ($row = $result->fetch_assoc()) {
        $cartListe[] = $row;
    }
    return $cartListe;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["ids"])) {
        $ids = $_GET["ids"];
        $cartListeNoJson = getCartridgeByIds($ids);
        if ($cartListeNoJson !== null) {
            $cartListe = json_encode($cartListeNoJson);
            echo $cartListe;
        } else {
            echo "Cartridge not found.";
        }

        exit();
    }
     
}

?>


<div id="bsModal" class="modal">
    <div class="bs-modal-content">
        <span class="close" onclick="closeBSModal()">&times;</span>
        <div class="A4-format">
    <div class="container-header">
        <div class="h-logo">
            <img src="./assets/logo.png" alt="Logo" class="h-logo-png">
        </div>
        <div class="h-title">
            <h1>Bon de Sortie magasin</h1>
        </div>
        <div class="h-info">
            <p class="h-info-data">FR-11 / PS-GSI</p>
            <p class="h-info-data">Version: 1</p>
            <p class="h-info-data">Date: 09/02/2023</p>
        </div>
    </div>
    <div class="container-date">
        Date : <span class="h-date" id="current-date">12/07/2025</span>
    </div>
    <div class="container-data">
        <table class="bs-data-table">
            <thead>
                <tr class="bs-table-data-tr">
                    <th>Demandeur</th>
                    <th>Référence du toner</th>
                    <th>Référence imprimante</th>
                    <th>Nombre du Toner</th>
                </tr>
            </thead>
            <tbody id="table-body"></tbody>
        </table>
    </div>
    <div class="container-sign">
        <table class="bs-table-sign-table">
            <thead class="bs-table-sign-thead">
                <tr class="bs-table-sign-tr">
                    <th class="bs-table-sign-th">Signature Demandeur</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bs-table-sign-tr">
                    <td class="bs-table-sign-td"></td>
                </tr>
            </tbody>
        </table>
        <div class="lol"></div>
        <table class="bs-table-sign-table">
            <thead class="bs-table-sign-thead">
                <tr class="bs-table-sign-tr">
                    <th class="bs-table-sign-th">Signature IT</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bs-table-sign-tr">
                    <td class="bs-table-sign-td"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

        <button id="stock-button"  class="update-button">Valider et Imprimer</button>


    </div>
</div>


<script>
    function openBSModal(strIds) {
        let currentDate = new Date();
        let day = currentDate.getDate();
        let month = currentDate.getMonth() + 1; // Adding 1 because months are zero-based
        let year = currentDate.getFullYear();

        let date =  day+ "/" + month + "/" + year;
        document.getElementById('current-date').textContent = date;
        
        document.getElementById('bsModal').style.display = 'flex';
        $.ajax({
                type: 'GET',
                url: 'bon_sortie.php', // Replace with the actual URL for your PHP script
                data: { ids: strIds },
                success: function(response) {
                    // Parse the JSON response

                    var cartListe = JSON.parse(response);
                    console.log(cartListe)
                    var tableBody = document.getElementById("table-body");

                    if (tableBody.hasChildNodes()) {
                        while (tableBody.firstChild) {
                            tableBody.removeChild(tableBody.firstChild);
                        }
                    }

                    // Loop to create the rows
                    cartListe.forEach(function (row, index) {
                        var tr = document.createElement("tr");

                        // For the first row, create and append the "Demandeur" input
                        if (index === 0) {
                            var demandeurTd = document.createElement("td");
                            var demandeurInput = document.createElement("input");
                            demandeurInput.type = "text";
                            demandeurInput.placeholder = "Demandeur";
                            demandeurInput.className = "input-bs-table";
                            demandeurInput.id = "demandeur-bs-table"; // Use a consistent ID for the input
                            demandeurTd.setAttribute("rowspan", cartListe.length);
                            // demandeurInput.classList.add("center-vertically");
                            demandeurTd.appendChild(demandeurInput);
                            tr.appendChild(demandeurTd);
                        }

                        // Create and append other cells
                        var nameTd = document.createElement("td");
                        nameTd.textContent = row.name;
                        tr.appendChild(nameTd);

                        var colorTd = document.createElement("td");
                        colorTd.textContent = row.color;
                        tr.appendChild(colorTd);

                        var quantityTd = document.createElement("td");
                        var quantityInput = document.createElement("input");
                        quantityInput.type = "text";
                        quantityInput.placeholder = "max " + row.stock;
                        quantityInput.className = "input-bs-table";
                        quantityInput.id = "quantite-bs-table-" + index;
                        quantityInput.setAttribute("stock", row.stock);
                        quantityInput.setAttribute("stock_min", row.stock_min);
                        quantityTd.appendChild(quantityInput);
                        tr.appendChild(quantityTd);

                        tableBody.appendChild(tr);
                    });

                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });

    }
    function closeBSModal(){
        
        document.getElementById('bsModal').style.display = 'none';
    }
    // window.onclick = function(event) {
    //     var modalBS = document.getElementById('bsModal');
    //     if (event.target === modalBS) {
    //         closeBSModal();
    //     }
    // };
</script>