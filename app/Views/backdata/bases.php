<?php /** @var array $batches */ /** @var string|null $q */ /** @var bool|null $showArchived */ ?>
<?php ob_start(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="mb-0">Bases (Lotes de Importación)</h5>
  <form class="d-flex gap-2" method="get" action="/backdata/bases">
    <input name="q" class="form-control" placeholder="Buscar por nombre o etiqueta" value="<?= htmlspecialchars($q ?? '') ?>">
    <div class="form-check d-flex align-items-center">
      <input class="form-check-input" type="checkbox" name="archived" value="1" id="archived" <?= !empty($showArchived)?'checked':'' ?>>
      <label class="form-check-label ms-1" for="archived">Archivadas</label>
    </div>
    <a href="/backdata/import" class="btn btn-primary">+ Nueva</a>
    <a href="/backdata/assign" class="btn btn-outline-secondary">Asignar</a>
  </form>
  
</div>
<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-striped mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Etiquetas</th>
            <th>Creado por</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Asignados</th>
            <th>Tipificados</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
         <?php if(empty($batches)): ?>
          <tr><td colspan="9" class="text-center text-muted">No hay bases aún.</td></tr>
        <?php else: foreach($batches as $b): ?>
          <tr>
            <td>#<?= (int)$b['id'] ?></td>
            <td><?= htmlspecialchars($b['name']) ?></td>
            <td><?= htmlspecialchars($b['tags'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['created_by_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['created_at']) ?></td>
            <td><span class="badge bg-secondary"><?= (int)$b['total_leads'] ?></span></td>
            <td><span class="badge bg-info text-dark"><?= (int)$b['assigned'] ?></span></td>
            <td><span class="badge bg-success"><?= (int)$b['tipified'] ?></span></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="/backdata/base?id=<?= (int)$b['id'] ?>">Ver</a>
              <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle-preview data-id="<?= (int)$b['id'] ?>">Expandir</button>
            </td>
          </tr>
          <tr class="bg-light" data-preview-row id="preview-row-<?= (int)$b['id'] ?>" style="display:none">
            <td colspan="9" id="preview-cell-<?= (int)$b['id'] ?>"></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-toggle-preview]');
  if(!btn) return;
  const id = btn.getAttribute('data-id');
  const row = document.getElementById('preview-row-'+id);
  const cell = document.getElementById('preview-cell-'+id);
  if(row.style.display==='none'){
    row.style.display='table-row';
    if(!cell.dataset.loaded){
      fetch('/backdata/base/preview?id='+id+'&limit=20',{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ cell.innerHTML=html; cell.dataset.loaded='1'; attachPreviewLinks(cell, id); });
    }
  } else {
    row.style.display='none';
  }
});
function attachPreviewLinks(scope, id){
  scope.querySelectorAll('[data-preview-link]').forEach(a=>{
    a.addEventListener('click', function(ev){ ev.preventDefault();
      fetch(this.href, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ scope.innerHTML=html; attachPreviewLinks(scope, id); });
    });
  });
}
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
