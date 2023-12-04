<?php
include "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $model = $_POST["model"];
    $color = $_POST["color"];
    $quantity = $_POST["quantity"];
    $isNewCartridge = $_POST["isNewCartridge"];

    if ($isNewCartridge == "new") {
        // Add a new cartridge to the database
        $sql = "INSERT INTO cartridges (model, color, quantity, status) VALUES ('$model', '$color', $quantity, 'In Stock')";

        if ($conn->query($sql) === TRUE) {
            echo "New cartridge added successfully";
        } else {
            echo "Error adding new cartridge: " . $conn->error;
        }
        header("Location: index.php");
        exit();
    } else {
        // Increment the quantity of an existing cartridge
        $existingCartridgeId = $_POST["existingCartridgeId"];

        $sql = "UPDATE cartridges SET quantity = quantity + $quantity WHERE id = $existingCartridgeId";

        if ($conn->query($sql) === TRUE) {
            echo "Cartridge quantity incremented successfully";
        } else {
            echo "Error incrementing cartridge quantity: " . $conn->error;
        }
        header("Location: index.php");
        exit();
    }
}

$conn->close();
?>
