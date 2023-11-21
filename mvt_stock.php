<?php


// Function for "entree stock" (stock in)
function entreeStock($cartridgeId, $user, $quantity) {
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);
    $user = mysqli_real_escape_string($conn, $user);
    $quantity = mysqli_real_escape_string($conn, $quantity);
    $currentStock=getCurrentStock($cartridgeId);
    $type = 'e';//e pour entrée de stock
    $mvtDate = date("Y-m-d");

    // database insertion
    $sql = "INSERT INTO mouvements (id_cartridge, user, qte,stock_apres, type, mvt_date) 
            VALUES ('$cartridgeId', '$user', '$quantity','$currentStock', '$type', '$mvtDate')";

    if ($conn->query($sql) === TRUE) {
        return "Stock in recorded successfully.";
    } else {
        return "Error recording stock in: " . $conn->error;
    }
}

// Function for "sortie stock" (stock out)
function sortieStock($cartridgeId, $user, $quantity) {
    global $conn;

    // Sanitize inputs to prevent SQL injection
    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);
    $user = mysqli_real_escape_string($conn, $user);
    $quantity = mysqli_real_escape_string($conn, $quantity);

    // Set the transaction type to 'out'
    $type = 's';

    // Get the current date
    $mvtDate = date("Y-m-d");

    // Check if there is enough stock for the requested quantity
    $currentStock = getCurrentStock($cartridgeId);

    if ($currentStock >= $quantity) {
        // Perform the database insertion
        $sql = "INSERT INTO mouvements (id_cartridge, user, qte, type, mvt_date) 
                VALUES ('$cartridgeId', '$user', '$quantity', '$type', '$mvtDate')";

        if ($conn->query($sql) === TRUE) {
            return "Stock out recorded successfully.";
        } else {
            return "Error recording stock out: " . $conn->error;
        }
    } else {
        return "Insufficient stock for stock out operation.";
    }
}

// Function to get the current stock for a specific cartridge
function getCurrentStock($cartridgeId) {
    global $conn;

    // Sanitize input to prevent SQL injection
    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);

    // Perform the database query to get the current stock from the 'cartridge' table
    $stockQuery = "SELECT stock FROM cartridges WHERE id = '$cartridgeId'";
    $stockResult = $conn->query($stockQuery);

    if ($stockResult && $stockResult->num_rows > 0) {
        $stockData = $stockResult->fetch_assoc();
        return $stockData['stock'];
    } else {
        return "Error fetching current stock from 'cartridge' table: " . $conn->error;
    }
}
?>
