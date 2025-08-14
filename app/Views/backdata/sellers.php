<?php /** @var array $sellers */ /** @var string $from */ /** @var string $to */ /** @var int|null $batch_id */ /** @var array $batches */ /** @var int $sellers_count */ /** @var string|null $q */ ?>
<?php ob_start(); ?>
<div class="container-fluid">
<h5 class="mb-3 d-flex justify-content-between align-items-center">
  <span><i class="bi bi-bar-chart-fill me-2"></i>Productividad por vendedor <?php if(isset($sellers_count)): ?><small class="text-muted">(<?= (int)$sellers_count ?> vendedores)</small><?php endif; ?></span>
  <form method="get" action="/backdata/sellers" class="d-flex" style="gap:.5rem; max-width:420px;">
    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
    <?php if($batch_id): ?><input type="hidden" name="batch_id" value="<?= (int)$batch_id ?>"><?php endif; ?>
    <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" class="form-control" placeholder="Buscar asesor (nombre o usuario)">
    <button class="btn btn-outline-primary">Buscar</button>
  </form>
</h5>
<form class="card mb-3" method="get" action="/backdata/sellers">
  <div class="card-body row g-3">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Base</label>
      <select name="batch_id" class="form-select">
        <option value="">Todas</option>
        <?php foreach($batches as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= ($batch_id==(int)$b['id'])?'selected':'' ?>>#<?= (int)$b['id'] ?> - <?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <input type="hidden" name="q" value="<?= htmlspecialchars($q ?? '') ?>">
      <button class="btn btn-primary w-100">Filtrar</button>
    </div>
  </div>
</form>

<div class="card mb-3">
  <div class="card-body p-0">
    <div class="table-responsive">
  <table class="table table-hover mb-0" style="min-width:1700px;">
        <thead>
          <tr>
            <th><a href="?order=name" class="text-decoration-none text-dark">Vendedor <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=username" class="text-decoration-none text-dark">Usuario <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=assigned" class="text-decoration-none text-dark">Asignados <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=tipified" class="text-decoration-none text-dark">Tipificados <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=bases" class="text-decoration-none text-dark">Bases <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=tags" class="text-decoration-none text-dark">Etiquetas <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=assigned_total" class="text-decoration-none text-dark">Asignados (Total) <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=tipified_total" class="text-decoration-none text-dark">Tipificados (Total) <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=bases_total" class="text-decoration-none text-dark">Bases (Total) <i class="bi bi-arrow-down-up"></i></a></th>
            <th><a href="?order=progress" class="text-decoration-none text-dark">Progreso <i class="bi bi-arrow-down-up"></i></a></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($sellers)): ?>
            <tr><td colspan="11" class="text-center text-muted">Sin resultados</td></tr>
          <?php else: foreach($sellers as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['username']) ?></td>
              <td><span class="badge bg-info text-dark"><?= (int)$s['assigned'] ?></span></td>
              <td><span class="badge bg-success"><?= (int)$s['tipified'] ?></span></td>
              <td><?= htmlspecialchars($s['base_names'] ?? '-') ?></td>
              <td><?= htmlspecialchars($s['base_tags'] ?? '-') ?></td>
              <td><span class="badge bg-secondary"><?= (int)($s['assigned_total'] ?? 0) ?></span></td>
              <td><span class="badge bg-secondary"><?= (int)($s['tipified_total'] ?? 0) ?></span></td>
              <td><span class="badge bg-secondary"><?= (int)($s['bases_total'] ?? 0) ?></span></td>
              <td>
                <?php 
                  $a = max(0, (int)$s['assigned']);
                  $t = max(0, (int)$s['tipified']);
                  $pct = $a>0 ? round(($t/$a)*100) : 0;
                ?>
                <div class="progress" style="height: 8px; width: 120px">
                  <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-1"><?= $pct ?>%</div>
              </td>
              <td>
                <a class="btn btn-sm btn-primary" href="#" data-modal-fetch="/backdata/seller/preview?seller_id=<?= (int)$s['id'] ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?><?php if($batch_id): ?>&batch_id=<?= (int)$batch_id ?><?php endif; ?>" data-modal-title="Vendedor: <?= htmlspecialchars($s['name']) ?>">Abrir información</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
</div>


<script></script>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


