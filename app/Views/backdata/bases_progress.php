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
  <table class="table table-sm align-middle mb-0" style="min-width:1400px;">
        <thead>
          <tr>
            <th class="sortable" data-col="id" data-type="number">ID <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="name" data-type="text">Nombre <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="tags" data-type="text">Etiquetas <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="created_at" data-type="date">Fecha <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="status_label" data-type="text">Estado <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="total_leads" data-type="number">Total <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="tipified" data-type="number">Tipificados <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="pending" data-type="number">Pendientes <i class="bi bi-arrow-down-up"></i></th>
            <th class="sortable" data-col="progress_pct" data-type="number">Avance <i class="bi bi-arrow-down-up"></i></th>
            <th></th>
          </tr>
        </thead>
        <tbody id="basesProgressTableBody">
          <?php if(isset($noResults) && $noResults): ?>
            <tr><td colspan="10" class="text-center" style="color:#222;">Sin resultados para la búsqueda.</td></tr>
          <?php elseif(empty($rows)): ?>
            <tr><td colspan="10" class="text-center text-muted">No hay bases.</td></tr>
          <?php else: foreach($rows as $b): ?>
            <tr>
              <td data-col="id">#<?= (int)$b['id'] ?></td>
              <td data-col="name"><?= htmlspecialchars($b['name']) ?></td>
              <td data-col="tags"><?= htmlspecialchars($b['tags'] ?? '') ?></td>
              <td data-col="created_at"><?= htmlspecialchars($b['created_at']) ?></td>
              <td data-col="status_label"><?php
                $arch = !empty($b['archived_at']);
                echo base_status_pill($b['status_label'] ?? '', $b['progress_pct'] ?? 0, $arch);
              ?></td>
              <td data-col="total_leads"><span class="badge bg-secondary"><?= (int)$b['total_leads'] ?></span></td>
              <td data-col="tipified"><span class="badge bg-success"><?= (int)$b['tipified'] ?></span></td>
              <td data-col="pending"><span class="badge bg-warning text-dark"><?= (int)$b['pending'] ?></span></td>
              <td data-col="progress_pct" style="min-width:170px">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.querySelector('table');
  const tbody = document.getElementById('basesProgressTableBody');
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
