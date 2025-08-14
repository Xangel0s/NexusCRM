<?php /** @var array $days */ /** @var string $from */ /** @var string $to */ /** @var string $status */ /** @var string $assigned */ /** @var array $statuses */ ?>
<?php ob_start(); ?>
<h5 class="mb-3">Leads</h5>
<form class="card mb-3" method="get" action="/backdata/leads">
  <div class="card-body row g-3">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Estado</label>
      <select name="status" class="form-select">
        <option value="">Todos</option>
        <?php foreach($statuses as $st): ?>
          <option value="<?= htmlspecialchars($st) ?>" <?= $status===$st?'selected':'' ?>><?= htmlspecialchars($st) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Asignación/Tipificado</label>
      <select name="assigned" class="form-select">
        <option value="" <?= $assigned===''?'selected':'' ?>>Todos</option>
        <option value="1" <?= $assigned==='1'?'selected':'' ?>>Asignados</option>
        <option value="0" <?= $assigned==='0'?'selected':'' ?>>No asignados</option>
        <option value="t1" <?= $assigned==='t1'?'selected':'' ?>>Tipificados</option>
        <option value="t0" <?= $assigned==='t0'?'selected':'' ?>>No tipificados</option>
      </select>
    </div>
    <div class="col-12">
      <button class="btn btn-primary">Filtrar</button>
    </div>
  </div>
  
</form>

<?php if(empty($days)): ?>
  <div class="alert alert-light">Sin resultados</div>
<?php else: foreach($days as $d): ?>
  <div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <div class="h6 mb-1">Fecha: <?= htmlspecialchars($d['d']) ?></div>
        <div class="small text-muted">
          <span class="badge bg-secondary">Total: <?= (int)$d['total'] ?></span>
          <span class="badge bg-info text-dark">Asignados: <?= (int)$d['assigned'] ?></span>
          <span class="badge bg-success">Tipificados: <?= (int)$d['tipified'] ?></span>
        </div>
      </div>
      <div class="d-flex gap-2">
  <a href="/backdata/leads/export?date=<?= urlencode($d['d']) ?>&status=<?= urlencode($status) ?>&assigned=<?= urlencode($assigned) ?>" class="btn btn-sm btn-success">Exportar CSV</a>
  <a href="#" class="btn btn-sm btn-outline-primary" data-modal-fetch="/backdata/leads/day-preview?date=<?= urlencode($d['d']) ?>&limit=20&status=<?= urlencode($status) ?>&assigned=<?= urlencode($assigned) ?>" data-modal-title="Leads del <?= htmlspecialchars($d['d']) ?>">Abrir información</a>
      </div>
    </div>
    
  </div>
<?php endforeach; endif; ?>

<script></script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


