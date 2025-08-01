

<?php
//var_dump(dirname(__DIR__)."\util\mvt_stock.php");
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."util".DIRECTORY_SEPARATOR ."mvt_stock.php";
// Fonctions pour mettre a jour le stock

function insertStockInDatabase($name, $model, $selectedColors, $stock, $stock_min, $users,$type,$nb_printer){
    global $conn;
    $colors = explode(",", $selectedColors);
    $user="admin";
    foreach ($colors as $color) {
        $insertQuery = "INSERT INTO cartridges (name, model,type, color, stock, stock_min, users,nb_printer) VALUES ('$name', '$model','$type', '$color', $stock, $stock_min, '$users','$nb_printer ')";
        if ($conn->query($insertQuery) === TRUE) {
            $id = $conn->insert_id;
            entreeStock($id,$user,$stock);   
        } else {
            return 'Error inserting stock: ' . $conn->error;
        }

    } 
    return 'success';
}
function updateStockInDatabase($id, $currentStock, $addedStock)
{
    global $conn;
    $id = mysqli_real_escape_string($conn, $id);
    $newStock = $currentStock+ $addedStock;
    $user="test";
    $sql = "UPDATE cartridges SET stock = '$newStock' WHERE id = '$id'";
   
    if ($conn->query($sql) === TRUE) {
        entreeStock($id,$user,$addedStock);
        return 'success';
    } else {
        return 'Error updating stock: ' . $conn->error;
    }
}

function getCartridgeById($id) {
    global $conn;
    // Sanitize input to prevent SQL injection
    $id = mysqli_real_escape_string($conn, $id);
    
    // Perform the database query
    $sql = "SELECT * FROM cartridges WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch the data as an associative array
        $cartridgeData = $result->fetch_assoc();
        return $cartridgeData;
    } else {
        return null;
    }
}

// POST check pour voir si on va lancer l'update car car ce fichier est inclu dans index.php, et vu que c'est un get, rien ne ce passe au niveau du php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST["id"];
    if( $id==-1){
        $name = $_POST["name"];
        $model = $_POST["model"];
        $selectedColors = $_POST["selectedColors"];
        $stock = $_POST["stock"];
        $stock_min = $_POST["stock_min"];
        $users = $_POST["users"];
        $type = $_POST["type"];
        $nb_printer = $_POST["nb_printer"];
        $insertResult = insertStockInDatabase($name, $model, $selectedColors, $stock, $stock_min, $users,$type,$nb_printer);
        echo $insertResult;

    }else{
        $currentStock = $_POST["currentStock"];
        $addedStock = $_POST["addedStock"];
        $updateResult = updateStockInDatabase($id, $currentStock, $addedStock);
        echo $updateResult;
    }

    exit(); 
}
// Check if it's a GET request
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Ensure the 'id' parameter is set
    if (isset($_GET["id"])) {
        $id = $_GET["id"];

        // Perform treatment to get cartridge data
        $cartridgeData = getCartridgeById($id);

        // If data is found, convert it to JSON and echo it
        if ($cartridgeData !== null) {
            $result = json_encode($cartridgeData);
            echo $result;
        } else {
            // If no data is found, echo an error message
            echo "Cartridge not found.";
        }

        exit(); // Terminate further execution
    }
}
?>
<!-- html du modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>


        <label for="name">Nom :</label>
        <input type="text" name="name" id="name" class="modal-input" disabled>

        <label for="model">Modèle :</label>
        <input type="text" name="model" id="modalCartridgeName" class="modal-input" disabled>

        <div class="options-container">
            <div class="color-selector-container">
                <label for="color">Couleur :</label>
                <input type="text" name="color" id="color" class="modal-input" disabled>
                <div class="color-selector" id="color-selector">
                    <input type="checkbox" name="cb-black" id="cb-black" class="input-color Noir">
                    <input type="checkbox" name="cb-jaune" id="cb-jaune" class="input-color Jaune">
                    <input type="checkbox" name="cb-magenta" id="cb-magenta" class="input-color Magenta">
                    <input type="checkbox" name="cb-cyan" id="cb-cyan" class="input-color Cyan">
                </div>
            </div>
            <div class="type-selector">
                <label for="color">Type :</label>
                <div class="type-row">
                    <label for="type-cartouche">Cartouche</label><br>  
                    <input type="radio" id="type-cartouche" name="type-ct" value="cartouche">
                </div>
                <div class="type-row">
                    <label for="type-toner">Toner</label><br>
                    <input type="radio" id="type-toner" name="type-ct" value="toner">
                </div>
                <div class="type-row">
                    <label for="type-drum">Drum</label><br>
                    <input type="radio" id="type-drum" name="type-ct" value="drum">
                </div>    
            </div>
        </div>


        <label for="stock">Stock:</label>
        <input type="number" name="stock" id="stock" class="modal-input" min="0" value="0" disabled>

        <label for="stock_min">Stock Sécurité :</label>
        <input type="number" name="stock_min" id="stock_min" class="modal-input" min="0" value="0" disabled>

        <label for="users">Utilisateurs :</label>
        <input type="text" name="users" id="users" class="modal-input" disabled>

        <label for="nb_printer">Nb imprimante :</label>
        <input type="number" name="nb_printer" id="nb_printer" class="modal-input" min="0" value="0" disabled>


        <div id="new-stock">
            <label for="newStock">Cartouche / toner à ajouter :</label>
            <input type="number" id="newStock" class="modal-input" min="0" value="0">
        </div>

        <button id="stock-button" onclick="updateStock()" class="update-button">Mettre à jours Tonner</button>


    </div>
