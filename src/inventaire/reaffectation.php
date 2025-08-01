<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
<div id="reaffectation-modal" class="search-modal">
    <div class="search-container">
        <input type="text" name="reaffectation-item" id="reaffectation-item" placeholder="Chercher ...">
    </div>
    <div id="reaffectation-result" class="search-result">

    </div>
</div>

<h1 class="page-title">Réaffectation</h1>
<form action="insert_item.php" method="post" class="item-form">
    <div class="item-top-container">
        <fieldset class="item-box">
            <legend class="filter-type-title">Code</legend>
            <input type="text" name="codeR" id="codeR"  onclick="openReafModal()" disabled>
        </fieldset>
        <fieldset class="item-box">
            <legend class="filter-type-title">Désignation</legend>
            <input type="text" name="designationR" id="designationR" disabled>
        </fieldset>
        <fieldset class="item-box">
            <legend class="filter-type-title">Date de réaffectation</legend>
            <input type="date" name="date_reaffectation" id="date_reaffectation" required>
        </fieldset>
        
    </div>

    <div class="item-bottom-container">

        <div class="item-left-container">
            <div class="affectation-tittle">Ancien Affectation</div>
            <fieldset class="item-box">
                <legend class="filter-type-title">Statut</legend>
                <input type="text" name="Ostatut" id="Ostatut" disabled>
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Etat</legend>
                <input type="text" name="Oetat" id="Oetat" disabled>
            </fieldset>    

            <div class="user-container">
                <fieldset class="item-box">
                    <legend class="filter-type-title">Utilisateur</legend>
                    <input type="text" name="Outilisateur" id="Outilisateur" disabled>
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Poste</legend>
                    <input type="text" name="Oposte" id="Oposte" disabled>
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Direction</legend>
                    <input type="text" name="Odirection" id="Odirection" disabled>
                </fieldset> 
            </div>    

            <div class="user-container">
                <fieldset class="item-box">
                    <legend class="filter-type-title">Site</legend>
                    <input type="text" name="Osite" id="Osite" disabled>
                </fieldset>  
                <fieldset class="item-box">
                    <legend class="filter-type-title">Emplacement</legend>
                    <input type="text" name="Oemplacement" id="Oemplacement" disabled>
                </fieldset>  
            </div>  
            <div class="item-buttons">
                <button class="btn-switch" type="button" onclick="validerReaffectation()">Valider</button>
                <button class="btn-switch" type="button" onclick="openReafModal()">Recherche</button>
            </div>


        </div>
        <div class="item-mid-container">
            <img src="/imp/assets/right-arrow.png" alt="">
        </div>
        <div class="item-right-container">
            <div class="affectation-tittle">Nouvelle Affectation</div>
            <fieldset class="item-box">
                <legend class="filter-type-title">Statut</legend>
                <select class ="item-select" name="Nstatut" id="Nstatut" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="en stock">En stock</option>
                    <option value="attribué">Attribué</option>
                    <option value="en réparation">En réparation</option>
                    <option value="Inactif">Inactif</option>
                </select>
            </fieldset>    
            <fieldset class="item-box">
                <legend class="filter-type-title">Etat</legend>
                <select class ="item-select" name="Netat" id="Netat" required>
                    <option value="">-- Sélectionnez --</option>
                    <option value="Neuf">Neuf</option>
                    <option value="Occasion">Occasion</option>
                    <option value="Endommagé">Endommagé</option>
                </select>
            </fieldset>    
            <div class="user-container">
                <fieldset class="item-box">
                    <legend class="filter-type-title">Utilisateur</legend>
                    <input type="text" name="Nutilisateur" id="Nutilisateur">
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Poste</legend>
                    <input type="text" name="Nposte" id="Nposte">
                </fieldset> 
                <fieldset class="item-box">
                    <legend class="filter-type-title">Direction</legend>
                    <select class ="item-select" name="Ndirection" id="Ndirection" required>
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
                    <input type="text" name="Nsite" id="Nsite">
                </fieldset>  
                <fieldset class="item-box">
                    <legend class="filter-type-title">Emplacement</legend>
                        <select class ="item-select" name="Nemplacement" id="Nemplacement" required>
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
            <fieldset class="item-box item-box-textarea">
                <legend class="filter-type-title">Commentaire</legend>
                <textarea name="Rcommentaire" id="Rcommentaire" rows="4"></textarea>
            </fieldset>    
        </div>
    </div>
</form>

<script>

