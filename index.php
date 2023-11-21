<?php
include "db_connection.php"; // Include database connection file
//include "render.php";



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Stock de toner</title>
    <link rel="stylesheet" href="assets/style.css">

</head>

<body>
    <header>
        <h1>Gestionnaire de Stock de toner</h1>
    </header>

    <nav>
    <a href="#" onclick="showContent('cartridge-inventory')">Inventaire</a>
    <a href="#" onclick="showContent('stock-movements')">Mouvements Stock</a>
    <a href="#" onclick="showContent('add-to-stock')">Entrée Stock</a>
    <a href="#" onclick="showContent('take-from-stock')">Sortie Stock</a>
    </nav>
    <?php
        include "modal.php";
    ?>
    <div class="container" id="main-container">
        <div class="inner-container" id="cartridge-inventory">
            <h2 class="inventory-title">Etat du stock</h2>
            <div class="inv-items-container">
                <?php include "render.php"; ?>
            </div>
        </div>
        <div class="inner-container  show" id="stock-movements">
            <div class="inv-items-container">
                <?php include "mvt_list.php"; ?>
            </div>
        </div>
        <div class="inner-container" id="add-to-stock">
            <?php include "add_new.php"; ?>
        </div>
        <div class="inner-container" id="take-from-stock">
            <div class="main-container">
                <ul class="columns">

                    <li class="column to-do-column">
                    <div class="column-header">
                        <h4>Stock</h4>
                    </div>
                    <ul class="task-list" id="to-do">

                    </ul>
                    </li>



                    <li class="column done-column">
                    <div class="column-header">
                        <h4>Sortie</h4>
                    </div>
                    <ul class="task-list" id="trash">
                        <li class="task">
                        <p>Interviews</p>
                        </li>
                        <li class="task">
                        <p>Research</p>
                        </li>

                    </ul>
                    <div class="column-button">
                        <button class="button delete-button" onclick="emptyTrash()">Valider</button>
                    </div>
                    </li>

                </ul>
                </div>
            </div>
    </div>
<!-- Scripts for Drag and Drop -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.js"></script>

<!-- Gestion de l'ajouter de quantité au click sur un toner -->
<script>
    function cartridgeClicked(id){
        openModal(id) 
    }
</script>
<script>
        /* Custom Dragula JS */
    dragula([
    document.getElementById("to-do"),
    document.getElementById("doing"),
    document.getElementById("done"),
    document.getElementById("trash")
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

    /* Vanilla JS to add a new task */
    function addTask() {
    /* Get task text from input */
    var inputTask = document.getElementById("taskText").value;
    /* Add task to the 'To Do' column */
    document.getElementById("to-do").innerHTML +=
        "<li class='task'><p>" + inputTask + "</p></li>";
    /* Clear task text from input after adding task */
    document.getElementById("taskText").value = "";
    }

    /* Vanilla JS to delete tasks in 'Trash' column */
    function emptyTrash() {
    /* Clear tasks from 'Trash' column */
    document.getElementById("trash").innerHTML = "";
    }

</script>
    <script>
    function showContent(id) {
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

                // Update the status in the database
                updateCartridgeStatus(cartridgeId, statusColumn);

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

        // Add this function to update the status in the database
        function updateCartridgeStatus(cartridgeId, newStatus) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            const data = "cartridgeId=" + encodeURIComponent(cartridgeId) + "&newStatus=" + encodeURIComponent(newStatus);
            xhr.send(data);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                } else {
                    console.error("Error updating status");
                }
            };
        }
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
