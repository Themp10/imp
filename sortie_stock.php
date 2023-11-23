<?php
include "db_connection.php";

function get_cartridges_list(){
    global $conn;
    $sql = "SELECT * FROM cartridges where stock>0";
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
        $html .= '<li class="task" id-cartridge='.$cartridge['id'].'>';
        $html .= '<div class="stock-item-data"><p class="cartridge-name">' . $cartridge['name'] . '</p><span class="badge-color ' . $cartridge['color'] . '"></span></div>';
        $html .= '<p class="cartridge-users">' . $cartridge['users'] . '</p>';
        $html .= '<p class="stock-values-sortie">En stock : <span class="stock-quantity">' . $cartridge['stock'] . '</span></p>';
        $html .= '</li>';
    }

    return $html;
}
$cartridgesList=get_cartridges_list();

?>

<div class="sortie-stock-header">
    <h2>Sortie Stock </h2>
    <input type="text" id="item-search" placeholder="Tonner">
</div>
<div class="main-container">
    <ul class="columns">

        <li class="column stock-set-column">
        <div class="column-header">
            <h4>Stock</h4>
           
        </div>
        <ul class="task-list" id="stock-set">
            <?php echo generate_cartridge_Item($cartridgesList); ?>
        </ul>
        </li>



        <li class="column done-column">
        <div class="column-header">
            <h4>Sortie</h4>
        </div>
        <ul class="task-list" id="cart">


        </ul>
        <div class="column-button">
            <button class="button delete-button" onclick="validerSortie()">Valider</button>
        </div>
        </li>

    </ul>
</div>

<script>
    function validerSortie(){
        //récupérer les id des toner selectionés
        let tonerList=Array.from(document.querySelector('#cart').children)
        let selectedIds=tonerList.map(toner =>toner.getAttribute('id-cartridge'));
        let strIds=selectedIds.join(",")

        openBSModal(strIds)
    }
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('item-search');

        input.addEventListener('input', function () {
            var filter = input.value.toLowerCase();
            var cartridges = document.querySelectorAll('.task');

            cartridges.forEach(function(cartridge) {
                var name = cartridge.querySelector('.cartridge-name').textContent.toLowerCase();
                var stock = cartridge.querySelector('.stock-quantity').textContent.toLowerCase();
                var users = cartridge.querySelector('.cartridge-users').textContent.toLowerCase();

                if (name.includes(filter) || stock.includes(filter)|| users.includes(filter)) {
                    cartridge.style.display = '';
                } else {
                    cartridge.style.display = 'none';
                }
            });
        });
    });
</script>