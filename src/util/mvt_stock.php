<?php


// Gerer le mouvement entrée stock
function entreeStock($cartridgeId, $user, $quantity) {
    global $conn;

    // toujours pour eviter les injections sql 
    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);
    $user = mysqli_real_escape_string($conn, $user);
    $quantity = mysqli_real_escape_string($conn, $quantity);
    // récupérer le stock actuel
    $currentStock=getCurrentStock($cartridgeId);

    $type = 'e';//e pour entrée de stock
    // formater la date
    $mvtDate = date("Y-m-d");

    // database insertion
    $sql = "INSERT INTO mouvements (id_cartridge, user, qte,stock_apres, type, mvt_date) 
            VALUES ('$cartridgeId', '$user', '$quantity','$currentStock', '$type', '$mvtDate')";
    // vérifier si l'execution sql c'est bien passsée
    if ($conn->query($sql) === TRUE) {
        return "Mouvement entrée enregistré avec succés .";
    } else {
        return "Erreur mise à jour stock " . $conn->error;
    }
}

// Gerer le mouvement sortie stock
function sortieStock($cartridgeId, $user, $quantity) {
    global $conn;

    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);
    $user = mysqli_real_escape_string($conn, $user);
    $quantity = mysqli_real_escape_string($conn, $quantity);

    // s pour sortie
    $type = 's';

    // formater la date
    $mvtDate = date("Y-m-d");

    // récupérer le stock actuel
    $currentStock = getCurrentStock($cartridgeId);

    $sql = "INSERT INTO mouvements(id_cartridge, user, qte,stock_apres, type, mvt_date) 
            VALUES ('$cartridgeId', '$user', '$quantity','$currentStock', '$type', '$mvtDate')";

    // vérifier si l'execution sql c'est bien passsée
    if ($conn->query($sql) === TRUE) {
        return "Mouvement sortie enregistré avec succés .";
    } else {
        return "Erreur mise à jour stock " . $conn->error;
    }
} 

// Fonction pour récuprer le stock actuel
function getCurrentStock($cartridgeId) {
    global $conn;

   
    $cartridgeId = mysqli_real_escape_string($conn, $cartridgeId);

   
    $stockQuery = "SELECT stock FROM cartridges WHERE id = '$cartridgeId'";
    $stockResult = $conn->query($stockQuery);

    if ($stockResult && $stockResult->num_rows > 0) {
        $stockData = $stockResult->fetch_assoc();
        return $stockData['stock'];
    } else {
        return "erreur lors de la récupération du stock : " . $conn->error;
    }
}


?>
