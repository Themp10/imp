<?php
include "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

function get_da_list(){
    global $conn; 
    $sql = "SELECT * FROM da_sap";
    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in SQL query: " . $conn->error);
    }

    $da_list = [];

    while ($row = $result->fetch_assoc()) {
        $da_list[] = $row;
    }

    return $da_list;
}
?>

<div class="sortie-stock-header">
    <h2>Liste Demande d'Achat Toner </h2>
</div>

<div class="da-list-container">
    <div class="da-container da-en-cours">
        <div class="da-item-container">
            <p class="da-item-title">Modele</p>
            <p class="da-item-data"> HP 207 A </p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Couleur</p>
            <p class="da-item-data"> Noir</p>
            <p class="da-item-data"> Cyan</p>
            <p class="da-item-data"> Magenta</p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Quantité</p>
            <p class="da-item-data">1</p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Demandeur</p>
            <p class="da-item-data">Oussama/Oussama + AA/MMMM /FSDFSDFSDF sDFSF</p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Demande d'achat</p>
            <p class="da-item-data success-badge">15/12/2023 : 230000012</p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Bon de Commande</p>
            <p class="da-item-data warning-badge">15/12/2023 : 230000012</p>
        </div>
        <div class="da-item-container">
            <p class="da-item-title">Réception</p>
            <p class="da-item-data idle-badge">15/12/2023 : 230000012</p>
        </div>
    </div>
    <div class="da-container">
    <table>
        <thead>
            <tr>
            <!-- <th colspan="2">The table header</th> -->
            <th >Modèle</th>
            <th >Couleur</th>
            <th >Quantité</th>
            <th >Demandeur</th>
            <th >Demande d'achat</th>
            <th >Bon de Commande</th>
            <th >Réception</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <td>A342 411</td>
            <td>Gris</td>
            <td>11</td>
            <td>Ama Ouss</td>
            <td>la DA</td>
            <td>le BC</td>
            <td>le BR</td>
            </tr>
        </tbody>
    </table>        
    </div>
    <div class="da-container">
    <div class="da-overlay"></div>
    </div>
</div>