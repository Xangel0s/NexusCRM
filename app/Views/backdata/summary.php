<?php /** @var int $assigned_today */ /** @var int $typed_today */ /** @var int $pending_today */ ob_start(); ?>
<h5 class="mb-3">Resumen diario</h5>
<div class="row g-3">
  <div class="col-md-4"><div class="card"><div class="card-body"><div class="small text-muted">Asignados hoy</div><div class="h3 mb-0"><?php echo (int)$assigned_today; ?></div></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><div class="small text-muted">Tipificados hoy</div><div class="h3 mb-0"><?php echo (int)$typed_today; ?></div></div></div></div>
  <div class="col-md-4"><div class="card"><div class="card-body"><div class="small text-muted">Pendientes hoy</div><div class="h3 mb-0"><?php echo (int)$pending_today; ?></div></div></div></div>
</div>
<div class="mt-4 d-flex gap-2">
  <a class="btn btn-outline-primary" href="/backdata/bases">Ver Bases</a>
  <a class="btn btn-outline-primary" href="/backdata/leads">Ver leads</a>
  <a class="btn btn-primary" href="/backdata/assign">Asignar leads</a>
  <a class="btn btn-outline-secondary" href="/backdata/import">Importar CSV</a>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php';
