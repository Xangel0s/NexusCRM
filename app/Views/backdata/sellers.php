<?php /** @var array $sellers */ /** @var string $from */ /** @var string $to */ /** @var int|null $batch_id */ /** @var array $batches */ /** @var int $sellers_count */ /** @var string|null $q */ ?>
<?php ob_start(); ?>
<h5 class="mb-3 d-flex justify-content-between align-items-center">
  <span>Productividad por vendedor <?php if(isset($sellers_count)): ?><small class="text-muted">(<?= (int)$sellers_count ?> vendedores)</small><?php endif; ?></span>
  <form method="get" action="/backdata/sellers" class="d-flex" style="gap:.5rem; max-width:420px;">
    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
    <?php if($batch_id): ?><input type="hidden" name="batch_id" value="<?= (int)$batch_id ?>"><?php endif; ?>
    <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" class="form-control" placeholder="Buscar asesor (nombre o usuario)">
    <button class="btn btn-outline-primary">Buscar</button>
  </form>
</h5>
<form class="card mb-3" method="get" action="/backdata/sellers">
  <div class="card-body row g-3">
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Base</label>
      <select name="batch_id" class="form-select">
        <option value="">Todas</option>
        <?php foreach($batches as $b): ?>
          <option value="<?= (int)$b['id'] ?>" <?= ($batch_id==(int)$b['id'])?'selected':'' ?>>#<?= (int)$b['id'] ?> - <?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <input type="hidden" name="q" value="<?= htmlspecialchars($q ?? '') ?>">
      <button class="btn btn-primary w-100">Filtrar</button>
    </div>
  </div>
</form>

<div class="card mb-3">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Vendedor</th>
            <th>Usuario</th>
            <th>Asignados</th>
            <th>Tipificados</th>
            <th>Bases</th>
            <th>Etiquetas</th>
            <th>Asignados (Total)</th>
            <th>Tipificados (Total)</th>
            <th>Bases (Total)</th>
            <th>Progreso</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($sellers)): ?>
            <tr><td colspan="11" class="text-center text-muted">Sin resultados</td></tr>
          <?php else: foreach($sellers as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['username']) ?></td>
              <td><span class="badge bg-info text-dark"><?= (int)$s['assigned'] ?></span></td>
              <td><span class="badge bg-success"><?= (int)$s['tipified'] ?></span></td>
              <td><?= htmlspecialchars($s['base_names'] ?? '-') ?></td>
              <td><?= htmlspecialchars($s['base_tags'] ?? '-') ?></td>
              <td><span class="badge bg-secondary"><?= (int)($s['assigned_total'] ?? 0) ?></span></td>
              <td><span class="badge bg-secondary"><?= (int)($s['tipified_total'] ?? 0) ?></span></td>
              <td><span class="badge bg-secondary"><?= (int)($s['bases_total'] ?? 0) ?></span></td>
              <td>
                <?php 
                  $a = max(0, (int)$s['assigned']);
                  $t = max(0, (int)$s['tipified']);
                  $pct = $a>0 ? round(($t/$a)*100) : 0;
                ?>
                <div class="progress" style="height: 8px; width: 120px">
                  <div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-1"><?= $pct ?>%</div>
              </td>
              <td>
                <button class="btn btn-sm btn-primary" type="button" data-seller-toggle data-id="<?= (int)$s['id'] ?>" data-expanded="false" aria-expanded="false">Expandir</button>
              </td>
            </tr>
            <tr class="bg-light" data-seller-preview-row id="seller-preview-row-<?= (int)$s['id'] ?>" style="display:none">
              <td colspan="11" id="seller-preview-cell-<?= (int)$s['id'] ?>" data-seller-id="<?= (int)$s['id'] ?>"></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
</div>

<script>
// Toggle inline preview under the clicked seller row
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-seller-toggle]');
  if(!btn) return;
  const id = btn.getAttribute('data-id');
  const row = document.getElementById('seller-preview-row-'+id);
  const cell = document.getElementById('seller-preview-cell-'+id);
  const expanded = btn.getAttribute('data-expanded') === 'true';
  if(expanded){
    row.style.display='none';
    btn.textContent='Expandir';
    btn.classList.remove('btn-secondary');
    btn.classList.add('btn-primary');
    btn.setAttribute('data-expanded','false');
    btn.setAttribute('aria-expanded','false');
    return;
  }
  // Show and load lazily
  row.style.display='table-row';
  btn.textContent='Contraer';
  btn.classList.remove('btn-primary');
  btn.classList.add('btn-secondary');
  btn.setAttribute('data-expanded','true');
  btn.setAttribute('aria-expanded','true');
  if(!cell.dataset.loaded){
    const params = new URLSearchParams({seller_id:id, from:'<?= htmlspecialchars($from) ?>', to:'<?= htmlspecialchars($to) ?>'<?php if($batch_id): ?>, batch_id:'<?= (int)$batch_id ?>'<?php endif; ?>});
    fetch('/backdata/seller/preview?'+params.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.text())
      .then(html=>{ cell.innerHTML=html; cell.dataset.loaded='1'; cell.dataset.sellerId=id; })
      .catch(()=>{ cell.innerHTML='<div class="alert alert-danger">Error al cargar preview</div>'; });
  }
});

// Delegated handlers inside any preview: limits and search
document.addEventListener('click', function(e){
  const limitBtn = e.target.closest('[data-seller-limit]');
  if(limitBtn){
    e.preventDefault();
    const container = e.target.closest('[id^="seller-preview-cell-"]');
    if(!container) return;
    const sellerId = container.dataset.sellerId || container.getAttribute('id').replace('seller-preview-cell-','');
    const limit = limitBtn.getAttribute('data-seller-limit');
    const searchInput = container.querySelector('.seller-search-input');
    const search = searchInput ? searchInput.value.trim() : '';
    reloadSellerPreviewIn(container, sellerId, {limit, search});
  }
  const searchBtn = e.target.closest('[data-seller-search]');
  if(searchBtn){
    const container = e.target.closest('[id^="seller-preview-cell-"]');
    if(!container) return;
    const sellerId = container.dataset.sellerId || container.getAttribute('id').replace('seller-preview-cell-','');
    const searchInput = container.querySelector('.seller-search-input');
    const search = searchInput ? searchInput.value.trim() : '';
    reloadSellerPreviewIn(container, sellerId, {search});
  }
});

document.addEventListener('keydown', function(e){
  if(e.key==='Enter'){
    const input = e.target.closest('.seller-search-input');
    if(!input) return;
    const container = input.closest('[id^="seller-preview-cell-"]');
    if(!container) return;
    const sellerId = container.dataset.sellerId || container.getAttribute('id').replace('seller-preview-cell-','');
    reloadSellerPreviewIn(container, sellerId, {search: input.value.trim()});
  }
});

function reloadSellerPreviewIn(container, sellerId, opts){
  const params = new URLSearchParams({ seller_id:sellerId, from:'<?= htmlspecialchars($from) ?>', to:'<?= htmlspecialchars($to) ?>' });
  <?php if($batch_id): ?>params.set('batch_id','<?= (int)$batch_id ?>');<?php endif; ?>
  if(opts.limit) params.set('limit', opts.limit);
  if(opts.search!==undefined) params.set('search', opts.search);
  fetch('/backdata/seller/preview?'+params.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.text())
    .then(html=>{ container.innerHTML=html; container.dataset.sellerId = sellerId; })
    .catch(()=>{ container.innerHTML='<div class="alert alert-danger">Error al cargar preview</div>'; });
}
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


