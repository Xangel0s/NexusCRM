<?php /** @var int $assigned_today */ /** @var int $typed_today */ /** @var int $bases_total_today */ /** @var int $pending_assign_today */ ob_start(); ?>
<h5 class="mb-3">Resumen diario</h5>
<div class="row g-3">
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Asignados hoy</div><div class="h3 mb-0"><?php echo (int)$assigned_today; ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Tipificados hoy</div><div class="h3 mb-0"><?php echo (int)$typed_today; ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Bases total hoy</div><div class="h3 mb-0"><?php echo (int)$bases_total_today; ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="small text-muted">Pendientes de asignar hoy</div><div class="h3 mb-0"><?php echo (int)$pending_assign_today; ?></div></div></div></div>
</div>

<div class="mt-4">
  <div class="card">
    <div class="card-body">
      <h6 class="mb-3"><i class="bi bi-bar-chart-fill me-2"></i>Desempeño diario</h6>
      <canvas id="summaryBarChart" height="80"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('summaryBarChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
  labels: ['Asignados', 'Tipificados', 'Bases total hoy', 'Pendientes de asignar'],
      datasets: [{
        label: 'Leads',
        data: [<?php echo (int)$assigned_today; ?>, <?php echo (int)$typed_today; ?>, <?php echo (int)$bases_total_today; ?>, <?php echo (int)$pending_assign_today; ?>],
        backgroundColor: [
          'rgba(13, 110, 253, 0.7)', // azul
          'rgba(25, 135, 84, 0.7)',  // verde
          'rgba(220, 53, 69, 0.7)',  // rojo
          'rgba(255, 193, 7, 0.7)'   // amarillo
        ],
        borderRadius: 8,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: true }
      },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      }
    }
  });
});
</script>
<div class="mt-4 d-flex gap-2">
  <a class="btn btn-outline-primary" href="/backdata/bases">Ver Bases</a>
  <a class="btn btn-outline-primary" href="/backdata/leads">Ver leads</a>
  <a class="btn btn-primary" href="/backdata/assign">Asignar leads</a>
  <a class="btn btn-outline-secondary" href="/backdata/import">Importar CSV</a>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php';
