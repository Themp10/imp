<?php
include "db_connection.php";

function get_cartridges_list(){
    global $conn;
    $sql = "SELECT * FROM cartridges";
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

function generate_cartridge_Item($cartridges) {
    $html ="";
    foreach ($cartridges as $cartridge) {
        $html .= '<li class="task">';
        $html .= '<div class="stock-item-data"><p>' . $cartridge['name'] . '</p><span class="badge-color ' . $cartridge['color'] . '"></span></div>';
        $html .= '<p class="stock-values-sortie">En stock : <span>' . $cartridge['stock'] . '</span></p>';
        $html .= '</li>';
    }

    return $html;
}
$cartridgesList=get_cartridges_list();

?>

<div class="sortie-stock-header">
    <h2>Sortie Stock </h2>
</div>
<div class="main-container">
    <ul class="columns">

        <li class="column to-do-column">
        <div class="column-header">
            <h4>Stock</h4>
        </div>
        <ul class="task-list" id="to-do">
            <?php echo generate_cartridge_Item($cartridgesList); ?>
        </ul>
        </li>



        <li class="column done-column">
        <div class="column-header">
            <h4>Sortie</h4>
        </div>
        <ul class="task-list" id="trash">


        </ul>
        <div class="column-button">
            <button class="button delete-button" onclick="emptyTrash()">Valider</button>
        </div>
        </li>

    </ul>
</div>