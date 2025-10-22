<?php
$servername = "172.28.0.22";
$username = "sa";
$password = "MG+P@ssw0rd";
$dbname = "INV";
$listconn = new mysqli($servername, $username, $password, $dbname);
if ($listconn->connect_error) {
    die("Connection failed: " . $listconn->connect_error);
}
$sql = "SELECT * FROM societe";
$soclist = $listconn->query($sql);
?>
<script src="https://unpkg.com/docx@7.1.0/build/index.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.js"></script>

<h1 class="page-title">Décharge matériel</h1>
<button class="btn-switch" type="button" onclick="getDecharges()">Actualiser</button>



<div class="mvt-container">
    <div class="dech-container-left">
        <h2 class="page-title">Décharges à créer</h2>
        <div class="decharge-container" id="dec-to-create">
            <p class="text-italic">Aucune décharge à traiter</p>
        </div>
        <h2 class="page-title">Décharges à compléter</h2>
        <div class="decharge-container" id="dec-to-complete">
            <p class="text-italic">Aucune décharge à traiter</p>
        </div>
        <h2 class="page-title">Décharges à imprimer</h2>
        <div class="decharge-container" id="dec-to-print">
            <p class="text-italic">Aucune décharge à traiter</p>
        </div>
    </div>
    <div class="dech-container-right">
        <form class="form-dech" id="dech-container-form">
                <input type="text" name="did" id="did" hidden>
                <input type="text" name="dtype" id="dtype" hidden>
                <input type="text" name="dserie" id="dserie" hidden>
                <input type="text" name="detat" id="detat" hidden>
                <div class="user-container">
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Code</legend>
                        <input type="text" name="dcode" id="dcode" disabled>
                    </fieldset>
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Materiel</legend>
                        <input type="text" name="dmateriel" id="dmateriel" <?= $profile === 'admin' ? '' : 'disabled' ?>>
                    </fieldset> 
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Description</legend>
                        <input type="text" name="ddescription" id="ddescription" <?= $profile === 'admin' ? '' : 'disabled' ?>>
                    </fieldset> 
                </div>          
                <div class="user-container">
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Societe</legend>
                        <select class ="item-select" name="dsoc" id="dsoc" <?= $profile === 'rh' ? '' : 'disabled' ?>>
                            <option value="">-- Sélectionnez --</option>
                        <?php while ($row = $soclist->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['id']) ?>" adresse="<?= htmlspecialchars($row['adresse']) ?>" ville="<?= htmlspecialchars($row['ville']) ?>"><?= htmlspecialchars($row['nom']) ?></option>
                        <?php endwhile; ?>
                        </select>
                    </fieldset>
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Utilisateur</legend>
                        <input type="text" name="duser" id="duser">
                    </fieldset> 
                    <fieldset class="item-box">
                        <legend class="filter-type-title">Poste</legend>
                        <input type="text" name="dposte" id="dposte" <?= $profile === 'rh' ? '' : 'disabled' ?>>
                    </fieldset> 
                </div>   
                <fieldset class="item-box">
                    <legend class="filter-type-title">Adresse</legend>
                    <input type="text" name="dadresse" id="dadresse" <?= $profile === 'rh' ? '' : 'disabled' ?>>
                </fieldset> 
        </form>
<button class="btn-switch" type="button" onclick="validerDecharge()" id="val-dech-btn" hidden>Valider</button>
<button class="btn-switch" type="button" onclick="printDecharge()" id="print-dech-btn" hidden>Imprimer</button>

    </div>                    

</div>

<button class="btn-switch" type="button" onclick="printDecharge()" >Imprimer</button>


