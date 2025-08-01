<?php



?>

<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
<div id="search-modal" class="search-modal">
    <div class="search-container">
        <input type="text" name="search-item" id="search-item" placeholder="Chercher ...">
    </div>
    <div id="search-result" class="search-result">

    </div>
</div>
<h1 class="page-title">Nouveau matériel IT</h1>
<form action="insert_item.php" method="post" class="item-form">
    <div class="item-top-container">
        <fieldset class="item-box">
            <legend class="filter-type-title">Type</legend>
            <select class ="item-select" name="type" id="type" required>
                <option value="">-- Sélectionnez --</option>
                <option value="IT">Matériel IT</option>
                <option value="IM">Immobilisation</option>
            </select>
        </fieldset>
        <fieldset class="item-box">
            <legend class="filter-type-title">Code</legend>
            <input type="text" name="code" id="code" disabled>
        </fieldset>
    </div>

    <div class="item-bottom-container">

        <div class="item-left-container">
            <fieldset class="item-box">
                <legend class="filter-type-title">Date d'affectation</legend>
                <input type="date" name="date_affectation" id="date_affectation" required>
            </fieldset>
            <fieldset class="item-box">
                <legend class="filter-type-title">Désignation</legend>
                <input type="text" name="designation" id="designation" required>
            </fieldset>
            <fieldset class="item-box">
                <legend class="filter-type-title">Numéro de Série</legend>
                <input type="text" name="numero_serie" id="numero_serie" required>
            </fieldset>
            <fieldset class="item-box">
                <legend class="filter-type-title">Marque</legend>
                <input type="text" name="marque" id="marque">
            </fieldset>
            <fieldset class="item-box">
                <legend class="filter-type-title">Modèle</legend>
                <input type="text" name="modele" id="modele">
            </fieldset>

            <fieldset class="item-box item-box-buttons">
                <legend class="filter-type-title">Validation</legend>
                <div class="item-buttons">
                    <button id="save-item-btn" class="btn-switch" type="button" onclick="sendData()">Créer</button>
                    <button class="btn-switch" type="button" onclick="printQR()">Imprimer</button>
                    <button class="btn-switch" type="button" onclick="openSearchModal()">Rechercher</button>
                    <button class="btn-switch" type="button" onclick="vider()">Annuler</button>
                </div>
                <div class="qr-container" id="qr-container">
                    <div id="qr-code"></div>
                    <div class="qr-text-container">
                        <div class="qr-text" id="qr-text"></div>
                        <div class="qr-text" id="qr-text2">Groupe Mfadel</div>
                    </div>

                </div>
            </fieldset>
        </div>
        <div class="item-right-container">
            <fieldset class="item-box">
                <legend class="filter-type-title">Date d'acquisition</legend>
                <input type="date" name="date_acquisition" id="date_acquisition" required>
            </fieldset>

            <fieldset class="item-box">
                <legend class="filter-type-title">Statut</legend>
                <select class ="item-select" name="statut" id="statut" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="En stock">En stock</option>
                    <option value="Attribué">Attribué</option>
                    <option value="En réparation">En réparation</option>
                    <option value="Inactif">Inactif</option>
                </select>
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Etat</legend>
                <select class ="item-select" name="etat" id="etat" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="Neuf">Neuf</option>
                    <option value="Occasion">Occasion</option>
                    <option value="Endommagé">Endommagé</option>
                </select>
            </fieldset>    
            <div class="user-container">
                <fieldset class="item-box">
                    <legend class="filter-type-title">Utilisateur</legend>
                    <input type="text" name="utilisateur" id="utilisateur">
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Poste</legend>
                    <input type="text" name="poste" id="poste">
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Direction</legend>
                    <select class ="item-select" name="direction" id="direction" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="DG">DG</option>
                        <option value="DOSI">DOSI</option>
                        <option value="BU FO">BU FO</option>
                        <option value="BU PI">BU PI</option>
                        <option value="DAF">DAF</option>
                        <option value="DT">DT</option>
                        <option value="RH & MG">RH & MG</option>
                    </select>
                </fieldset> 
            </div>
            <div class="user-container">
                <fieldset class="item-box">
                    <legend class="filter-type-title">Site</legend>
                    <input type="text" name="site" id="site">
                </fieldset>  
                <fieldset class="item-box">
                    <legend class="filter-type-title">Emplacement</legend>
                        <select class ="item-select" name="emplacement" id="emplacement" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Plateau Direction">Plateau Direction</option>
                            <option value="Plateau Support">Plateau Support</option>
                            <option value="Accueil">Accueil</option>
                            <option value="BV KPC">BV KPC</option>
                            <option value="BV CP">BV CP</option>
                            <option value="BV UP">BV UP</option>
                            <option value="BV ZENATA">BV ZENATA</option>
                            <option value="BV BELAIR">BV BELAIR</option>
                            <option value="Ecole Primaire">Ecole Primaire</option>
                            <option value="Ecole Collège">Ecole Collège</option>
                            <option value="Ecole Lycée">Ecole Lycée</option>
                        </select>
                </fieldset>  
            </div>  
            <fieldset class="item-box" >
                <legend class="filter-type-title">Valeur (MAD)</legend>
                <input type="number" name="valeur" id="valeur" step="100" value="0" >
            </fieldset>    
            <fieldset class="item-box item-box-textarea">
                <legend class="filter-type-title">Commentaire</legend>
                <textarea name="commentaire" id="commentaire" rows="4"></textarea>
            </fieldset>    
        </div>
    </div>
