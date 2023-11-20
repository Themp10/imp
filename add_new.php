<?php
include "db_connection.php";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $option = $_POST["option"];

    if ($option === "new") {
        // Handle new cartridge insertion
        $name = $_POST["name"];
        $model = $_POST["model"];
        $color = $_POST["color"];
        $quantity = $_POST["quantity"];
        $stock_min = $_POST["stock_min"];
        $users = $_POST["users"];

        // Perform database insertion or validation as needed
        // Example: Insert the new cartridge into the database
        $insertQuery = "INSERT INTO cartridges (name, model, color, stock, stock_min, users) VALUES ('$name', '$model', '$color', $quantity, $stock_min, '$users')";
        $conn->query($insertQuery);

    } elseif ($option === "existing") {
        // Handle updating quantity for an existing cartridge
        $model = $_POST["existing_model"];
        $quantity = $_POST["existing_quantity"];

        // Perform database update or validation as needed
        // Example: Update the quantity for the existing cartridge
        $updateQuery = "UPDATE cartridges SET stock = $quantity WHERE model = '$model'";
        $conn->query($updateQuery);
    }
}
?>

<h2>Add Cartridge</h2>
    <form action="" method="post">
        <label for="option">Choose an option:</label>
        <select name="option" id="option">
            <option value="new">New Cartridge</option>
            <option value="existing">Existing Cartridge</option>
        </select>
        <br>

        <!-- New Cartridge Fields -->
        <div id="newCartridgeFields">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required>
            <br>
            <label for="model">Model:</label>
            <input type="text" name="model" id="model" required>
            <br>
            <label for="color">Color:</label>
            <input type="text" name="color" id="color" required>
            <br>
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" required>
            <br>
            <label for="stock_min">Stock Min:</label>
            <input type="number" name="stock_min" id="stock_min" required>
            <br>
            <label for="users">Users:</label>
            <input type="text" name="users" id="users" required>
            <br>
        </div>

        <!-- Existing Cartridge Fields -->
        <div id="existingCartridgeFields" style="display:none;">
            <label for="existing_model">Model:</label>
            <input type="text" name="existing_model" id="existing_model">
            <br>
            <label for="existing_quantity">Quantity:</label>
            <input type="number" name="existing_quantity" id="existing_quantity">
            <br>
        </div>

        <button type="submit">Submit</button>
    </form>

    <script>
        // Toggle visibility of new/existing cartridge fields based on the selected option
        document.getElementById('option').addEventListener('change', function() {
            var newFields = document.getElementById('newCartridgeFields');
            var existingFields = document.getElementById('existingCartridgeFields');

            if (this.value === 'new') {
                newFields.style.display = 'block';
                existingFields.style.display = 'none';
            } else {
                newFields.style.display = 'none';
                existingFields.style.display = 'block';
            }
        });
    </script>