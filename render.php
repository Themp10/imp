<?php
include "db_connection.php";

function get_printers_list(){
    global $conn; 
    $sql = "SELECT DISTINCT users FROM cartridges";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $printers = [];

    while ($row = $result->fetch_assoc()) {
        $printers[] = $row['users'];
    }

    return $printers;
}

function get_cartridges_data($user){
    global $conn;
    $sql = "SELECT * FROM cartridges WHERE users = '$user'";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $cartridges = [];

    while ($row = $result->fetch_assoc()) {
        $cartridges[] = $row;
    }

    return $cartridges;
}

function generate_cartridge_html($cartridge) {

    $html = '<div class="cartridge-item';

    // Add a class for stock danger if needed
    if ($cartridge['stock'] < $cartridge['stock_min'] && $cartridge['stock'] > 0) {
        $html .= ' stock-danger';
    }
    if ($cartridge['stock'] > $cartridge['stock_min']) {
        $html .= ' stock-good';
    }
    $html .= '" onclick="cartridgeClicked(\'' . $cartridge['id'] . '\',\'' . $cartridge['stock'] . '\')">';

    if ($cartridge['stock'] == 0) {
        $html .= '<div class="overlay-out">En rupture</div>';
    }
    $html .= '<div class="stock-item-data"><p>' . $cartridge['name'] . '</p><span class="badge-color ' . $cartridge['color'] . '"></span></div>';
    $html .= '<p class="stock-values">En stock : <span>' . $cartridge['stock'] . '</span></p>';
    $html .= '<p class="stock-values">Stock min : <span>' . $cartridge['stock_min'] . '</span></p>';
    $html .= '</div>';

    return $html;
}

$printers = get_printers_list();

foreach ($printers as $printer) {
    echo '<div class="printer-card">';
    echo '<h3>Utilisateurs : ' . $printer . '</h3>';
    echo '<div class="cartridge-container">';

    $cartridges = get_cartridges_data($printer);

    foreach ($cartridges as $cartridge) {
        echo generate_cartridge_html($cartridge);
    }

    echo '</div>';
    echo '</div>';
}
?>
