
<?php
include_once  dirname(__DIR__). DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

$defaultPerPage=getPerPageOptions()[2];
$perPage = isset($_GET['perPage'])?intval($_GET['perPage']):$defaultPerPage;
$page = isset($_GET['selectedPage']) ?intval($_GET['selectedPage']): 1;
$totalRows=getNbRows();
$totalPages=ceil($totalRows / $perPage);

if (isset($_GET['action']) ) {
    if($_GET['action'] == 'setFilters'){
        if(isset($_GET['filters']) ){
            $filtredSql=getFiltredSql();
            $filtredSqlWithPagination=setPagination($filtredSql);
            $filtredDataArray=getFiltredData($filtredSqlWithPagination);

            if ($filtredDataArray !== null) {
                $filtredData = json_encode($filtredDataArray);
                echo $filtredData;
            } 
        }
    }
    
    exit();
} else {
    // affichage normal du html dessous
    //ce permet d'afficher des resultat au loading
    $sql  ="SELECT c.name ,c.color,m.user,c.users,m.qte,m.stock_apres,CASE WHEN m.type='e' THEN 'entrée' WHEN m.type='s' THEN 'sortie' END AS 'type',m.mvt_date" ;
    $sql .=" FROM mouvements m";
    $sql .=" INNER JOIN cartridges c ON c.id = m.id_cartridge";
    $sql .=" WHERE 1=1 ";
    $sqlWithPagination=setPagination($sql);
    $dataArray=getFiltredData($sqlWithPagination);
    if ($dataArray !== null) {
        $data = json_encode($dataArray["mvtData"]);
    } 
}

function getNbRows(){
    global $conn;
    $sql = "SELECT COUNT(*) FROM mouvements";
    $result = $conn->query($sql);
    if ($result) {
        $count = $result->fetch_row()[0];
        return $count;
    } else {
        return 0;
    }
}

function setPagination($sql){
    global $perPage;
    global $page;
    $offset = ($page - 1) * $perPage;
    $perPageOptions = getPerPageOptions();
    $sql .=" LIMIT ".$perPage." OFFSET ".$offset;
    return $sql;
}

function getFiltredData($filtredSql){
    global $conn;
    global $perPage;
    global $totalPages;
    global $page;

    $result = $conn->query($filtredSql);
    if ($result) {
        $mvtData = $result->fetch_all(MYSQLI_ASSOC);

        $fullData["mvtData"]=$mvtData;
        $fullData["perPage"]=$perPage;
        $fullData["totalPages"] = $totalPages;
        $fullData["selectedPage"] = $page;
        return $fullData;
    } else {
        return "Error fetching movement data: " . $conn->error;
    }
}

function getFiltredSql(){
    $name = $_GET['filters']['name'];
    $color = $_GET['filters']['color'];
    $users = $_GET['filters']['users'];
    $type = $_GET['filters']['type'];
    $dateFrom = $_GET['filters']['dateFrom'];
    $dateTo = $_GET['filters']['dateTo'];
    $filtredSql="SELECT c.name ,c.users,c.color,m.qte,m.stock_apres,m.user,CASE WHEN m.type='e' THEN 'entrée' WHEN m.type='s' THEN 'sortie' END AS 'type',m.mvt_date" ;
    $filtredSql .=" FROM mouvements m";
    $filtredSql .=" INNER JOIN cartridges c ON c.id = m.id_cartridge";
    $filtredSql .=" WHERE 1=1 ";
    $filters = [$name, $color, $users, $type];
    $columns = ['c.name', 'c.color', 'c.users', 'm.type'];
    for ($i = 0; $i < count($filters); $i++) {
        if ($filters[$i] != '' && $filters[$i] != 'none') {
            $filtredSql .= " AND " . $columns[$i] . " = '" . $filters[$i] . "'";
        }
    }
    $filtredSql .=" AND m.mvt_date BETWEEN '".$dateFrom."' AND '".$dateTo."'";
    return $filtredSql;
}
function searchList($filter){
    global $conn;
    $html='<option value="">-</option>';
    $sql  ="SELECT DISTINCT $filter FROM cartridges" ;
    $result = $conn->query($sql);
    if ($result) {
        // Fetch the data as an associative array
    $itemList = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($itemList as $item) {
        $html .= '<option value="' . $item[$filter] . '">' . $item[$filter] . '</option>';
    }
    }
    return $html;
}

function getPerPageOptions(){
    return [1,5, 10, 20, 100,200,1000];
}
?>
<div class="sortie-stock-header">
    <h2>Mouvements Stock </h2>
