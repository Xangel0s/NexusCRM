<?php /** @var array $leads */ /** @var int $limit */ /** @var int $batch_id */ ?>
<div class="p-2">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted">Mostrar</span>
    <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="/backdata/assign/preview?batch_id=<?= (int)$batch_id ?>&limit=20" data-assign-preview-link>20</a>
    <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="/backdata/assign/preview?batch_id=<?= (int)$batch_id ?>&limit=50" data-assign-preview-link>50</a>
    <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="/backdata/assign/preview?batch_id=<?= (int)$batch_id ?>&limit=100" data-assign-preview-link>100</a>
    <span class="ms-auto small text-muted">Click para seleccionar/deseleccionar</span>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Creado</th></tr></thead>
      <tbody>
        <?php if(empty($leads)): ?>
          <tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>
        <?php else: foreach($leads as $l): ?>
          <tr data-row-id="<?= (int)$l['id'] ?>">
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
  <?php if(isset($total)): $pages = max(1, ceil($total / $limit)); ?>
    <nav class="mt-2"><ul class="pagination pagination-sm">
      <li class="page-item <?= ($page<=1)?'disabled':'' ?>"><a class="page-link" href="?batch_id=<?= (int)$batch_id ?>&limit=<?= (int)$limit ?>&page=<?= max(1,$page-1) ?>">&laquo; Prev</a></li>
      <?php for($p=1;$p<=max(1,min($pages,10));$p++): ?>
        <li class="page-item <?= ($p==$page)?'active':'' ?>"><a class="page-link" href="?batch_id=<?= (int)$batch_id ?>&limit=<?= (int)$limit ?>&page=<?= $p ?>"><?= $p ?></a></li>
      <?php endfor; ?>
      <li class="page-item <?= ($page>=$pages)?'disabled':'' ?>"><a class="page-link" href="?batch_id=<?= (int)$batch_id ?>&limit=<?= (int)$limit ?>&page=<?= min($pages,$page+1) ?>">Next &raquo;</a></li>
    </ul></nav>
  <?php endif; ?>
</div>


