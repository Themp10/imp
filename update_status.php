<?php
include "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cartridgeId = $_POST["cartridgeId"];
    $newStatus = $_POST["newStatus"];

    // Update the status in the database
    $sql = "UPDATE cartridges SET status = '$newStatus' WHERE id = $cartridgeId";

    if ($conn->query($sql) === TRUE) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $conn->error;
    }
}

$conn->close();
?>
