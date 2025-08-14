<?php /** @var array $rows */ /** @var string|null $q */ /** @var string $state */ ?>
<?php ob_start(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="mb-0">Progreso de Tipificación de Bases</h5>
  <form class="d-flex gap-2" method="get" action="/backdata/bases/progreso">
    <input name="q" class="form-control" placeholder="Buscar nombre o etiqueta" value="<?= htmlspecialchars($q ?? '') ?>">
    <select name="state" class="form-select" style="max-width:160px">
      <option value="all" <?= ($state??'all')==='all'?'selected':'' ?>>Todas</option>
      <option value="active" <?= ($state??'all')==='active'?'selected':'' ?>>Activas</option>
      <option value="archived" <?= ($state??'all')==='archived'?'selected':'' ?>>Archivadas</option>
    </select>
    <button class="btn btn-primary">Filtrar</button>
  </form>
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Etiquetas</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Total</th>
            <th>Tipificados</th>
            <th>Pendientes</th>
            <th>Avance</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(isset($noResults) && $noResults): ?>
            <tr><td colspan="9" class="text-center" style="color:#222;">Sin resultados para la búsqueda.</td></tr>
          <?php elseif(empty($rows)): ?>
            <tr><td colspan="9" class="text-center text-muted">No hay bases.</td></tr>
          <?php else: foreach($rows as $b): ?>
            <tr>
              <td>#<?= (int)$b['id'] ?></td>
              <td><?= htmlspecialchars($b['name']) ?></td>
              <td><?= htmlspecialchars($b['tags'] ?? '') ?></td>
              <td><?= htmlspecialchars($b['created_at']) ?></td>
              <td><?php
                $arch = !empty($b['archived_at']);
                echo base_status_pill($b['status_label'] ?? '', $b['progress_pct'] ?? 0, $arch);
              ?></td>
              <td><span class="badge bg-secondary"><?= (int)$b['total_leads'] ?></span></td>
              <td><span class="badge bg-success"><?= (int)$b['tipified'] ?></span></td>
              <td><span class="badge bg-warning text-dark"><?= (int)$b['pending'] ?></span></td>
              <td style="min-width:170px">
                <div class="progress" style="height:8px;">
                  <div class="progress-bar bg-info" style="width: <?= $b['progress_pct'] ?>%;"></div>
                </div>
                <small><?= $b['progress_pct'] ?>%</small>
              </td>
              <td class="d-flex gap-1">
                <a href="/backdata/base?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                <a href="/backdata/base/export?id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-secondary" title="CSV" target="_blank">CSV</a>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