<script>
async function printDecharge(){
    let x = document.getElementById("snackbar");
    let id=document.getElementById("did").value
    let code=document.getElementById("dcode").value
    let data=[]
    data.push({
        id,
        code
        });
    const payload = JSON.stringify({
        key: "printDecharge",
        data: data
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
            const decharge = {
                societe: document.getElementById("dsoc").options[document.getElementById("dsoc").selectedIndex].text,
                socAdresse: document.getElementById("dsoc").options[document.getElementById("dsoc").selectedIndex].getAttribute('adresse'),
                socVille: document.getElementById("dsoc").options[document.getElementById("dsoc").selectedIndex].getAttribute('ville'),
                user: document.getElementById('duser').value,
                userPoste: document.getElementById('dposte').value,
                userAdresse: document.getElementById('dadresse').value,
                materiel: document.getElementById('dmateriel').value,
                code: document.getElementById('dcode').value,
                serie: document.getElementById('dserie').value,
                etat: document.getElementById('detat').value,
                description: document.getElementById('ddescription').value,
                date: data.html
            };
            imprimerDecharge(decharge)
            x.innerHTML="Décharge imprimée avec succés!"
            x.className = "show success-message";
            setTimeout(function(){ x.className = x.className.replace("show success-message", ""); }, 3000);
        })
    } catch (error) {
        console.error('Search failed:', error);
    }
}
function imprimerDecharge(decharge) {
    const doc = new docx.Document({
        styles: {
            default: {
                document: {
                    run: {
                        font: "Garamond",
                        size: 24, // 12pt
                    },
                    paragraph: {
                        spacing: {
                            after: 120,
                        },
                    },
                },
            },
        },
        sections: [
            {
                children: [
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: "Objet : Lettre de décharge pour la remise du matériel professionnel",
                                bold: true,
                                underline: { type: "single" },
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.societe,
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.socAdresse,
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.socVille,
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph("À l'attention de :"),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.user.toUpperCase(),
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.userPoste.toUpperCase(),
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.userAdresse.toUpperCase(),
                                bold: true,
                            }),
                        ],
                    }),                
                    new docx.Paragraph(""),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun("Par la présente, nous vous confirmons la remise du matériel professionnel mis à votre disposition dans le cadre de votre contrat de travail au sein de "),
                            new docx.TextRun({
                                text: decharge.societe,
                                bold: true,
                            }),
                            new docx.TextRun("."),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: "Matériel remis : ",
                                bold: true,
                            }),
                            new docx.TextRun(decharge.materiel),
                        ],
                    }),
                    new docx.Paragraph({
                        bullet: { level: 0 },
                        children: [
                            new docx.TextRun({
                                text: "N° Inventaire : ",
                                bold: true,
                            }),
                            new docx.TextRun(decharge.code),
                        ],
                    }),
                    new docx.Paragraph({
                        bullet: { level: 0 },
                        children: [
                            new docx.TextRun({
                                text: "N° Série : ",
                                bold: true,
                            }),
                            new docx.TextRun(decharge.serie),
                        ],
                    }),
                    new docx.Paragraph({
                        bullet: { level: 0 },
                        children: [
                            new docx.TextRun({
                                text: "Etat  : ",
                                bold: true,
                            }),
                            new docx.TextRun(decharge.etat),
                        ],
                    }),
                    new docx.Paragraph({
                        bullet: { level: 0 },
                        children: [
                            new docx.TextRun({
                                text: "Description  : ",
                                bold: true,
                            }),
                            new docx.TextRun(decharge.description),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun("(le « "),
                            new docx.TextRun({
                                text: "Bien",
                                bold: true,
                            }),
                            new docx.TextRun(" »)"),
                        ],
                    }),
                    new docx.Paragraph({
                        alignment: docx.AlignmentType.JUSTIFIED,
                        children: [
                            new docx.TextRun("Vous êtes responsable de ce matériel pendant toute la durée de votre emploi au sein de "),
                            new docx.TextRun({
                                text: decharge.societe,
                                bold: true,
                            }),
                            new docx.TextRun(". Il doit être utilisé dans le cadre strict de vos fonctions professionnelles. En cas de perte, vol, ou dommage, nous vous demandons de nous en informer immédiatement. Tout manquement pourrait entraîner une facturation ou une retenue sur votre solde de tout compte, conformément à notre politique interne."),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun("En signant cette lettre de décharge, vous reconnaissez avoir reçu le Bien cité ci-dessus de la part de "),
                            new docx.TextRun({
                                text: "service IT ",
                                bold: true,
                            }),
                            new docx.TextRun("Le "),
                            new docx.TextRun({
                                text: decharge.date+".",
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph("Veuillez agréer, Madame, l'expression de nos salutations distinguées."),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: "Service IT",
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph("[Signature]"),
                    new docx.Paragraph(""),
                    new docx.Paragraph(""),
                    new docx.Paragraph(""),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: "Accusé de réception du salarié :",
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun("Je soussigné(e), "),
                            new docx.TextRun({
                                text: decharge.user,
                                bold: true,
                            }),
                            new docx.TextRun(" , atteste avoir reçu l’ensemble du matériel listé ci-dessus de "), 
                            new docx.TextRun({
                                text: "service IT",
                                bold: true,
                            }),
                            new docx.TextRun(" en date du "), 
                            new docx.TextRun({
                                text: decharge.date+".",
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph({
                        children: [
                            new docx.TextRun({
                                text: decharge.user,
                                bold: true,
                            }),
                        ],
                    }),
                    new docx.Paragraph("[Signature du salarié]"),
                ],
            },
        ],
    });


    docx.Packer.toBlob(doc).then((blob) => {
        console.log(blob);
        saveAs(blob, decharge.user+" "+decharge.materiel+".docx");
        console.log("Document created successfully");
    });
}
//dadza
async function getDecharges(){
    const payload = JSON.stringify({
        key: "getDecharge",
        data: [0]
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
            document.getElementById("dec-to-create").innerHTML = data.html.htmlToCreate;
            document.getElementById("dec-to-complete").innerHTML = data.html.htmlToComplete;
            document.getElementById("dec-to-print").innerHTML = data.html.htmlToPrint;
        })

        

    } catch (error) {
        console.error('Search failed:', error);

    }
}

