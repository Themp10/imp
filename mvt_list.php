<?php

include "db_connection.php";

function getMvtDataWithPagination($page, $perPage) {
    global $conn;

    // Calculate the offset
    $offset = ($page - 1) * $perPage;

    // Perform the database query to get movement data with pagination
    $sql  ="SELECT c.name ,c.color,m.user,c.users,m.qte,m.stock_apres,CASE WHEN m.type='e' THEN 'entrée' WHEN m.type='e' THEN 'sortie' END AS 'type',m.mvt_date" ;
    $sql .=" FROM mouvements m";
    $sql .=" INNER JOIN cartridges c ON c.id = m.id_cartridge";
    $sql .=" LIMIT ".$perPage." OFFSET ".$offset;
    $result = $conn->query($sql);

    if ($result) {
        // Fetch the data as an associative array
        $mvtData = $result->fetch_all(MYSQLI_ASSOC);
        return $mvtData;
    } else {
        return "Error fetching movement data: " . $conn->error;
    }
}


function getTotalRows() {
    global $conn;

    // Perform the database query to get the total number of rows
    $sql = "SELECT COUNT(*) as totalRows FROM mouvements";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['totalRows'];
    } else {
        return 0;
    }
}
$perPageOptions = [ 5, 10, 20, 100,200,1000];
// Get the selected number of rows per page from the URL parameter or use the default value
$perPage = isset($_GET['perPage']) && in_array($_GET['perPage'], $perPageOptions)
    ? intval($_GET['perPage'])
    : $perPageOptions[2]; // Default to 10 if not set or invalid

// Get the current page from the URL parameter, default to page 1
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
// Get movement data with pagination
$mvtData = getMvtDataWithPagination($page, $perPage);

// Get the total number of rows
$totalRows = getTotalRows();

// Calculate the total number of pages
$totalPages = ceil($totalRows / $perPage);


?>


<h2>Mouvements Stock </h2>
<div class="pagination-container">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class ="page-button" href="?page=<?= $page - 1 ?>&perPage=<?= $perPage ?>">←</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a  href="?page=<?= $i ?>&perPage=<?= $perPage ?>" class ="page-button <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a class ="page-button" href="?page=<?= $page + 1 ?>&perPage=<?= $perPage ?>">→</a>
            <?php endif; ?>
        </div>

        <!-- Rows per page dropdown menu -->
        <form action="" method="get">
            <label for="perPage">Lignes :</label>
            <select name="perPage" id="perPage" class="update-button">
                <?php foreach ($perPageOptions as $option): ?>
                    <option value="<?= $option ?>" <?= $option == $perPage ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Ok" class="show-page-button">
        </form>

    </div>
<?php if (is_array($mvtData)): ?>
    <table border="1">
        <thead>
            <tr>
                <th>Toner</th>
                <th>Utilisateurs</th>
                <th>Couleur</th>
                <th>Quantité</th>
                <th>Stock Après</th>
                <th>Demandeur</th>
                <th>Type mouvement</th>
                <th>Date mouvement</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mvtData as $row): ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['users'] ?></td>
                    <td><?= $row['color'] ?></td>
                    <td><?= $row['qte'] ?></td>
                    <td><?= $row['stock_apres'] ?></td>
                    <td><?= $row['user'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td><?= $row['mvt_date'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- Pagination -->



<?php else: ?>
    <p><?= $mvtData ?></p>
<?php endif; ?>

<!-- Add any additional content or scripts here -->
