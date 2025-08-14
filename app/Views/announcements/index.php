<?php $content=ob_start(); ?>
<h5 class="mb-3"><i class="bi bi-megaphone-fill me-2"></i>Anuncios</h5>
<a href="/announcements/create" class="btn btn-sm btn-primary mb-3">Nuevo</a>
<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr>
    <th class="sortable" data-col="id" data-type="number">ID <i class="bi bi-arrow-down-up"></i></th>
    <th>Img</th>
    <th class="sortable" data-col="title" data-type="text">Título <i class="bi bi-arrow-down-up"></i></th>
    <th class="sortable" data-col="audience" data-type="text">Audiencia <i class="bi bi-arrow-down-up"></i></th>
    <th class="sortable" data-col="starts_at" data-type="date">Inicio <i class="bi bi-arrow-down-up"></i></th>
    <th class="sortable" data-col="ends_at" data-type="date">Fin <i class="bi bi-arrow-down-up"></i></th>
    <th class="sortable" data-col="creator" data-type="text">Creador <i class="bi bi-arrow-down-up"></i></th>
    <th></th>
  </tr></thead>
  <tbody id="announcementsTableBody">
    <?php foreach($rows as $r): ?>
      <tr>
        <td data-col="id">#<?= (int)$r['id'] ?></td>
        <td><?php if(!empty($r['image_path'])): ?><img src="<?= htmlspecialchars($r['image_path']) ?>" style="height:40px;width:60px;object-fit:cover;" alt="img"><?php else: ?>-<?php endif; ?></td>
        <td data-col="title"><?= htmlspecialchars($r['title']) ?></td>
        <td data-col="audience"><?= htmlspecialchars($r['audience']) ?></td>
        <td data-col="starts_at"><?= htmlspecialchars($r['starts_at']) ?></td>
        <td data-col="ends_at"><?= htmlspecialchars($r['ends_at'] ?? '-') ?></td>
        <td data-col="creator"><?= htmlspecialchars($r['creator']) ?></td>
        <td>
          <form method="post" action="/announcements/delete" onsubmit="return confirm('Eliminar?')" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Borrar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.querySelector('table');
  const tbody = document.getElementById('announcementsTableBody');
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
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
