<?php

include "db_connection.php";

function getDatawithFilters($filters){

}
// $filters=[];
// if ($_SERVER["REQUEST_METHOD"] == "GET") {
//     // Ensure the 'id' parameter is set
//     if (isset($_GET["name"])) {
//         $filters = $_GET["name"];
//         echo getDatawithFilters($filters);
        
//         exit(); // Terminate further execution
//     }
// }
// var_dump($filters);


function getMvtDataWithPagination($page, $perPage) {
    global $conn;

    $offset = ($page - 1) * $perPage;

    // Perform the database query to get movement data with pagination
    $sql  ="SELECT c.name ,c.color,m.user,c.users,m.qte,m.stock_apres,CASE WHEN m.type='e' THEN 'entrée' WHEN m.type='s' THEN 'sortie' END AS 'type',m.mvt_date" ;
    $sql .=" FROM mouvements m";
    $sql .=" INNER JOIN cartridges c ON c.id = m.id_cartridge";
    $sql .=" WHERE 1=1 ";
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

$name = isset($_GET['name']) ? $_GET['name'] : "";
$color = isset($_GET['color']) ? $_GET['color'] : "";
$users = isset($_GET['users']) ? $_GET['users'] : "";
$type = isset($_GET['type']) ? $_GET['type'] : "";
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : "";
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : "";
$filters=[];
array_push($filters, $name ,$color ,$users ,$type ,$dateFrom ,$dateTo);

//terminer ici



var_dump($filters);
// Get the total number of rows
$totalRows = getTotalRows();

// Calculate the total number of pages
$totalPages = ceil($totalRows / $perPage);


function searchList($items){
    global $conn;
    $itemList=[];
    if($items=="type"){
        $itemList=['Entrée','Sortie'];
    }else{
        $sql  ="SELECT DISTINCT $items FROM cartridges" ;
        $result = $conn->query($sql);
        //var_dump($result);
        if ($result) {
            // Fetch the data as an associative array
            $itemList = $result->fetch_all(MYSQLI_ASSOC);
      
        }
    }

    return $itemList;
}
function generate_filters() {
    global $conn;
    $filterTitle = ['Toner', 'Couleur', 'Utilisateur'];
    $filters = ['name', 'color', 'users'];
    $html = '';

    foreach ($filters as $index => $filter) {
        $items = searchList($filter);
        
        if (!is_array($items)) {
            // Handle error if searchList returns an error
            
            $html .= '<p>' . $filter . '</p>';
        } else {
            // Generate HTML select box for each filter
            $html .= '<div class="filter-box">';
            $html .= '<p>' . $filterTitle[$index] . '</p>';
            $html .= '<select id="filter-' . $filter . '" class="filter-select">';
            $html .= '<option value="none">-</option>';
            foreach ($items as $item) {
                $html .= '<option value="' . $item[$filter] . '">' . $item[$filter] . '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';
        }
    }
    $html .= '<div class="filter-box">';
    $html .= '<p>Type</p>';
    $html .= '<select id="filter-type" class="filter-select">';
    $html .= '<option value="none">-</option>';
    $html .= '<option value="e">Entrée</option>';
    $html .= '<option value="s">Sortie</option>';
    $html .= '</select>';
    $html .= '</div>';
    $date=date('Y-m-d');
    $html .= '<input type="date" class="filter-select" id="filter-date-from" value="'.$date.'">-';
    $html .= '<input type="date" class="filter-select" id="filter-date-to" value="'.$date.'" >';
    $html .= '<input type="button" value="Filtrer" class="show-page-button" onclick="appliquerFiltre()">';
    $html .= '<i class="fa-regular fa-circle-xmark fa-xl cancel-filter" style="color: #f07575;" onclick="supprimerFiltre()"></i>';
    return $html;
}



?>



<div class="mvt-stock-header">
    <h2>Mouvements Stock </h2>
</div>
<div class="filters-container">

<?php echo generate_filters(); ?>
</div>
<div class="pagination-container">
    <input type="button" value="Exporter (ne fonctionne pas)" class="show-page-button">
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
    <form class="page-selector" action="" method="get">
        <label for="perPage">Lignes :</label>
        <select name="perPage" id="perPage" class="filter-select">
            <?php foreach ($perPageOptions as $option): ?>
                <option value="<?= $option ?>" <?= $option == $perPage ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Ok" class="show-page-button">
    </form>

</div>
<?php if (is_array($mvtData)): ?>
    <div class="mvt-table-container">
        <table class="mvt-table" border="1">
            <thead class="mvt-table-thead">
                <tr class="mvt-table-tr">
                    <th >Toner</th>
                    <th>Utilisateurs</th>
                    <th>Couleur</th>
                    <th>Quantité</th>
                    <th>Stock Après</th>
                    <th>Demandeur</th>
                    <th>Type mouvement</th>
                    <th>Date mouvement</th>
                </tr>
            </thead>
            <tbody  class="mvt-table-tbody">
                <?php foreach ($mvtData as $row): ?>
                    <tr class="mvt-table-tr">
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
    </div>

    <!-- Pagination -->



<?php else: ?>
    <p><?= $mvtData ?></p>
<?php endif; ?>

<script>
    function supprimerFiltre(){
        document.getElementById("filter-name").value="none"
        document.getElementById("filter-color").value="none"
        document.getElementById("filter-users").value="none"
        document.getElementById("filter-type").value="none"
    }
    function appliquerFiltre(){
        let filters={
            name:document.getElementById("filter-name").value,
            color:document.getElementById("filter-color").value,
            users:document.getElementById("filter-users").value,
            type:document.getElementById("filter-type").value,
            dateFrom:document.getElementById("filter-date-from").value,
            dateTo:document.getElementById("filter-date-to").value
        }
        $.ajax({
            type: 'GET',
            url: 'mvt_list.php', // Replace with the actual URL for your PHP script
            data: filters,
            success: function(response) {
                // Parse the JSON response
                var data = JSON.parse(response);
                console.log(response)
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }
</script>