</div>
<!-- Js pour gere son comportement -->

<script>

    function getSelectedColors(){
        let colors=document.querySelectorAll('.input-color')
        let selectedColors=[]
        colors.forEach(element => {
            if(element.checked){
                selectedColors.push(element.classList[1])
               
            }
        });
        return selectedColors.join(",")
    }
    function openModal(cartridgeId=-1) {
        
        document.getElementById('myModal').dataset.cartridgeId = cartridgeId;
        if(cartridgeId==-1){

            let colors=document.querySelectorAll('.input-color')
            colors.forEach(element => {
                if(element.checked){
                    element.checked=false
                }
            });
            document.getElementsByClassName('update-button')[0].textContent="Insérer nouveau Tonner"

            document.getElementById('myModal').style.display = 'flex';

            document.getElementById('name').value = "";
            document.getElementById('name').disabled=false;

            document.getElementById('modalCartridgeName').value = "";
            document.getElementById('modalCartridgeName').disabled=false;


            document.getElementById('color').style.display = "none";
            
            document.getElementById('stock_min').value = 0;
            document.getElementById('stock_min').disabled=false;
            
            document.getElementById('users').value = "";
            document.getElementById('users').disabled=false;

            document.getElementById('stock').value = 0;
            document.getElementById('stock').disabled=false;

            document.getElementById('nb_printer').value = 0;
            document.getElementById('nb_printer').disabled=false;

            document.getElementById('new-stock').style.display = "none";
            document.getElementById('color-selector').style.display = "block";

        }else{
            document.getElementsByClassName('update-button')[0].textContent="Mettre à jours Tonner"

            document.getElementById('color-selector').style.display = "none";
            document.getElementById('new-stock').style.display = "block";
            document.getElementById('name').disabled=true;
            document.getElementById('modalCartridgeName').disabled=true;
            document.getElementById('color').disabled=true;
            document.getElementById('stock_min').disabled=true;
            document.getElementById('users').disabled=true;
            document.getElementById('nb_printer').disabled=true;
            document.getElementById('stock').disabled=true;
            $.ajax({
                type: 'GET',
                url: './src/modals/entree_stock.php', // Replace with the actual URL for your PHP script
                data: { id: cartridgeId },
                success: function(response) {
                    // Parse the JSON response
                    var cartridgeData = JSON.parse(response);

                    // Update the modal content
                    document.getElementById('modalCartridgeName').value = cartridgeData.model;
                    document.getElementById('color').value = cartridgeData.color;
                    document.getElementById('name').value = cartridgeData.name;
                    document.getElementById('stock_min').value = cartridgeData.stock_min;
                    document.getElementById('users').value = cartridgeData.users;
                    document.getElementById('stock').value = cartridgeData.stock;
                    document.getElementById('nb_printer').value = cartridgeData.nb_printer;
                    // Show the modal
                    document.getElementById('myModal').style.display = 'flex';
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('AJAX Error: ' + status + ' ' + error);
                }
            });
        }
    }

    //fermer le modal
    function closeModal() {
        document.getElementById('myModal').style.display = 'none';
        document.getElementById('newStock').value=0
    }
    // fontion ajax pour mettre a jour le stock dans la base de données
    var selectedColorsGlobal=""
    function updateStock() {
        let data={}
        var id=document.getElementById('myModal').dataset.cartridgeId 
        if(id==-1){

            

            let tonerName=document.getElementById('name').value
            if(tonerName=="" ){
                alert("Entrez le nom du toner!")
                return
            }
            let tonerModel=document.getElementById('modalCartridgeName').value
            if(tonerModel=="" ){
                alert("Entrez le modèle du toner!")
                return
            }

            selectedColorsGlobal=getSelectedColors()
            if(selectedColorsGlobal=="" ){
                alert("Choisir au moins un couleur")
                return
            }
            let tonerUsers=document.getElementById('users').value
            if(tonerUsers=="" ){
                alert("Entrez les utilisateurs de ce toner!")
                return
            }

            let stock=parseInt(document.getElementById('stock').value)
            if(stock <= 0 ){
                alert("Merci de renseigner le stock initiale ! ")
                return
            }
            let stock_min=parseInt(document.getElementById('stock_min').value)
            if(stock_min <= 0 ){
                alert("Merci de renseigner le stock de sécurité ! ")
                return
            }
            let nb_printer=parseInt(document.getElementById('nb_printer').value)
            if(nb_printer <= 0 ){
                alert("Merci de renseigner le nombre d'imprimantes ! ")
                return
            }
         
            let type_ctr=document.querySelector('input[name="type-ct"]:checked')
            if(!type_ctr){
                alert("Merci de choisir le type! ")
                return
            }
            data={  
                    id: id,
                    name:tonerName,
                    model: tonerModel,
                    selectedColors: selectedColorsGlobal,
                    users: tonerUsers,
                    stock: stock,
                    stock_min: stock_min,
                    type:type_ctr.value,
                    nb_printer:nb_printer,
                }

        }else{

            
            var addedStock=parseInt(document.getElementById('newStock').value)

            if(!addedStock ){
                alert("Entrez un nomber s'il vous plait !")
                return
            }
            if (addedStock==0){
                alert("Ca ne sert à rien d'ajouter 0 au stock !")
                return
            }
            if (addedStock<0){
                alert("Helas ! C'est pas possible d'ajouter du stock négatif ! ")
                return
            }
            var currentStock=parseInt(document.getElementById('stock').value)
            data= {
                    id: id,
                    addedStock:addedStock,
                    currentStock: currentStock
                }
        }

        // ajax POST pour inserer ou maj le stock

        $.ajax({
                type: 'POST',
                url: 'src/modals/entree_stock.php',
                data: data,
                success: function(response) {
                    // Check the response from the server and handle accordingly
                    if (response.trim() == 'success') {
                        // Update successful, close the modal
                        closeModal();
                        location.reload();
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
        var modalBS = document.getElementById('bsModal');
        if (event.target === modalBS) {
            closeBSModal();
        }
        var modalSearch = document.getElementById('search-modal');
        if (event.target === modalSearch) {
            closeSearchModal();
        }
        var modalRea = document.getElementById('reaffectation-modal');
        if (event.target === modalRea) {
            closeReafModal();
        }
    };
</script>
