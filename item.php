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
        body,h1 {padding: 0;margin:0}
        p{
            margin:0;
            font-size: 30px;
            border:1px solid #547471;
            width: 70%;
            padding:5px;
            border-radius:10px;
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        }
        .header-banner{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header {
            text-align: center;
            padding: 10px 0;
            background-color: #547471;
            color: white;
            font-size:40px ;
            margin: 10px 0;
        }
        .item-details{
            display:flex;flex-direction:column;justify-content:center;align-items:center;gap:10px
        }
        #qr-code{display:flex;flex-direction:column;justify-content:center;align-items:center;margin-top:30px}
    </style>
</head>
<body>
    <div class="header-banner">
        <img src="/imp/assets/MG-logo.png" alt="Logo" width="300" height="150">  
    </div>
    <header>        
        <h1><?= htmlspecialchars($item['designation']) ?></h1>
    </header>
    <div class="item-details">
        <p><strong>Code:</strong> <?= htmlspecialchars($item['code']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($item['type']) ?></p>
        <p><strong>Numéro de Série:</strong> <?= htmlspecialchars($item['numero_serie']) ?></p>
        <p><strong>Marque:</strong> <?= htmlspecialchars($item['marque']) ?></p>
        <p><strong>Modèle:</strong> <?= htmlspecialchars($item['modele']) ?></p>
        <p><strong>Date d'acquisition:</strong> <?= htmlspecialchars($item['date_acquisition']) ?></p>
        <p><strong>Date d'affectation:</strong> <?= htmlspecialchars($item['date_affectation']) ?></p>
        <p><strong>Statut:</strong> <?= htmlspecialchars($item['statut']) ?></p>
        <p><strong>État:</strong> <?= htmlspecialchars($item['etat']) ?></p>
        <p><strong>Utilisateur:</strong> <?= htmlspecialchars($item['utilisateur']) ?></p>
        <p><strong>Emplacement:</strong> <?= htmlspecialchars($item['emplacement']) ?></p>
        <p><strong>Valeur:</strong> <?= htmlspecialchars($item['valeur']) ?> MAD</p>
        <p><strong>Commentaire:</strong><br><?= nl2br(htmlspecialchars($item['commentaire'])) ?></p>
    </div>
    <div id="qr-code"></div>
</body>
<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
<script>
        document.addEventListener("DOMContentLoaded", async function () {
        await generateQR();
        });
        async function generateQR(){

            const itemCode = "<?= htmlspecialchars($item['code']) ?>";
            const qrCode = new QRCodeStyling({
                width: 300,
                height: 300,
                data: "http://172.28.0.22/imp/item/"+itemCode,
                image: "/imp/assets/MG-logoSM.png", 
                dotsOptions: {
                    color: "#000",
                    type: "rounded"
                },
                backgroundOptions: {
                    color: "#fff"
                },
                imageOptions: {
                    crossOrigin: "anonymous",
                    margin: 1
                }
            });
            const qrContainer = document.getElementById("qr-code");
            qrContainer.innerHTML = "";
            const qrCanvas = await qrCode._canvas.getCanvas(); // Get canvas directly
            qrContainer.appendChild(qrCanvas);

        }
</script>
</html>
