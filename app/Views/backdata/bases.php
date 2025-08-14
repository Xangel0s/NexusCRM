<?php /** @var array $batches */ /** @var string|null $q */ /** @var bool|null $showArchived */ ?>
<?php ob_start(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="mb-0"><i class="bi bi-database-fill me-2"></i>Bases (Lotes de Importación)</h5>
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
    <div class="table-responsive" style="max-width:100%; overflow-x:auto;">
  <table class="table table-hover table-striped mb-0" style="min-width:1400px;">
        <thead>
          <tr>
            <th class="sortable" data-col="id" data-type="number">ID <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="name" data-type="text">Nombre <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="tags" data-type="text">Etiquetas <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="created_by_name" data-type="text">Creado por <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="created_at" data-type="date">Fecha <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="total_leads" data-type="number">Total <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="assigned" data-type="number">Asignados <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="tipified" data-type="number">Tipificados <i class="bi bi-arrow-down-up"></i></th>
            <th></th>
          </tr>
        </thead>
        <tbody id="basesTableBody">
         <?php if(isset($noResults) && $noResults): ?>
          <tr><td colspan="9" class="text-center" style="color: #222;">No se encontraron resultados para la búsqueda.</td></tr>
        <?php elseif(empty($batches)): ?>
          <tr><td colspan="9" class="text-center text-muted">No hay bases aún.</td></tr>
        <?php else: foreach($batches as $b): ?>
          <tr>
            <td data-col="id">#<?= (int)$b['id'] ?></td>
            <td data-col="name"><?= htmlspecialchars($b['name']) ?></td>
            <td data-col="tags"><?= htmlspecialchars($b['tags'] ?? '') ?></td>
            <td data-col="created_by_name"><?= htmlspecialchars($b['created_by_name'] ?? '') ?></td>
            <td data-col="created_at"><?= htmlspecialchars($b['created_at']) ?></td>
            <td data-col="total_leads"><span class="badge bg-secondary"><?= (int)$b['total_leads'] ?></span></td>
            <td data-col="assigned"><span class="badge bg-info text-dark"><?= (int)$b['assigned'] ?></span></td>
            <td data-col="tipified"><span class="badge bg-success"><?= (int)$b['tipified'] ?></span></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="/backdata/base?id=<?= (int)$b['id'] ?>">Ver</a>
              <a class="btn btn-sm btn-outline-secondary" href="#" data-modal-fetch="/backdata/base/preview?id=<?= (int)$b['id'] ?>&limit=20" data-modal-title="Base #<?= (int)$b['id'] ?> - Preview">Abrir información</a>
            </td>
          </tr>
          
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.querySelector('table');
  const tbody = document.getElementById('basesTableBody');
  let currentSort = { col: null, dir: null };
  function getCellValue(row, col, type) {
    let cell = row.querySelector(`[data-col='${col}']`);
    if(!cell) return '';
    let val = cell.textContent.trim();
    if(type === 'number') return parseFloat(val.replace(/[^\d.]/g, ''));
    if(type === 'date') return new Date(val);
    return val.toLowerCase();
  }
  function sortTable(col, dir, type) {
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
      let va = getCellValue(a, col, type);
      let vb = getCellValue(b, col, type);
      if(type === 'number' || type === 'date') {
        if(dir === 'asc') return va - vb;
        else return vb - va;
      }
      if(dir === 'asc') return va > vb ? 1 : va < vb ? -1 : 0;
      else return va < vb ? 1 : va > vb ? -1 : 0;
    });
    rows.forEach(r => tbody.appendChild(r));
  }
  table.querySelectorAll('th.sortable').forEach(th => {
    th.style.cursor = 'pointer';
    th.addEventListener('click', function() {
      const col = th.getAttribute('data-col');
      const type = th.getAttribute('data-type');
      let dir;
      if(type === 'number' || type === 'date') {
        dir = currentSort.col === col && currentSort.dir === 'desc' ? 'asc' : 'desc';
      } else {
        dir = currentSort.col === col && currentSort.dir === 'asc' ? 'desc' : 'asc';
      }
      if(currentSort.col === col && ((type === 'number' || type === 'date') ? currentSort.dir === 'asc' : currentSort.dir === 'desc')) dir = null;
      currentSort = { col: dir ? col : null, dir };
      table.querySelectorAll('th.sortable i').forEach(i => { i.className = 'bi bi-arrow-down-up'; });
      if(dir === 'desc') th.querySelector('i').className = 'bi bi-arrow-down';
      else if(dir === 'asc') th.querySelector('i').className = 'bi bi-arrow-up';
      if(dir) sortTable(col, dir, type); else location.reload();
    });
  });
});
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
