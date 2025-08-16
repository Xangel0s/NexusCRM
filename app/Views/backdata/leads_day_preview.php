<?php /** @var array $leads */ /** @var int $limit */ /** @var string $date */ ?>
<div class="p-2">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted">Mostrar</span>
  <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=20" data-day-link>20</a>
  <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=50" data-day-link>50</a>
  <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=100" data-day-link>100</a>
  <a class="btn btn-sm <?= $limit==500?'btn-primary':'btn-outline-primary' ?>" href="/backdata/leads/day-preview?date=<?= urlencode($date) ?>&limit=500" data-day-link>500</a>
  </div>
  <div class="table-responsive">
  <div class="mb-2 small text-muted">Nota: el listado por defecto incluye leads creados, asignados o tipificados en la fecha seleccionada. Usa los toggles para ajustar.</div>
    <table class="table table-sm table-striped mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Asignado a</th><th>Estado</th><th>Creado</th></tr></thead>
      <tbody>
        <?php if(empty($leads)): ?>
         <tr><td colspan="8" class="text-center text-muted">Sin resultados</td></tr>
        <?php else: foreach($leads as $l): ?>
         <tr>
           <td>#<?= (int)$l['id'] ?></td>
           <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['email'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['source_name'] ?? '') ?></td>
           <td><?= htmlspecialchars($l['assigned_to'] ?? '') ?></td>
               <td><?= age_status_pill($l['last_status'] ?? '', $l['created_at'] ?? null) ?><?php if(!empty($l['last_status_by'])): ?> <div class="small text-muted">por: <?= htmlspecialchars($l['last_status_by']) ?></div><?php endif; ?></td>
               <td><?= htmlspecialchars($l['created_at'] ?? '') ?><?php if(!empty($l['assigned_at'])): ?> <div class="small text-muted">asig: <?= htmlspecialchars($l['assigned_at']) ?></div><?php endif; ?><?php if(!empty($l['last_note'])): ?> <div class="small text-muted">nota: <?= htmlspecialchars($l['last_note']) ?></div><?php endif; ?></td>
         </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  <div class="ms-auto d-flex align-items-center gap-2">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="includeAssigned" <?php if(!isset($_GET['include_assigned'])||$_GET['include_assigned']=='1') echo 'checked'; ?>>
        <label class="form-check-label small" for="includeAssigned">Incl. asignados</label>
      </div>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="includeTipified" <?php if(!isset($_GET['include_tipified'])||$_GET['include_tipified']=='1') echo 'checked'; ?>>
        <label class="form-check-label small" for="includeTipified">Incl. tipificados</label>
      </div>
    </div>
  <script>
    (function(){
  // Preserve current filters (batch_id, q, status, assigned, typed) so modal matches the cards
  const extra = [];
  <?php if(isset($_GET['batch_id']) && $_GET['batch_id']!==''): ?> extra.push('batch_id=<?= urlencode($_GET['batch_id']) ?>'); <?php endif; ?>
  <?php if(isset($_GET['q']) && $_GET['q']!==''): ?> extra.push('q=<?= urlencode($_GET['q']) ?>'); <?php endif; ?>
  <?php if(isset($_GET['status']) && $_GET['status']!==''): ?> extra.push('status=<?= urlencode($_GET['status']) ?>'); <?php endif; ?>
  <?php if(isset($_GET['assigned']) && $_GET['assigned']!==''): ?> extra.push('assigned=<?= urlencode($_GET['assigned']) ?>'); <?php endif; ?>
  <?php if(isset($_GET['typed']) && $_GET['typed']!==''): ?> extra.push('typed=<?= urlencode($_GET['typed']) ?>'); <?php endif; ?>
  const baseUrl = '/backdata/leads/day-preview?date=' + encodeURIComponent('<?= $date ?>') + '&limit=<?= $limit ?>' + (extra.length?('&'+extra.join('&')):'');
      const includeAssigned = document.getElementById('includeAssigned');
      const includeTipified = document.getElementById('includeTipified');
      function update(){
        let url = baseUrl;
        if(includeAssigned && includeAssigned.checked) url += '&include_assigned=1';
        if(includeTipified && includeTipified.checked) url += '&include_tipified=1';
        // navigate the modal content via fetch to avoid full page reload
  fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r=>r.text()).then(html=>{
          // replace modal body
          const tmp = document.createElement('div'); tmp.innerHTML = html;
          const newTable = tmp.querySelector('.table-responsive');
          const old = document.querySelector('.modal .table-responsive');
          if(old && newTable) old.parentNode.replaceChild(newTable, old);
        });
      }
      if(includeAssigned) includeAssigned.addEventListener('change', update);
      if(includeTipified) includeTipified.addEventListener('change', update);
    })();
  </script>
    <?php if(isset($totalRows)): $pages = max(1, ceil($totalRows / $limit)); ?>
      <nav class="mt-2"><ul class="pagination pagination-sm">
        <li class="page-item <?= ($page<=1)?'disabled':'' ?>"><a class="page-link" href="?date=<?= urlencode($date) ?>&limit=<?= (int)$limit ?>&page=<?= max(1,$page-1) ?>">&laquo; Prev</a></li>
        <?php for($p=1;$p<=max(1,min($pages,10));$p++): ?>
          <li class="page-item <?= ($p==$page)?'active':'' ?>"><a class="page-link" href="?date=<?= urlencode($date) ?>&limit=<?= (int)$limit ?>&page=<?= $p ?>"><?= $p ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= ($page>=$pages)?'disabled':'' ?>"><a class="page-link" href="?date=<?= urlencode($date) ?>&limit=<?= (int)$limit ?>&page=<?= min($pages,$page+1) ?>">Next &raquo;</a></li>
      </ul></nav>
    <?php endif; ?>
</div>