const reafInput = document.getElementById('reaffectation-item');
    let typingTimer2;
    reafInput.addEventListener('input', () => {
        clearTimeout(typingTimer2);
        typingTimer2 = setTimeout(() => {
            const item = reafInput.value.trim();
            if (item.length > 0) {
                searchItems2(item);
            } else {
                document.getElementById('reaffectation-result').innerHTML = '';
            }
        }, 300); // slight delay to avoid triggering on every keystroke
    });

    async function searchItems2(item) {
    const payload = JSON.stringify({
        key: "search2",
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
            document.getElementById('reaffectation-result').innerHTML = data.html;

        })

        
    } catch (error) {
        console.error('Search failed:', error);
    }
}
    async function selectSearchItem2(item) {
        let itemCode = item.getAttribute('item-code');
        closeReafModal();

        try {
            const url = `./src/inventaire/items.php?key=getItem&itemCode=${encodeURIComponent(itemCode)}`;
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            // Send data to form
            setDatainForm2(data.item);
        } catch (error) {
            console.error('Error fetching item data:', error);
        }
    }

    function setDatainForm2(item){
        document.getElementById(`codeR`).value=item.code
        document.getElementById(`designationR`).value=item.designation
        document.getElementById(`Ostatut`).value=item.statut
        document.getElementById(`Nstatut`).value=item.statut
        document.getElementById(`Oetat`).value=item.etat
        document.getElementById(`Netat`).value=item.etat
        document.getElementById(`Outilisateur`).value=item.utilisateur
        document.getElementById(`Oposte`).value=item.poste
        document.getElementById(`Odirection`).value=item.direction
        document.getElementById(`Nutilisateur`).value=item.utilisateur
        document.getElementById(`Nposte`).value=item.poste
        document.getElementById(`Ndirection`).value=item.direction
        document.getElementById(`Oemplacement`).value=item.emplacement
        document.getElementById(`Osite`).value=item.site
        document.getElementById(`Nemplacement`).value=item.emplacement
        document.getElementById(`Nsite`).value=item.site
    }


    function openReafModal() {
        document.getElementById('reaffectation-modal').style.display = 'flex';
    }
    function closeReafModal() {
        document.getElementById('reaffectation-modal').style.display = 'none';
    }

    function formatReafData(){
        const errors =document.querySelectorAll('.error-item')
        let err=false;
        errors.forEach(element => {
            element.classList.remove('error-item');
        })
        let data=[]
        const code = document.getElementById(`codeR`).value;
        const date_reaffectation = document.getElementById(`date_reaffectation`).value;
        if(date_reaffectation==""){
                document.getElementById(`date_reaffectation`).classList.add("error-item")
                err=true;
            }        
        const Nstatut = document.getElementById(`Nstatut`).value;
        const Netat = document.getElementById(`Netat`).value;
        const Nutilisateur = document.getElementById(`Nutilisateur`).value;
        const Nposte = document.getElementById(`Nposte`).value;
        const Ndirection = document.getElementById(`Ndirection`).value;
        const Nemplacement = document.getElementById(`Nemplacement`).value;
        const Nsite = document.getElementById(`Nsite`).value;
        const Ostatut = document.getElementById(`Ostatut`).value;
        const Oetat = document.getElementById(`Oetat`).value;
        const Outilisateur = document.getElementById(`Outilisateur`).value;
        const Oposte = document.getElementById(`Oposte`).value;
        const Odirection = document.getElementById(`Odirection`).value;
        const Oemplacement = document.getElementById(`Oemplacement`).value;
        const Osite = document.getElementById(`Osite`).value;
        const Rcommentaire = document.getElementById(`Rcommentaire`).value;
        data.push({
            code,
            date_reaffectation,
            Nstatut,
            Netat,
            Nutilisateur,
            Nposte,
            Ndirection,
            Nemplacement,
            Nsite,
            Ostatut,
            Oetat,
            Outilisateur,
            Odirection,
            Oposte,
            Oemplacement,
            Osite,
            Rcommentaire
            });
        if (err) {
            return "err"
        }
        return data
    }
    async function validerReaffectation(){
        let x = document.getElementById("snackbar");
        let dataToSend=formatReafData()
        if (dataToSend=="err") {
            x.innerHTML="Merci de renseigner les champs en rouge!"
            x.className = "show error-message";
            setTimeout(function(){ x.className = x.className.replace("show error-message", ""); }, 3000);
            return "err"
        }
        let key="reaffectation"

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
                }, 3000);
            }else{
                x.innerHTML="Problème survenu, Merci de contacter votre administrateur!"
                x.className = "show error-message";
                setTimeout(function(){ 
                    x.className = x.className.replace("show error-message", ""); 
                }, 3000);
            }
            document.getElementsByClassName("item-form")[1].reset()

        })
        .catch(error => {
            console.error('Error sending data:', error);
        });

    }
</script>