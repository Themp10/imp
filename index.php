<?php

require "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Stock de toner</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" href="assets/icon.png">
</head>

<body>
    <header class="page-header">
            <img src="./assets/logo.png" alt="Logo" class="header-logo">
            <h1>Gestionnaire de Stock de toner</h1>
            <div class="settings-container">
                
            </div>
    </header>

    <nav>
    <a href="#" onclick="showContent('cartridge-inventory')">Inventaire</a>
    <a href="#" onclick="showContent('take-from-stock')" id="a-stock">Sortie Stock</a>
    <a href="#" onclick="showContent('stock-movements')">Mouvements Stock</a>
    <a href="#" onclick="showContent('page-DA')">DA SAP</a>
    <a href="#" onclick="showContent('page-tdb')">Tableau de bord</a>
    <!-- <a href="#" onclick="showContent('add-to-stock')">Entrée Stock</a> -->
    
    </nav>
    <?php
        include "src/modals/entree_stock.php";
        include "src/modals/bon_sortie.php";
    ?>
    <div class="container" id="main-container">
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
    </div>
<!-- Scripts for Drag and Drop -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js"></script>

<!-- Gestion de l'ajouter de quantité au click sur un toner -->
<script>
    window.onload = (event) => {
        let screenId=localStorage.getItem("screen");
        if(screenId){
            document.getElementById(screenId).classList.add("show");
        }else{
            document.getElementById("cartridge-inventory").classList.add("show");
        }

    };
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
    <script src="https://kit.fontawesome.com/49540fc0d4.js" crossorigin="anonymous"></script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
