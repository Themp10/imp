<?php




?>



<H1>Décharge Matériel</H1>
<div class="dech-header">
    <fieldset class="filter-box">
            <legend class="filter-type-title">Utilisateur</legend>
            <input type="text" id="decharge-user" name="decharge-user"  class="decharge-input">
        </fieldset>
    <fieldset class="filter-box">
        <legend class="filter-type-title">Date Remise</legend>
        <input type="date" class="select-filter" id="decharge-date"  name="decharge-date" >
    </fieldset>
    <fieldset class="filter-box">
        <legend class="filter-type-title">  Affectation  </legend>
        <select id="decharge-affectation" class="select-filter" name="decharge-affectation">
            <option value="new">Nouvelle affectation</option>
            <option value="replace">Remplacement</option>
        </select>
    </fieldset>
    <fieldset class="filter-box">
        <legend class="filter-type-title wider">Condition de transfert</legend>
        <input type="text" id="decharge-cond" name="decharge-cond"  class="decharge-input">
    </fieldset>
</div>

<h2>Matériel</h2>
<div class="decharge-add-del">
    <i id="decharge-add" class="fa-solid fa-circle-plus fa-2xl decharge-icon" style="color: #63E6BE;" onclick="handleIconclick(event)"></i>
    <i id="decharge-del" class="fa-solid fa-circle-minus fa-2xl  decharge-icon" style="color: #f50a2d;" onclick="handleIconclick(event)"></i>
</div>

<div class="materiel-container">
    <div id="mat-row-container-1" class="mat-row-container">
        <input type="text" id="decharge-desi" placeholder="Désignation" class="materiel-input">
        <input type="text" id="decharge-ninv" placeholder="N° Inventaire" class="materiel-input">
        <input type="text" id="decharge-nser" placeholder="N° Série" class="materiel-input">
        <select id="decharge-etat-1" class="materiel-input">
            <option value="new">Neuf</option>
            <option value="old">Occasion</option>
        </select>
        <input type="text" id="decharge-desc" placeholder="Description" class="materiel-input">
    </div>
    
</div>
<button type="button"  class="users-btn" onclick="validerDecharge()">Valider</button>

<script>
function handleIconclick(event) {
    let action = event.target.id.split("-")[1];
    let container = document.querySelector('.materiel-container');
    let rows = container.getElementsByClassName('mat-row-container');
    let nbItems = rows.length;
    if (action === "add") {
        let newRow = rows[nbItems - 1].cloneNode(true);
        newRow.id = `mat-row-container-${nbItems + 1}`;
        let inputs = newRow.querySelectorAll('input');
        inputs.forEach(input => {
            let baseId = input.id.split('-');
            baseId[2] = nbItems + 1; 
            input.id = baseId.join('-');
        });

        let select = newRow.querySelector('select');
        if (select) {
            let baseId = select.id.split('-');
            baseId[3] = nbItems + 1;
            select.id = baseId.join('-');
        }
        container.appendChild(newRow);
    } else if (action === "del") {
        if (nbItems > 1) {
            container.removeChild(rows[nbItems - 1]);
        }
    }
}

function validerDecharge(){
    let data=""
    let user=document.getElementById('decharge-user').value;
    if(user=="")alert("Merci de saisir le Demandeur !")

    let user=document.getElementById('decharge-date').value;
    if(user=="")alert("Merci de saisir la Date de remise !")

    let itemsList=document.getElementsByClassName('materiel-container')
    itemsList.forEach(itemRow => {
        itemRow.children.forEach( element=> {
            console.log(element)
    }); 
    }); 
}
</script>