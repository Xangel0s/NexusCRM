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
  <div class="table-responsive" style="width:100%; overflow-x:auto;">
    <table class="table table-sm table-striped mb-0" style="min-width:1200px;">
  <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Creado</th><th>Asignado a</th><th>Status</th><th>Tipificado por</th><th>Fecha status</th><th>Nota</th></tr></thead>
      <tbody>
      <?php if(empty($leads)): ?>
        <tr><td colspan="11" class="text-muted text-center">Sin registros</td></tr>
      <?php else: foreach($leads as $l): ?>
        <tr>
          <td>#<?= (int)$l['id'] ?></td>
          <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['email'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['source_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($l['created_at'] ?? '') ?></td>
          <td>
            <?php if(!empty($l['seller_id'])): ?>
              <?= htmlspecialchars(($l['seller_name'] ?? '')) ?> (<?= '#'.(int)$l['seller_id'] ?>)
            <?php else: ?>-
            <?php endif; ?>
          </td>
          <td>
            <?php if(!empty($l['last_status'])): ?>
              <?= status_pill($l['last_status']) ?>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <?php if(!empty($l['last_status_by']) && !empty($l['last_status'])): ?>
              <?= htmlspecialchars($l['last_status_by']) ?>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <?php if(!empty($l['last_status_at']) && !empty($l['last_status'])): ?>
              <?= htmlspecialchars($l['last_status_at']) ?>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td style="max-width:400px; white-space:normal; overflow-wrap:anywhere;" title="<?= htmlspecialchars($l['last_note'] ?? '-') ?>">
            <?= htmlspecialchars($l['last_note'] ?? '-') ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