</form>

<script>

    const searchInput = document.getElementById('search-item');
    let typingTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            const item = searchInput.value.trim();
            if (item.length > 0) {
            searchItems(item);
            console.log(item)

            } else {
                document.getElementById('search-result').innerHTML = '';
            }
        }, 300); // slight delay to avoid triggering on every keystroke
    });

    async function searchItems(item) {
    const payload = JSON.stringify({
        key: "search",
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
            document.getElementById('search-result').innerHTML = data.html;
            
        })

        
    } catch (error) {
        console.error('Search failed:', error);
    }
}
    async function selectSearchItem(item) {
        let itemCode = item.getAttribute('item-code');
        closeSearchModal();
        generateQR(itemCode)
        document.getElementById("qr-text").innerHTML=itemCode
        try {
            const url = `./src/inventaire/items.php?key=getItem&itemCode=${encodeURIComponent(itemCode)}`;
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            // Send data to form
            setDatainForm(data.item);
            document.getElementById('save-item-btn').textContent="Modifier"
        } catch (error) {
            console.error('Error fetching item data:', error);
        }
    }

    function setDatainForm(item){
        document.getElementById(`code`).value=item.code
        document.getElementById(`type`).value=item.type
        document.getElementById(`designation`).value=item.designation
        document.getElementById(`numero_serie`).value=item.numero_serie
        document.getElementById(`date_acquisition`).value=item.date_acquisition
        document.getElementById(`date_affectation`).value=item.date_affectation
        document.getElementById(`statut`).value=item.statut
        document.getElementById(`etat`).value=item.etat
        document.getElementById(`utilisateur`).value=item.utilisateur
        document.getElementById(`emplacement`).value=item.emplacement
        document.getElementById(`valeur`).value=item.valeur
        document.getElementById(`commentaire`).value=item.commentaire
        document.getElementById(`marque`).value=item.marque
        document.getElementById(`modele`).value=item.modele
        document.getElementById(`direction`).value=item.direction
        document.getElementById(`poste`).value=item.poste
        document.getElementById(`site`).value=item.site
    }
    function openSearchModal() {
        document.getElementById('search-modal').style.display = 'flex';
    }
    function closeSearchModal() {
        document.getElementById('search-modal').style.display = 'none';
    }
    function formatData(){
        const errors =document.querySelectorAll('.error-item')
        let err=false;
        errors.forEach(element => {
            element.classList.remove('error-item');
        })
        let data=[]
        const code = document.getElementById(`code`).value;
        // const type = document.getElementById(`type`).value;
        // if(type==""){
        //         document.getElementById(`type`).classList.add("error-item")
        //         err=true;
        //     }
        const type = "IT";
        const designation = document.getElementById(`designation`).value;
        if(designation==""){
                document.getElementById(`designation`).classList.add("error-item")
                err=true;
            }
        const numero_serie = document.getElementById(`numero_serie`).value;
        const marque = document.getElementById(`marque`).value;
        const modele = document.getElementById(`modele`).value;
        const date_acquisition = document.getElementById(`date_acquisition`).value;
        if(date_acquisition==""){
                document.getElementById(`date_acquisition`).classList.add("error-item")
                err=true;
            }
        const date_affectation = document.getElementById(`date_affectation`).value;
        if(date_affectation==""){
                document.getElementById(`date_affectation`).classList.add("error-item")
                err=true;
            }        
        const statut = document.getElementById(`statut`).value;
        if(statut==""){
                document.getElementById(`statut`).classList.add("error-item")
                err=true;
            }
        const etat = document.getElementById(`etat`).value;
        if(etat==""){
                document.getElementById(`etat`).classList.add("error-item")
                err=true;
            }
        const utilisateur = document.getElementById(`utilisateur`).value;
        const direction = document.getElementById(`direction`).value;
        const poste = document.getElementById(`poste`).value;
        const emplacement = document.getElementById(`emplacement`).value;
        const site = document.getElementById(`site`).value;
        const valeur = document.getElementById(`valeur`).value;
        const commentaire = document.getElementById(`commentaire`).value;
        data.push({
            code,
            type,
            designation,
            numero_serie,
            marque,
            modele,
            date_affectation,
            date_acquisition,
            statut,
            etat,
            utilisateur,
            direction,
            poste,
            emplacement,
            site,
            valeur,
            commentaire
            });
        if (err) {
            return "err"
        }
        return data
    }
    
    async function sendData(){
        let x = document.getElementById("snackbar");
        let dataToSend=formatData()
        if (dataToSend=="err") {
            x.innerHTML="Merci de renseigner les champs en rouge!"
            x.className = "show error-message";
            setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
            return "err"
        }
        let key="new"
        if(document.getElementById('code').value==""){
            key="new"
        }else{
            key="update"
        }
        const payload = JSON.stringify({
            key: key,
            data: dataToSend
        });
        await fetch('./src/inventaire/items.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: payload
        })
        .then(response => response.json())
        .then(data => {
            if (data.status="success"){
                x.innerHTML="Données enregistrées avec succés!"
                x.className = "show success-message";
                setTimeout(function(){ 
                    x.className = x.className.replace("show success-message", ""); 
                    document.getElementsByClassName("item-form")[0].reset()
                    document.getElementById('save-item-btn').textContent="Créer"
                }, 3000);

                generateQR(data.code)
                document.getElementById("qr-text").innerHTML=data.code
                document.getElementById("code").innerHTML=data.code
            }else{
                x.innerHTML="Problème survenu, Merci de contacter votre administrateur!"
                x.className = "show error-message";
                setTimeout(function(){ 
                    x.className = x.className.replace("show error-message", ""); 
                    document.getElementsByClassName("item-form")[0].reset()
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error sending data:', error);
        });

    }
</script>
<script>
    async function generateQR(itemCode){
        const qrCode = new QRCodeStyling({
        width: 150,
        height: 150,
        data: "http://172.28.0.22/imp/item/"+itemCode,
        image: "./assets/MG-logoSM.png", 
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
    async function printQR() {
    const qrContainer = document.getElementById("qr-container");
    const canvas = qrContainer.querySelector("canvas");

    if (!canvas) {
        alert("QR code non généré!");
        return;
    }

    const dataUrl = canvas.toDataURL("image/png");
    const qrText = qrContainer.querySelector(".qr-text")?.innerText || "";

    const printWindow = window.open('', '', 'width=300,height=400');
    printWindow.document.write(`
        <html>
        <head>
            <style>
            @media print {
                body {
                margin: 0;
                padding: 0;
                }
            }
            body {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                font-family: monospace;
                margin: 0;
                padding: 0;
            }
            img {
                width: 150px;
                height: 150px;
                display: block;
                margin-bottom: 4px;
            }
            div{
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .gmtaille{
                font-size: 20px;    
            }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <img src="${dataUrl}" alt="QR Code" />
            <div>
                <div>${qrText}</div>
                <center><div class="gmtaille">Groupe Mfadel</div></center>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    }


    function vider(){
        document.getElementsByClassName("item-form")[0].reset()
        document.getElementById(`qr-code`).innerHTML=""
        document.getElementById("qr-text").innerHTML=""
        document.getElementById('save-item-btn').textContent="Créer"
    }

</script>