<?php
include "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";
include "src". DIRECTORY_SEPARATOR ."util".DIRECTORY_SEPARATOR ."stats.php";
// Example usage:
$jsonData = fetchAllStatistics();
function get_years(){ 
  $sql="SELECT distinct YEAR(mvt_date) as year FROM mouvements  where type='s'";
  $years=sql_from_imp($sql);
  return $years;
}
?>


<div class="sortie-stock-header">
    <h2>Tableau de Bord </h2>
</div>

<div class="tbd-container">
    <div class="card-row">
        <div class="card-item">
            <div class="card-title">Nombre de Toner</div>
            <div class="card-data" id="nb-toner">13</div>
        </div>
        <div class="card-item">
            <div class="card-title">Nombre de Cartouche</div>
            <div class="card-data" id="nb-cartouche">24</div>
        </div>
        <div class="card-item">
            <div class="card-title">DA en Cours</div>
            <div class="card-data" id="da-en-cours">10</div>
        </div>
        <div class="card-item">
            <div class="card-title">DA Cloturée</div>
            <div class="card-data" id="da-cloture">10</div>
        </div>
        <div class="card-item">
            <div class="card-title">Nombre d'imprimantes</div>
            <div class="card-data" id="card-nb-printer">10</div>
        </div>
        <div class="card-item">
            <div class="card-title">Toner en rupture</div>
            <div class="card-data" id="toner-rupture">10</div>
        </div>
        <div class="card-item">
            <div class="card-title">Total achat</div>
            <div class="card-data" id="total-achat">10</div>
        </div>
    </div>

    
    <div class="charts-container">
      <div class="chart-container" style="position: relative; height:300px; width:600px;">
        <canvas id="chart-spt"></canvas>
      </div>
      <div class="tbd-btn-container" style="position: relative; height:300px; width:600px;">
        <canvas id="chart-spd"></canvas>
          <?php foreach (get_years() as $row): ?>
            <input class="btn-year" type="button" value="<?= $row["year"]?>" onclick="setYear(<?= $row["year"]?>)">
          <?php endforeach; ?>
      </div>
      <div class="chart-container" style="position: relative; height:300px; width:600px;">
        <canvas id="chart-ppf"></canvas>
      </div>
      <div class="chart-container" style="position: relative; height:300px; width:600px;">
        <canvas id="chart-spu"></canvas>
      </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  let jsonData = <?php echo $jsonData; ?>;
  console.log(jsonData)
  document.getElementById('nb-toner').textContent=jsonData.table1[0]["Nombre de Toner"]
  document.getElementById('nb-cartouche').textContent=jsonData.table2[0]["Nombre de Cartouche"]
  document.getElementById('da-en-cours').textContent=jsonData.table3[0]["total"]
  document.getElementById('da-cloture').textContent=jsonData.table3[1]["total"]
  document.getElementById('card-nb-printer').textContent=jsonData.table7[0]["total"]
  document.getElementById('toner-rupture').textContent=jsonData.table8[0]["total"]
  
  const chart_spt = document.getElementById('chart-spt');//sortie par toner

  function getByfrs(listSAP,year=2023,societe="YASMINE_FONCIERE"){
    const aggregatedData = listSAP.reduce((acc, item) => {
  if (!acc[item.CardName]) {
    acc[item.CardName] = 0;
  }
  acc[item.CardName] += parseFloat(item.Total);
  return acc;
}, {});
  return aggregatedData
}

var options = {
    scales: {
                x: {
                    grid: {
                    display: false
                    }
                },
                y: {
                    grid: {
                    display: false
                    }
                }
                }
}
let spt_label=[]
let spt_data=[]
jsonData.table4.forEach(element => {
  spt_label.push(element.Toner)
  spt_data.push(element.utilisation)
});
  new Chart(chart_spt, {
    type: 'bar',
    data: {
      labels: spt_label,
      datasets: [{
        label: 'Sortie par toner',
        data: spt_data,
        borderWidth: 1
      }]
    }
    // plugins: [plugin]
  });



const chart_spu = document.getElementById('chart-spu'); //sortie par utilsiateur
let spu_label=[]
let spu_data=[]
jsonData.table5.forEach(element => {
  spu_label.push(element.user)
  spu_data.push(element.utilisation)
});
  new Chart(chart_spu, {
    type: 'pie',
    data: {
      labels: spu_label,
      datasets: [{
        label: 'Sortie par utilisateur',
        data: spu_data,
        borderWidth: 1
      }]
    },
    options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Sortie par utilisateur'
      }
    }
  },
  });

const chart_spd = document.getElementById('chart-spd'); //sortie par utilsiateur
let spd_label=[]
let spd_data=[]

const mois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
for (index = 0; index < mois.length; index++) {
  let totalQte=0
  jsonData.table6.forEach(element => {
    if(element.year==2023){
      if(element.month-1==index){
        totalQte=element.total_quantity
      }
    }
  });
  spd_label.push(mois[index])  
  spd_data.push(totalQte)
}

const spdChart = new Chart(chart_spd, {
    type: 'bar',
    data: {
      labels: spd_label,
      datasets: [{
        label: 'Sortie par date : 2023',
        data: spd_data,
        borderWidth: 1,
        backgroundColor: [
      'rgba(255, 99, 132, 0.2)'
    ]
      }]

    },
    
  });

  let listSAP=  jsonData.table9

document.getElementById('total-achat').textContent=listSAP.reduce((sum, item) => {
  return sum + parseInt(item.Total);
}, 0);


  let data1=getByfrs(listSAP)

  const chart_ppf = document.getElementById('chart-ppf'); //sortie par utilsiateur
let ppf_label=[]
let ppf_data=[]
console.log(data1)
for (const key in data1) {
  ppf_label.push(key)
  ppf_data.push(data1[key])
}

  new Chart(chart_ppf, {
    type: 'pie',
    data: {
      labels: ppf_label,
      datasets: [{
        label: 'Par fourisseur',
        data: ppf_data,
        borderWidth: 1
      }]
    },
    options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Par fourisseur'
      }
    }
  },
  });



</script>

<script>



function setYear(year){
let spd_label=[]
let spd_data=[]

const mois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
for (index = 0; index < mois.length; index++) {
  let totalQte=0
  jsonData.table6.forEach(element => {
    if(element.year==year){
      if(element.month-1==index){
        totalQte=element.total_quantity
      }
    }
  });
  spd_label.push(mois[index])  
  spd_data.push(totalQte)
}
  spdChart.data.datasets[0].label="Sortie par date : "+year;
  spdChart.data.labels=spd_label
  spdChart.data.datasets[0].data=spd_data
  spdChart.update();
}

</script>