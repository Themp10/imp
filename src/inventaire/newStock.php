<?php



?>

<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>

<h1 class="page-title">Nouvelle Entrée</h1>
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
                    <button class="btn-switch" type="button" onclick="sendData()">Ajouter</button>
                    <button class="btn-switch" type="button" onclick="generateQR()">Générer QR Code</button>
                    <button class="btn-switch" type="button" onclick="printQR()">Imprimer</button>
                </div>
                <div class="qr-container" id="qr-container">
                    <div id="qr-code"></div>
                    <div class="qr-text" id="qr-text"></div>
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
                    <option value="en stock">En stock</option>
                    <option value="attribué">Attribué</option>
                    <option value="en réparation">En réparation</option>
                </select>
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Etat</legend>
                <select class ="item-select" name="etat" id="etat" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="en stock">Neuf</option>
                    <option value="attribué">Occasion</option>
                    <option value="en réparation">Endommagé</option>
                </select>
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Utilisateur</legend>
                <input type="text" name="utilisateur" id="utilisateur">
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Emplacement</legend>
                <input type="text" name="emplacement" id="emplacement">
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Valeur (MAD)</legend>
                <input type="number" name="valeur" id="valeur" step="0.01">
            </fieldset>    
            <fieldset class="item-box item-box-textarea">
                <legend class="filter-type-title">Commentaire</legend>
                <textarea name="commentaire" id="commentaire" rows="4"></textarea>
            </fieldset>    
        </div>
    </div>
</form>

<script>
    function viderForm(){

    }
    function formatData(){
        const errors =document.querySelectorAll('.error-item')
        let err=false;
        errors.forEach(element => {
            element.classList.remove('error-item');
        })
        let data=[]
        const code = document.getElementById(`code`).value;
        const type = document.getElementById(`type`).value;
        if(type==""){
                document.getElementById(`type`).classList.add("error-item")
                err=true;
            }
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
        const emplacement = document.getElementById(`emplacement`).value;
        const valeur = document.getElementById(`valeur`).value;
        const commentaire = document.getElementById(`commentaire`).value;
        data.push({
            code,
            type,
            designation,
            numero_serie,
            marque,
            modele,
            date_acquisition,
            statut,
            etat,
            utilisateur,
            emplacement,
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
            return
        }
        const payload = JSON.stringify({
            key: "new",
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
                }, 3000);

                generateQR()
                document.getElementById("qr-text").innerHTML=data.code
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

        console.log(dataToSend)
    }
</script>
<script>
    async function generateQR(){
        const qrCode = new QRCodeStyling({
        width: 150,
        height: 150,
        data: "https://172.28.0.22/imp/",
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
      alert("QR code not generated yet!");
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
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-size: 36px;
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
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <img src="${dataUrl}" alt="QR Code" />
            <div>${qrText}</div>
        </body>
        </html>
    `);
    printWindow.document.close();
    }
</script>