<?php
include "db_connection.php";

function getCartridgeByIds($ids) {
    global $conn;

    // Sanitize input to prevent SQL injection
    $ids = mysqli_real_escape_string($conn, $ids);

    // Perform the database query
    $sql = "SELECT * FROM cartridges WHERE id in('$ids')";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the data as an associative array
        $cartListe = $result->fetch_assoc();
        return $cartListe;
    } else {
        return null;
    }
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
        Date : <span class="h-date">12/07/2025</span>
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
            <tbody>
                <?php foreach ($cartListe as $index => $row): ?>
                    <tr class="bs-table-data-tr">
                        <td><input type="text" class="input-bs-table" id="input-bs-table-<?= $index ?>"> </td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['color'] ?></td>
                        <td><input type="text" placeholder="max 3" class="input-bs-table" id="quantite-bs-table-<?= $index ?>" stock="<?= $row['stock'] ?>" stock_min="<?= $row['stock_min'] ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
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
        document.getElementById('bsModal').style.display = 'flex';
        $.ajax({
                type: 'GET',
                url: 'bon_sortie.php', // Replace with the actual URL for your PHP script
                data: { ids: strIds },
                success: function(response) {
                    // Parse the JSON response
                    var cartListe = JSON.parse(response);
                    // Update the modal content
                    // document.getElementById('modalCartridgeName').value = cartridgeData.model;
                    // document.getElementById('color').value = cartridgeData.color;
                    // document.getElementById('name').value = cartridgeData.name;
                    // document.getElementById('stock_min').value = cartridgeData.stock_min;
                    // document.getElementById('users').value = cartridgeData.users;
                    // document.getElementById('stock').value = cartridgeData.stock;

                    // Show the modal
                    // document.getElementById('myModal').style.display = 'flex';
                    console.log(cartListe)
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
    window.onclick = function(event) {
        var modal = document.getElementById('bsModal');
        if (event.target === modal) {
            closeBSModal();
        }
    };
</script>