
<?php
include_once  dirname(__DIR__)."\db\db_connection.php";

function searchList($filter){
    global $conn;
    $html='<option value="none">-</option>';
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

?>
<div class="mvt-stock-header">
    <h2>Mouvements Stock </h2>
</div>
<form class="filters-container">
    <div class="filters-data-container">
        <div class="filters-data">
            <fieldset class="filter-box">
                <legend class="filter-type-title">Toner</legend>
                <select id="filter-name" class="select-filter" name="name">
                    <?php echo searchList('name');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Utilisateur</legend>
                <select id="filter-users" class="select-filter" name="users">
                    <?php echo searchList('users');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Couleur</legend>
                <select id="filter-color" class="select-filter" name="color">
                    <?php echo searchList('color');?>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Type</legend>
                <select id="filter-type" class="select-filter" name="type">
                    <option value="none">-</option>
                    <option value="e">Entrée</option>
                    <option value="s">Sortie</option>
                </select>
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Du</legend>
                <input type="date" class="select-filter" id="filter-date-from" value="2020-01-01" name="dateFrom">
            </fieldset>
            <fieldset class="filter-box">
                <legend class="filter-type-title">Au</legend>
                <input type="date" class="select-filter" id="filter-date-to" value="2024-01-01" name="dateTo">
            </fieldset>
            <button type="submit" class="filter-cancel"><i class="fa-regular fa-circle-xmark fa-xl cancel-filter" style="color: #f07575;"></i></button>
        </div>
    </div>
    <div class="pagination-data">

    </div>
</form>