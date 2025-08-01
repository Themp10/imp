<?php
$mysqli = new mysqli("172.28.0.22", "sa", "MG+P@ssw0rd", "INV");

// Check connection
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// Query
$sql = "SELECT CODE, utilisateur,designation, marque, modele, numero_serie 
        FROM items ";
        //WHERE CODE IN (SELECT DISTINCT CODE FROM reaffectation)";
$result = $mysqli->query($sql);
?>
<h1 class="page-title">Mouvement Stock</h1>
<input type="text" id="filterMvt" class="itemsearch" placeholder="Tapez pour filtrer..." oninput="filterMvtList()">
<div class="mvt-container">
    <div class="mvt-container-left">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="mvt-item-card" onclick="getHistory(this)">
                    <p>Code : <span><?= htmlspecialchars($row['CODE']); ?></span></p>
                    <p>Utilisateur : <span><?= htmlspecialchars($row['utilisateur']); ?></span></p>
                    <p>Désignation : <span><?= htmlspecialchars($row['designation']); ?></span></p>
                    <p>Marque : <span><?= htmlspecialchars($row['marque']); ?></span></p>
                    <p>Modèle : <span><?= htmlspecialchars($row['modele']); ?></span></p>
                    <p>N° Série : <span><?= htmlspecialchars($row['numero_serie']); ?></span></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>
    
    <div class="mvt-container-right" id="mvt-container-history">
        

    </div>
</div>
<script>
async function getHistory(card) {
    let item=card.children[0].children[0].innerHTML
    const payload = JSON.stringify({
        key: "getHistory",
        data: item
    });

    try {
        const response = await fetch('./src/inventaire/items.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: payload
        }).then(response => response.json())
        .then(data => {
            document.getElementById("mvt-container-history").innerHTML = data.html;
        })

        

    } catch (error) {
        console.error('Search failed:', error);
        document.getElementById("mvt-container-history").innerHTML =
            "<p>Erreur lors du chargement de l'historique.</p>";
    }
}


function filterMvtList() {
    const input = document.getElementById('filterMvt').value.toLowerCase();
    const cards = document.querySelectorAll('.mvt-item-card');

    cards.forEach(card => {
        // Get all the text inside this card
        const cardText = card.innerText.toLowerCase();

        // Show if any text in the card matches the search input
        card.style.display = cardText.includes(input) ? '' : 'none';
    });
}
</script>
<?php $mysqli->close(); ?>