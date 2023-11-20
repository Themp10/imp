<!-- modal.php -->

<?php

// Assuming you have a database connection function in your database file
include "db_connection.php";

// Function to update the stock in the database
function updateStockInDatabase($model, $newStock)
{
    global $conn;

    // Sanitize input to prevent SQL injection
    $model = mysqli_real_escape_string($conn, $model);
    $newStock = mysqli_real_escape_string($conn, $newStock);

    // Perform the database update
    $sql = "UPDATE cartridges SET stock = '$newStock' WHERE id = '$model'";

    if ($conn->query($sql) === TRUE) {
        return 'success';
    } else {
        return 'Error updating stock: ' . $conn->error;
    }
}

// Check if the form is submitted for updating the stock
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = $_POST["model"];
    $newStock = $_POST["newStock"];

    $updateResult = updateStockInDatabase($model, $newStock);
    echo $updateResult;
    exit(); // Terminate further execution after the update
}

?>

<style>
    /* Add this CSS to your existing stylesheet or create a new stylesheet */

   
</style>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalCartridgeName"></h2>
        <label for="newStock">New Stock:</label>
        <input type="number" id="newStock" class="new-stock-input" min="0" value="0">
        <button onclick="updateStock()" class="update-button">Update Stock</button>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    function openModal(cartridgeModel, currentStock) {
        document.getElementById('modalCartridgeName').innerText = cartridgeModel;
        document.getElementById('newStock').value = currentStock;
        document.getElementById('myModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
    }

    function updateStock() {
        var cartridgeModel = document.getElementById('modalCartridgeName').innerText;
        var newStock = document.getElementById('newStock').value;

        // Perform the necessary actions to update the stock in the database
        // You need to implement the backend logic to update the stock
        $.ajax({
            type: 'POST',
            url: 'modal.php', // This assumes the modal.php file is in the same directory
            data: {
                model: cartridgeModel,
                newStock: newStock
            },
            success: function(response) {
                // Check the response from the server and handle accordingly
                if (response === 'success') {
                    // Update successful, close the modal
                    closeModal();
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
