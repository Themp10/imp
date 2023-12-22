<?php


$servername = "localhost";
$username = "sa";
$password = "Thethepo06+";
$dbname = "PRINTERS";

// $servername = "172.28.0.9";
// $username = "glpi";
// $password = "MG+P@ssw0rd";
// $dbname = "PRINTERS";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
