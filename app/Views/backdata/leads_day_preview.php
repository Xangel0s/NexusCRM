<?php /** @var array $leads */ /** @var int $limit */ /** @var string $date */ ?>
<div class="p-2">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted">Mostrar</span>
    <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=20" data-day-link>20</a>
    <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=50" data-day-link>50</a>
    <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=100" data-day-link>100</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Status</th><th>Creado</th></tr></thead>
      <tbody>
        <?php if(empty($leads)): ?>
         <tr><td colspan="7" class="text-center text-muted">Sin resultados</td></tr>
        <?php else: foreach($leads as $l): ?>
         <tr>
           <td>#<?= (int)$l['id'] ?></td>
           <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['email'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['source_name'] ?? '') ?></td>
           <td><span class="badge bg-secondary"><?= htmlspecialchars($l['status'] ?? '') ?></span></td>
           <td><?= htmlspecialchars($l['created_at'] ?? '') ?></td>
         </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


