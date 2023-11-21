

<?php

include "db_connection.php";
include "mvt_stock.php";
// Fontion pour mettre a jour le stock
function updateStockInDatabase($id, $currentStock, $addedStock)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $newStock = $currentStock+ $addedStock;
    $user="test";
    $sql = "UPDATE cartridges SET stock = '$newStock' WHERE id = '$id'";
   
    if ($conn->query($sql) === TRUE) {
        entreeStock($id,$user,$addedStock);
        return 'success';
    } else {
        return 'Error updating stock: ' . $conn->error;
    }
}

function getCartridgeById($id) {
    global $conn;

    // Sanitize input to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);

    // Perform the database query
    $sql = "SELECT * FROM cartridges WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the data as an associative array
        $cartridgeData = $result->fetch_assoc();
        return $cartridgeData;
    } else {
        return null;
    }
}

// POST check pour voir si on va lancer l'update car car ce fichier est inclu dans index.php, et vu que c'est un get, rien ne ce passe au niveau du php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST["id"];
    $currentStock = $_POST["currentStock"];
    $addedStock = $_POST["addedStock"];

    $updateResult = updateStockInDatabase($id, $currentStock, $addedStock);
    echo $updateResult;
    exit(); 
}
// Check if it's a GET request
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Ensure the 'id' parameter is set
    if (isset($_GET["id"])) {
        $id = $_GET["id"];

        // Perform treatment to get cartridge data
        $cartridgeData = getCartridgeById($id);

        // If data is found, convert it to JSON and echo it
        if ($cartridgeData !== null) {
            $result = json_encode($cartridgeData);
            echo $result;
        } else {
            // If no data is found, echo an error message
            echo "Cartridge not found.";
        }

        exit(); // Terminate further execution
    }
}
?>
<!-- html du modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>


        <label for="name">Nom :</label>
        <input type="text" name="name" id="name" class="modal-input" disabled>

        <label for="model">Modèle :</label>
        <input type="text" name="model" id="modalCartridgeName" class="modal-input" disabled>

        <label for="color">Couleur :</label>
        <input type="text" name="color" id="color" class="modal-input" disabled>

        <label for="stock">Stock:</label>
        <input type="number" name="stock" id="stock" class="modal-input" disabled>

        <label for="stock_min">Stock Sécurité :</label>
        <input type="number" name="stock_min" id="stock_min" class="modal-input" disabled>

        <label for="users">Utilisateur :</label>
        <input type="text" name="users" id="users" class="modal-input" disabled>

        <label for="newStock">Cartouche / toner à ajouter :</label>
        <input type="number" id="newStock" class="modal-input" min="0" value="0">

        <button onclick="updateStock()" class="update-button">Update Stock</button>


    </div>
</div>
<!-- Js pour gere son comportement -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    //afficher le modal
    // function openModal(cartridgeModel, currentStock) {
    //     document.getElementById('modalCartridgeName').innerText = cartridgeModel;
    //     document.getElementById('newStock').value = currentStock;
    //     document.getElementById('myModal').style.display = 'flex';
    // }

    function openModal(cartridgeId) {
        // Set the cartridge ID in the modal for later use
        document.getElementById('myModal').dataset.cartridgeId = cartridgeId;

        // Make a GET request to fetch cartridge data
        $.ajax({
            type: 'GET',
            url: 'modal.php', // Replace with the actual URL for your PHP script
            data: { id: cartridgeId },
            success: function(response) {
                // Parse the JSON response
                var cartridgeData = JSON.parse(response);

                // Update the modal content
                document.getElementById('modalCartridgeName').value = cartridgeData.model;
                document.getElementById('color').value = cartridgeData.color;
                document.getElementById('name').value = cartridgeData.name;
                document.getElementById('stock_min').value = cartridgeData.stock_min;
                document.getElementById('users').value = cartridgeData.users;
                document.getElementById('stock').value = cartridgeData.stock;

                // Show the modal
                document.getElementById('myModal').style.display = 'flex';
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }


    //fermer le modal
    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
        document.getElementById('newStock').value=0
    }
    // fontion ajax pour mettre a jour le stock dans la base de données

    function updateStock() {
        var id=document.getElementById('myModal').dataset.cartridgeId 
        var addedStock=parseInt(document.getElementById('newStock').value)

        if(!addedStock ){
            alert("Entrez un nomber s'il vous plait !")
            return
        }
        if (addedStock==0){
            alert("Ca ne sert à rien d'ajouter 0 au stock !")
            return
        }
        if (addedStock<0){
            alert("Helas ! C'est pas possible d'ajouter du stock négatif ! ")
            return
        }
        var currentStock=parseInt(document.getElementById('stock').value)
        // Perform the necessary actions to update the stock in the database
        // You need to implement the backend logic to update the stock
        $.ajax({
            type: 'POST',
            url: 'modal.php', // This assumes the modal.php file is in the same directory
            data: {
                id: id,
                addedStock:addedStock,
                currentStock: currentStock
            },
            success: function(response) {
                // Check the response from the server and handle accordingly
                if (response.trim() == 'success') {
                    // Update successful, close the modal
                    closeModal();
                    location.reload();
                } else {
                    // Handle error
                    alert('Error updating stock: ' + response);
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }

    // Close the modal if the user clicks outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('myModal');
        if (event.target === modal) {
            closeModal();
        }
    };
</script>
