<?php
include "src". DIRECTORY_SEPARATOR ."db".DIRECTORY_SEPARATOR ."db_connection.php";

?>

<div class="sortie-stock-header">
    <h2>Tableau de Bord </h2>
</div>

<div class="tbd-container">
<div class="card-row">
    <div class="card-item">
        <div class="card-title">Nombre de Toner</div>
        <div class="card-data">13</div>
    </div>
    <div class="card-item">
        <div class="card-title">Nombre de Cartouche</div>
        <div class="card-data">24</div>
    </div>
    <div class="card-item">
        <div class="card-title">Nombre d'imprimantes</div>
        <div class="card-data">10</div>
    </div>
    <div class="card-item">
        <div class="card-title">Nombre de machin</div>
        <div class="card-data">10</div>
    </div>
</div>
<div class="sortie-stock-header">
    <h2>Charts</h2>
    <div>
    <div class="chart-container-row" style="position: relative; height:300px; width:100%;display:flex">
        <canvas id="myChart"></canvas>
        <canvas id="myChart2"></canvas>
    </div>
        
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx = document.getElementById('myChart');
  const ctx2 = document.getElementById('myChart2');

  const plugin = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart, args, options) => {
    const {ctx} = chart;
    ctx.save();
    ctx.globalCompositeOperation = 'destination-over';
    ctx.fillStyle = 'white';
    //ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
};
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
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin','Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
      datasets: [{
        label: 'Budget Toner',
        data: [0, 1200, 3500, 2005, 0, 0,0, 5000, 10000, 5000, 8000, 3200],
        borderWidth: 1
      }]
    },
    options: options
    ,
    plugins: [plugin]
  });

  new Chart(ctx2, {
    type: 'pie',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: 'Sortie par utilisateur',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: options
  });


</script>