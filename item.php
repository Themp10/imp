<?php
// item.php

if (!isset($_GET['code'])) {
    echo "Code manquant.";
    exit;
}

$code = $_GET['code'];

// Connect to your DB
$mysqli = new mysqli("172.28.0.22", "sa", "MG+P@ssw0rd", "INV");

if ($mysqli->connect_error) {
    die("Connexion échouée: " . $mysqli->connect_error);
}

$stmt = $mysqli->prepare("SELECT * FROM items WHERE code = ?");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Aucun item trouvé avec le code: $code";
    exit;
}

$item = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'item <?= htmlspecialchars($item['code']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .item-details { border: 1px solid #ccc; padding: 15px; max-width: 600px; }
        .item-details h2 { margin-top: 0; }
        .item-details p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="item-details">
        <h2>Item: <?= htmlspecialchars($item['code']) ?></h2>
        <p><strong>Type:</strong> <?= htmlspecialchars($item['type']) ?></p>
        <p><strong>Désignation:</strong> <?= htmlspecialchars($item['designation']) ?></p>
        <p><strong>Numéro de Série:</strong> <?= htmlspecialchars($item['numero_serie']) ?></p>
        <p><strong>Marque:</strong> <?= htmlspecialchars($item['marque']) ?></p>
        <p><strong>Modèle:</strong> <?= htmlspecialchars($item['modele']) ?></p>
        <p><strong>Date d'acquisition:</strong> <?= htmlspecialchars($item['date_acquisition']) ?></p>
        <p><strong>Statut:</strong> <?= htmlspecialchars($item['statut']) ?></p>
        <p><strong>État:</strong> <?= htmlspecialchars($item['etat']) ?></p>
        <p><strong>Utilisateur:</strong> <?= htmlspecialchars($item['utilisateur']) ?></p>
        <p><strong>Emplacement:</strong> <?= htmlspecialchars($item['emplacement']) ?></p>
        <p><strong>Valeur:</strong> <?= htmlspecialchars($item['valeur']) ?> MAD</p>
        <p><strong>Commentaire:</strong><br><?= nl2br(htmlspecialchars($item['commentaire'])) ?></p>
    </div>
</body>
</html>
