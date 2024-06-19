<?php
define('BASEDIR', __DIR__);
require "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
session_start();
$hidden=false;
$profile=$_SESSION['profile'];
// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['connected']) || $_SESSION['connected'] !== true) {
    header('Location: index.php');
    exit;
}else{
    if($profile !="admin"){
        $hidden=true;
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <!-- <meta charset="UTF-8"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet Groupe Mfadel</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" href="assets/icon.png">
</head>

<body>
    <header class="page-header">
            <img src="./assets/MG-logo.png" alt="Logo" class="header-logo">
                <h1>Intranet Groupe Mfadel</h1>
            
            <div class="settings-container">
                <h2 class="settings-username"><?php echo $_SESSION['user']." | ".$_SESSION['profile'];?></h2>
            <form action="logout.php" method="get">
                <input type="submit" class ="deco-btn" value="Se déconnecter">
            </form>
            </div>
    </header>

    <nav>
    <?php if($profile =="admin") : ?>
        <a href="#" id="sub-stock">Gestion de stock</a>
        <a href="#" id="sub-da">Gestion des DA</a>
    <?php endif; ?>

    <?php if($profile =="admin" ||$profile =="achat") : ?>
        <!-- <a href="#" onclick="showContent('page-all-DA')">Suvi DA</a> -->
    <?php endif; ?>

    <!-- <a href="#" onclick="showContent('page-mes-DA')">Mes DA</a> -->
    <?php if($profile =="admin") : ?>
        <a href="#" onclick="showContent('page-printers')">Imprimantes</a>
        <a href="#" onclick="showContent('page-users')">Utilisateurs</a>
        <a href="#" onclick="showContent('page-sap-query')">Requetes SAP</a>
        <a href="#" id="sub-rh">Gestion des sorties</a>
        <a href="#" onclick="showContent('page-decharge')">Décharge Matériel</a>
        <a href="#" onclick="showContent('page-switch')">Etat des switchs</a>

    <?php endif; ?>
    <!-- <a href="#" onclick="showContent('add-to-stock')">Entrée Stock</a> -->
    
    </nav>
    <?php
        include "src/modals/entree_stock.php";
        include "src/modals/bon_sortie.php";
    ?>
    <div class="tool-tip" id="tool-tip"></div>
    <div class="container" id="main-container">
        <div id="sub-nav" class="sub-nav"></div>
        <?php if(!$hidden) : ?>


        <div class="inner-container" id="cartridge-inventory">
            <div class="inventory-header">
                <h2 class="inventory-title">Etat du stock</h2>
                <input type="button" class="show-page-button" value="Nouveau Toner" onClick="addnew()">
            </div>
            
            <div class="inv-items-container">
                <?php include "src/screens/inventory.php"; ?>
            </div>
        </div>

        <div class="inner-container" id="stock-movements">
            <div class="mvt-items-container">
                <?php include "src/screens/ml.php"; ?>
            </div>
        </div>
        <div class="inner-container" id="take-from-stock">
            <?php include "src/screens/sortie_stock.php"; ?>
        </div>
        <div class="inner-container" id="page-DA">
            <?php include "src/screens/liste_da.php"; ?>
        </div>
        <div class="inner-container" id="page-tdb">    
            <?php include "src/screens/tbd.php"; ?>
        </div>
        <div class="inner-container" id="page-users">    
            <?php include "src/screens/users.php"; ?>
        </div>

        <div class="inner-container" id="page-sap-query">    
            <?php include "src/screens/SAPquery.php"; ?>
        </div>
        <div class="inner-container" id="page-decharge">    
            <?php include "src/screens/decharge.php"; ?>
        </div>

        <div class="inner-container" id="page-switch">    
            <?php include "src/screens/switchs.php"; ?>
        </div>

        <div class="inner-container" id="page-printers">    
            <?php include "src/screens/printers.php"; ?>
        </div>

        <?php endif; ?>
        <div class="inner-container" id="page-mes-DA">    
            <?php include "src/screens/myDA.php"; ?>
        </div>

        <div class="inner-container" id="page-all-DA">    
            <?php include "src/screens/allDA.php"; ?>
        </div>

        <!-- Gestion des conges et sortie -->
        <div class="inner-container" id="page-new-leave">    
            <?php include "src/screens/rh_demande.php"; ?>
        </div>
        <div class="inner-container" id="page-my-leaves">    
            <?php include "src/screens/rh_leaves.php"; ?>
        </div>
        <div class="inner-container" id="page-validation">    
            <?php include "src/screens/rh_validation.php"; ?>
        </div>
        <div class="inner-container" id="page-etat">    
            <?php include "src/screens/rh_etat.php"; ?>
        </div>
    </div>

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js"></script> -->
<script src="src/static/dragula.js"></script>
<script src="src/static/jquery-3.6.4.min.js"></script>
<script src="src/static/49540fc0d4.js" crossorigin="anonymous"></script>
<!-- <script src="https://kit.fontawesome.com/49540fc0d4.js" crossorigin="anonymous"></script> -->

<!-- Scripts for Drag and Drop -->
<!-- Gestion de l'ajouter de quantité au click sur un toner -->
<script>
    function addnew(){
        openModal() 
    }
    function cartridgeClicked(id){
        openModal(id) 
    }
</script>
<script>
        /* Custom Dragula JS */
    dragula([
    document.getElementById("da-set"),
    document.getElementById("stock-set"),
    document.getElementById("cart")
    ]);
    removeOnSpill: false
    .on("drag", function(el) {
        el.className.replace("ex-moved", "");
    })
    .on("drop", function(el) {
        el.className += "ex-moved";
    })
    .on("over", function(el, container) {
        container.className += "ex-over";
    })
    .on("out", function(el, container) {
        container.className.replace("ex-over", "");
    });


    /* Vanilla JS to delete tasks in 'cart' column */
    function emptycart() {
    /* Clear tasks from 'cart' column */
    document.getElementById("cart").innerHTML = "";
    }

</script>
    <script>
    function showContent(id) {
            localStorage.setItem("screen", id);
        // Hide all containers
            var containers = document.querySelectorAll('.inner-container');
            containers.forEach(function(container) {
                container.classList.remove('show');
            });

            // Show the selected container
            var selectedContainer = document.getElementById(id);
            if (selectedContainer) {
                selectedContainer.classList.add('show');
            }
        }
    </script>

    <script>
        // JavaScript for drag-and-drop functionality
        const draggables = document.querySelectorAll('.kanban-item');
        const columns = document.querySelectorAll('.kanban-column');

        draggables.forEach(draggable => {
            draggable.addEventListener('dragstart', () => {
                const clone = draggable.cloneNode(true);
                clone.classList.add('dragging');
                clone.dataset.quantity = 1; // Set the quantity to 1
                draggable.parentElement.insertBefore(clone, draggable.nextSibling);
            });

            draggable.addEventListener('dragend', () => {
                const cartridgeId = draggable.dataset.cartridgeId;
                const statusColumn = getColumnStatus(draggable.parentElement.id); // Get status based on column ID
                draggable.remove();
            });
        });

        columns.forEach(column => {
            column.addEventListener('dragover', e => {
                e.preventDefault();
                const afterElement = getDragAfterElement(column, e.clientY);
                const draggable = document.querySelector('.dragging');
                if (afterElement == null) {
                    column.appendChild(draggable);
                } else {
                    column.insertBefore(draggable, afterElement);
                }
            });
        });

        function getDragAfterElement(column, y) {
            const draggableElements = [...column.querySelectorAll('.kanban-item:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Add this function to get the status based on the column ID
        function getColumnStatus(columnId) {
            switch (columnId) {
                case 'kanban-column-users':
                    return 'In Stock';
                case 'kanban-column-consumed':
                    return 'Consumed';
                // Add more cases as needed
                default:
                    return 'Unknown';
            }
        }

    </script>
    <script>
        $(document).ready(function() {
            $('nav a').hover(
                function() {
                        $('#sub-nav').empty()
                        let id = $(this).attr('id');
                        if(id =='sub-stock') {   
                            $('#sub-nav').addClass('hovered');      
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'cartridge-inventory\')">Inventaire</a>');
                            if($(".input-switch")[0].checked){
                                $('#sub-nav').append('<a href="#" onclick="showContent(\'take-from-stock\')">Sortie Stock</a>');
                            }else{
                                $('#sub-nav').append('<a href="#" onclick="showContent(\'take-from-stock\')">Sasie DA</a>');
                            }
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'stock-movements\')">Mouvements Stock</a>');
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-tdb\')">Tableau de bord</a>');
                        }
                        if(id =='sub-da') {   
                            $('#sub-nav').addClass('hovered');      
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-DA\')">DA Toner</a>');
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-mes-DA\')">Mes DA</a>');
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-all-DA\')">Suvi DA</a>');                         
                        }
                        if(id =='sub-rh') {   
                            $('#sub-nav').addClass('hovered');      
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-new-leave\')">Nouvelle demande</a>');
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-my-leaves\')">Mes demandes</a>');
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-validation\')">Validation</a>');                         
                            $('#sub-nav').append('<a href="#" onclick="showContent(\'page-etat\')">Etat des employés</a>');                         
                        }
                    },
                function() { 
                    $('#sub-nav').removeClass('hovered');
                    //$('#sub-nav').empty();
                } // Mouse leave
            );
            });
    </script>
        <script>
        $(document).ready(function() {
            $('#sub-nav').hover(
                function() {
                    $('#sub-nav').addClass('hovered'); 
                    },
                function() { 
                    $('#sub-nav').removeClass('hovered');
                    $('#sub-nav').empty();
                } // Mouse leave
            );
            });
    </script>
    <script>
        window.onload = (event) => {

            let profile="<?php echo $profile ;?>"
            if(profile =="achat") {
                showContent('page-all-DA')
            }else if(profile =="user") {
                showContent('page-mes-DA')
            }else if(profile =="admin") {
                let screenId=localStorage.getItem("screen");
                if(screenId){
                    document.getElementById(screenId).classList.add("show");
                }else{
                    document.getElementById("cartridge-inventory").classList.add("show");
                }

            }
        

    };
    </script>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
