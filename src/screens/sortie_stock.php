<?php
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
//include_once "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

function get_cartridges_list(){
    global $conn;
    $sql = "SELECT * FROM cartridges ";
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
        $html .= '<li class="task" id-cartridge='.$cartridge['id'].' stock-min='.$cartridge['stock_min'].' stock='.$cartridge['stock'].' users="'.$cartridge['users'].'">';
        $html .= '<div class="stock-item-data"><p class="cartridge-name">' . $cartridge['name'] . '</p><span class="badge-color ' . $cartridge['color'] . '"></span></div>';
        $html .= '<p class="cartridge-users">' . $cartridge['users'] . '</p>';
        $html .= '<p class="stock-values-sortie">En stock : <span class="stock-quantity">' . $cartridge['stock'] . '</span></p>';
        $html .= '</li>';
    }

    return $html;
}
$cartridgesList=get_cartridges_list();

function getLastDaId(){
    global $conn;
    $sql="SELECT MAX(id_da)+1 as next_id FROM da_sap";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
    }
    $nextId = $row['next_id'];
    return $nextId;
}
function createDafromIds($ids){
    
    global $conn;
    $tabDA = explode("%%", $ids);
    $nextId=(int)getLastDaId();

    $datesortie = date("Y-m-d");

    foreach ($tabDA as $row) {
        $data=explode("#", $row);
        //$data ==> 0 : id |  1 : stock |  2 : stockmin |  3 : users 
        $insertQuery = "INSERT INTO da_sap (id_da,toner,qte, demandeur,date) VALUES ('$nextId','$data[0]','$data[2]','$data[3]', '$datesortie')";
        if ($conn->query($insertQuery) === TRUE) {
            $id = $conn->insert_id;
        } else {
            return 'Error creating DA : ' . $conn->error;
        }

    } 
    return 'success';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ids = $_POST["strIds"];
    
    //$updatedData = updateStockOutDatabase($demandeur,$rows);
    // if( $updatedData=='success'){
    //     $createdDA = createDA($demandeur,$rows);
    //     echo $createdDA ;
    // }else{
    //     echo $updatedData;
    // } 
    echo createDafromIds($ids);
    exit();    
}


?>

<div class="sortie-stock-header">
    <h2 id="stock-title">Sortie Stock </h2>
    <input type="text" id="item-search" placeholder="Tonner">
    <i id="clear-search" class="fa-regular fa-circle-xmark fa-xl cancel-filter" style="color: #bdbdbd;" onclick="viderRecherche(this)"></i>
    <label class="switch">
        <input type="checkbox" class="input-switch" onchange="handleSwitch(this)" checked>
        <span class="slider"></span>
    </label>
</div>
<div class="main-container">
    <ul class="columns">
        <li class="column da-set-column hide-column" id="da-column-id">
            <div class="column-header">
                <h4>DA</h4>
            </div>
            <ul class="task-list" id="da-set">
            </ul>
            <div class="column-button">
                <button class="button valider-button" onclick="validerDA()">Valider</button>
            </div>
        </li>
        <li class="column stock-set-column" id="stock-column-id">
            <div class="column-header">
                <h4>Stock</h4>
            </div>
            <ul class="task-list" id="stock-set">
                <?php echo generate_cartridge_Item($cartridgesList); ?>
            </ul>
        </li>
        <li class="column done-column" id="cart-column-id">
            <div class="column-header">
                <h4>Sortie</h4>
            </div>
            <ul class="task-list" id="cart">


            </ul>
            <div class="column-button">
                <button class="button valider-button" onclick="validerSortie()">Valider</button>
            </div>
        </li>

    </ul>
</div>

<script>


    function validerDA(){
        let tonerList=Array.from(document.querySelector('#da-set').children)
        let selectedIds=tonerList.map(toner =>toner.getAttribute('id-cartridge')+"#"+toner.getAttribute('stock')+"#"+toner.getAttribute('stock-min')+"#"+toner.getAttribute('users'));
        let strIds=selectedIds.join("%%")
        if(strIds==""){
            alert("Merci de choisir au moins un toner à commander !")
            return
        }

        $.ajax({
                type: 'POST',
                url: './src/screens/sortie_stock.php',
                data: {strIds},
                success: function(response) {
                    // Check the response from the server and handle accordingly
                    if (response.trim() == 'success') {
                        // Update successful, close the modal
                        location.reload();
                    } else {
                        // Handle error
                        alert('Error creating DA : ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });

    }

    function handleSwitch(e){
        let state=e.checked
        //true = sortie
        //false = DA
        var cartridges = document.querySelectorAll('#stock-set .task');

        
        
        if(state){
            document.getElementById('stock-title').textContent="Sortie Stock"
            cartridges.forEach(function(cartridge) {
            var stock = cartridge.querySelector('.stock-quantity').textContent.toLowerCase();
            if ( stock=="0") {
                    cartridge.style.display = 'none';
                } else {
                    cartridge.style.display = '';
                }
            });
            document.getElementById('da-column-id').classList.add("hide-column")
            document.getElementById('cart-column-id').classList.remove("hide-column")
        }else{
            document.getElementById('stock-title').textContent="Saisie DA"
            cartridges.forEach(function(cartridge) {
                cartridge.style.display = '';
            });
            document.getElementById('da-column-id').classList.remove("hide-column")
            document.getElementById('cart-column-id').classList.add("hide-column")
        }
    }
    function viderRecherche(a){
        var input = document.getElementById('item-search');
        var cartridges = document.querySelectorAll('#stock-set .task');
        cartridges.forEach(function(cartridge) {
            cartridge.style.display = '';
        });
        input.value="";
        a.style.display = 'none';
    }
    function validerSortie(){
        //récupérer les id des toner selectionés
        let tonerList=Array.from(document.querySelector('#cart').children)
        let selectedIds=tonerList.map(toner =>toner.getAttribute('id-cartridge'));
        let strIds=selectedIds.join(",")
        if (strIds==""){
            alert('Merci de choisir au moins un Toner !')
        }else{
            openBSModal(strIds)
        }
        
    }
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('item-search');

        input.addEventListener('input', function () {
            document.getElementById('clear-search').style.display = 'block';
            var filter = input.value.toLowerCase();
            var cartridges = document.querySelectorAll('#stock-set .task');

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


<script>
    var cartridges = document.querySelectorAll('#stock-set .task');

    cartridges.forEach(function(cartridge) {
        var stock = cartridge.querySelector('.stock-quantity').textContent.toLowerCase();
        if ( stock=="0") {
                cartridge.style.display = 'none';
            } else {
                cartridge.style.display = '';
            }
        }); 
</script>