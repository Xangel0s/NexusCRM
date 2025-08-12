<?php /** @var array $leads */ /** @var array $counts */ /** @var int $limit */ /** @var int $id */ ?>
<div class="p-2 border-top">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <div class="small">
      <span class="badge bg-secondary">Total: <?= (int)$counts['total'] ?></span>
      <span class="badge bg-info text-dark">Asignados: <?= (int)$counts['assigned'] ?></span>
      <span class="badge bg-success">Tipificados: <?= (int)$counts['tipified'] ?></span>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <span class="small text-muted">Mostrar</span>
      <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="/backdata/base/preview?id=<?= (int)$id ?>&limit=20" data-preview-link>20</a>
      <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="/backdata/base/preview?id=<?= (int)$id ?>&limit=50" data-preview-link>50</a>
      <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="/backdata/base/preview?id=<?= (int)$id ?>&limit=100" data-preview-link>100</a>
      <a class="btn btn-sm btn-success" href="/backdata/base/export?id=<?= (int)$id ?>">Exportar CSV</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Creado</th></tr></thead>
      <tbody>
      <?php if(empty($leads)): ?>
        <tr><td colspan="6" class="text-muted text-center">Sin registros</td></tr>
      <?php else: foreach($leads as $l): ?>
        <tr>
          <td>#<?= (int)$l['id'] ?></td>
          <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['source_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


