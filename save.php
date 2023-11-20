

<div class="container">
        <div id="cartridge-list">
            <h2>Cartridge List</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="kanban-item" draggable="true" data-cartridge-id="' . $row["id"] . '" data-quantity="1">
                        Name: ' . $row["name"] . ' | Quantity: ' . $row["quantity"] . 'Utilisateur: ' . $row["id"] . '
                    </div>';
                }
            } else {
                echo "No cartridges in stock.";
            }
            ?>
        </div>

        <div id="kanban-table">
            <div class="kanban-column">
                <h2>En cours d'utilsiation</h2>
                <!-- Add users/printers dynamically -->
            </div>



            <div class="kanban-column" id="kanban-column-consumed">
                <h2>Utilisés</h2>
                <!-- Consumed cartridges will be displayed here -->
            </div>
        </div>
    </div>
    <!-- Add this form in the appropriate section of your index.php file -->
    <div id="add-to-stock" class="tab-content">
        <h2>Add Cartridge to Stock</h2>
        <form action="add_cartridge.php" method="post">
            <label for="model">Model:</label>
            <input type="text" name="model" required>

            <label for="color">Color:</label>
            <input type="text" name="color" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" required>

            <label for="isNewCartridge">Is it a new cartridge?</label>
            <input type="radio" name="isNewCartridge" value="new" checked> New
            <input type="radio" name="isNewCartridge" value="existing"> Existing

            <!-- Additional input for existing cartridge selection -->
            <div id="existingCartridgeSelection" style="display: none;">
                <label for="existingCartridgeId">Select existing cartridge:</label>
                <select name="existingCartridgeId">
                    <?php
                    // Fetch existing cartridges from the database and populate the dropdown dynamically
                    $existingCartridgesSql = "SELECT id, model FROM cartridges";
                    $existingCartridgesResult = $conn->query($existingCartridgesSql);

                    if ($existingCartridgesResult->num_rows > 0) {
                        while ($row = $existingCartridgesResult->fetch_assoc()) {
                            echo '<option value="' . $row["id"] . '">' . $row["model"] . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <button type="submit">Submit</button>
        </form>
    </div>