async function getDechargeDetails(id,type){
    document.getElementById("did").value=id
    document.getElementById("dtype").value=type
    const valButton = document.getElementById('val-dech-btn');
    const printButton = document.getElementById('print-dech-btn');
    let profile="<?php echo $profile ;?>"
    if((type=='IT' && profile=='admin') || (type=='RH' && profile=='rh')){
        valButton.style.display = 'inline-block';
    }else{
        valButton.style.display = 'none';
    }
    if(type=='IMP' && profile=='admin'){
        printButton.style.display = 'inline-block';
    }else{
        printButton.style.display = 'none';
    }
    const payload = JSON.stringify({
        key: "getDechargeDetail",
        data: [id]
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
            setDechargeData(data.html)
        })

        

    } catch (error) {
        console.error('Search failed:', error);

    }
}
function setDechargeData(data){
    document.getElementById("dcode").value=data.code
    document.getElementById("dmateriel").value=data.materiel
    document.getElementById("ddescription").value=data.description
    document.getElementById("dsoc").value=data.societe
    document.getElementById("duser").value=data.user
    document.getElementById("dposte").value=data.poste_user
    document.getElementById("dadresse").value=data.adresse_user
    document.getElementById("dserie").value=data.numero_serie
    document.getElementById("detat").value=data.etat
}

async function validerDecharge(){
    let x = document.getElementById("snackbar");
    let code = document.getElementById("dcode").value
    if (code=="") {
        x.innerHTML="Merci de choisir une décharge avant de valider"
        x.className = "show error-message";
        setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
        return
    }
    let data=[]
    const errors =document.querySelectorAll('.error-item')
    let err=false;
    errors.forEach(element => {
            element.classList.remove('error-item');
    })
    let id=document.getElementById("did").value
    let type=document.getElementById("dtype").value
    let key=''
    if(type=='IT'){
        let materiel=document.getElementById("dmateriel").value
        if(materiel==""){
            document.getElementById(`dmateriel`).classList.add("error-item")
            err=true;
        }
        let description=document.getElementById("ddescription").value
        if(description==""){
            document.getElementById(`ddescription`).classList.add("error-item")
            err=true;
        }
        let user=document.getElementById("duser").value
        if(user==""){
            document.getElementById(`duser`).classList.add("error-item")
            err=true;
        }
        if (err) {
            x.innerHTML="Merci de renseigner les champs en rouge!"
            x.className = "show error-message";
            setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
            return
        }
        key="updateDechargeIT"
        data.push({
                id,
                materiel,
                description,
                user
                });
    }else if(type=='RH'){
        let societe=document.getElementById("dsoc").value
        if(societe==""){
            document.getElementById(`dsoc`).classList.add("error-item")
            err=true;
        }
        let poste_user=document.getElementById("dposte").value
        if(poste_user==""){
            document.getElementById(`dposte`).classList.add("error-item")
            err=true;
        }
        let user=document.getElementById("duser").value
        if(user==""){
            document.getElementById(`duser`).classList.add("error-item")
            err=true;
        }
        let adresse_user=document.getElementById("dadresse").value
        if(adresse_user==""){
            document.getElementById(`dadresse`).classList.add("error-item")
            err=true;
        }
        if (err) {
            x.innerHTML="Merci de renseigner les champs en rouge!"
            x.className = "show error-message";
            setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
            return
        }
        key="updateDechargeRH"
        data.push({
                id,
                societe,
                poste_user,
                user,
                adresse_user
                });
    }
    const payload = JSON.stringify({
        key: key,
        data: data
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
            x.innerHTML="Décharge enregistrée avec succés!"
            x.className = "show success-message";
            setTimeout(function(){ x.className = x.className.replace("show success-message", ""); }, 3000);
            getDecharges()
            document.getElementById("dech-container-form").reset()
        })

        

    } catch (error) {
        console.error('Search failed:', error);

    }





}


    </script>