</div>
<div class="filters-container">
    <div class="filters-data-container">
        <div class="filters-data">
            <fieldset class="filter-box">
                <legend class="filter-type-title">Toner</legend>
                <select id="filter-name" class="select-filter" name="name" onchange="handleSelectChange()">
                    <?php echo searchList('name');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Utilisateur</legend>
                <select id="filter-users" class="select-filter" name="users" onchange="handleSelectChange()">
                    <?php echo searchList('users');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Couleur</legend>
                <select id="filter-color" class="select-filter" name="color" onchange="handleSelectChange()">
                    <?php echo searchList('color');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Type</legend>
                <select id="filter-type" class="select-filter" name="type" onchange="handleSelectChange()">
                    <option value="none">-</option>
                    <option value="e">Entrée</option>
                    <option value="s">Sortie</option>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Du</legend>
                <input type="date" class="select-filter" id="filter-date-from" value="2020-01-01" name="dateFrom" onchange="handleSelectChange()">
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Au</legend>
                <input type="date" class="select-filter" id="filter-date-to" value="2024-01-01" name="dateTo" onchange="handleSelectChange()">
            </fieldset>
            <button class="filter-cancel"><i class="fa-regular fa-circle-xmark fa-xl cancel-filter" style="color: #f07575;" onclick="supprimerFiltre()"></i></button>
        </div>
    </div>

    <div class="pagination-data">
        <!-- <button class="btn-arrow" id="prevPage" disabled>← <span class="nav-text"></span></button> -->
        <fieldset class="filter-box">
            <legend class="filter-type-title">Page</legend>
            <!-- <div class="list-pages" id="pagination-pages"></div> -->
            <select id="filter-page" class="select-filter" name="name" onchange="handleSelectChange()">
            <?php for ($i = 1; $i < $totalPages+1; $i++) { ?>
                <option value="<?= $i ?>" <?php //echo ($page == $i) ? 'selected' : ''; ?>>
                    <?= $i ?>
                </option>
            <?php } ?>

            </select>
        </fieldset>

        <!-- <button class="btn-arrow" id="nextPage"><span class="nav-text"></span> →</button> -->
        <fieldset class="filter-box">
                <legend class="filter-type-title">Eléments par page</legend>
                <select id="filter-per-page" class="select-filter" name="name" onchange="handleSelectChange()">
                    <?php foreach (getPerPageOptions() as $row): ?>
                        <option value="<?= $row?>" <?php echo ($perPage == $row) ? 'selected' : ''; ?> ><?= $row?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
    </div>
</div>

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
        <tbody  class="mvt-table-tbody" id="mvt-table-tbody">
            <?php foreach ($dataArray["mvtData"] as $row): ?>
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
<script>

    function supprimerFiltre(){

        document.getElementById("filter-name").value=""
        document.getElementById("filter-color").value=""
        document.getElementById("filter-users").value=""
        document.getElementById("filter-type").value=""
        document.getElementById("filter-date-from").value="2023-01-01"

        let currentDate = new Date();
        let formattedDate = currentDate.toISOString().split('T')[0];
        document.getElementById("filter-date-to").value=formattedDate
        handleSelectChange()
    }   
    function handleSelectChange(){
        let filters={
            name:document.getElementById("filter-name").value,
            color:document.getElementById("filter-color").value,
            users:document.getElementById("filter-users").value,
            type:document.getElementById("filter-type").value,
            dateFrom:document.getElementById("filter-date-from").value,
            dateTo:document.getElementById("filter-date-to").value
        }
        getData("setFilters",filters) 
    }
    function getData(action,filters) {
        $.ajax({
            type: 'GET',
            url: './src/screens/ml.php',
            data: {
                action: action, 
                filters:filters,
                perPage:document.getElementById("filter-per-page").value,
                selectedPage:document.getElementById("filter-page").value
            },
            success: function (response) {
                console.log(response);
                let data = JSON.parse(response);

                // generation de la pagination
                let pages=data["totalPages"]
                let pageBody=document.getElementById("filter-page")

                if (pageBody.hasChildNodes()) {
                        while (pageBody.firstChild) {
                            pageBody.removeChild(pageBody.firstChild);
                        }
                    }
                for (let i = 1; i < pages+1; i++) {
                    let option = document.createElement("option");
                    option.value = i;
                    option.textContent = i; 
                    pageBody.appendChild(option);    
                }

                document.getElementById("filter-page").value=data["selectedPage"]
                // generation du tableau
                let tableBody = document.getElementById("mvt-table-tbody");
                

                if (tableBody.hasChildNodes()) {
                        while (tableBody.firstChild) {
                            tableBody.removeChild(tableBody.firstChild);
                        }
                    }

                    data["mvtData"].forEach(function (row, index) {
                    let tr = document.createElement("tr");
                    tr.className = "mvt-table-tr";
                    for (let key in row) {
                        let td = document.createElement("td");
                        td.textContent = row[key];
                        tr.appendChild(td);
                    }
                    tableBody.appendChild(tr);
                });    
            },
            error: function (xhr, status, error) {

                console.error('AJAX Error: ' + status + ' ' + error);
            }
        });
    }

    
</script>

<script>



    function showPage(pageNumber,pages) {
        pages.forEach((page, index) => {
            if (index === pageNumber) {
                page.style.display = "block";
            } else {
                page.style.display = "none";
            }
        });
    }



    function setActive(pageNumbers,currentPage) {
        pageNumbers.forEach((page, index) => {
            if(currentPage === index) {
                page.classList.add("active-number");
            } else {
                page.classList.remove("active-number");
            }
        });
    }

    function setUpPages(){
    let totalPages=<?php echo $totalPages; ?>    
    const pages = document.querySelectorAll(".page");
    const pageNumbers = document.querySelectorAll(".page-number");
    let currentPage = 0;


        pageNumbers.forEach((page, index) => {
            page.addEventListener("click", function () {
                showPage(index,pages);
                currentPage = index;
                setActive(pageNumbers,currentPage);
            });
        });
        showPage(currentPage,pages);
    }

    setUpPages()


</script>