<?php /** @var array $sellers */ /** @var string $from */ /** @var string $to */ /** @var int|null $batch_id */ /** @var array $batches */ ?>
<?php ob_start(); ?>
<h5 class="mb-3">Productividad por vendedor</h5>
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
      <button class="btn btn-primary w-100">Filtrar</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Vendedor</th><th>Usuario</th><th>Asignados</th><th>Tipificados</th><th>Bases</th><th></th></tr>
        </thead>
        <tbody>
          <?php if(empty($sellers)): ?>
            <tr><td colspan="6" class="text-center text-muted">Sin resultados</td></tr>
          <?php else: foreach($sellers as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['username']) ?></td>
              <td><span class="badge bg-info text-dark"><?= (int)$s['assigned'] ?></span></td>
              <td><span class="badge bg-success"><?= (int)$s['tipified'] ?></span></td>
              <td><span class="badge bg-secondary"><?= (int)$s['bases'] ?></span></td>
              <td>
                <button class="btn btn-sm btn-outline-primary" type="button" data-seller-toggle data-id="<?= (int)$s['id'] ?>">Expandir</button>
              </td>
            </tr>
            <tr class="bg-light" id="seller-preview-row-<?= (int)$s['id'] ?>" style="display:none">
              <td colspan="6" id="seller-preview-cell-<?= (int)$s['id'] ?>"></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
</div>

<script>
document.addEventListener('click', function(e){
  const btn = e.target.closest('[data-seller-toggle]');
  if(!btn) return;
  const id = btn.getAttribute('data-id');
  const row = document.getElementById('seller-preview-row-'+id);
  const cell = document.getElementById('seller-preview-cell-'+id);
  if(row.style.display==='none'){
    row.style.display = 'table-row';
    if(!cell.dataset.loaded){
      const params = new URLSearchParams({seller_id:id, from:'<?= htmlspecialchars($from) ?>', to:'<?= htmlspecialchars($to) ?>'<?php if($batch_id): ?>, batch_id:'<?= (int)$batch_id ?>'<?php endif; ?>});
      fetch('/backdata/seller/preview?'+params.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ cell.innerHTML = html; cell.dataset.loaded='1'; attachSellerLinks(cell,id); });
    }
  } else {
    row.style.display = 'none';
  }
});
function attachSellerLinks(scope, id){
  scope.querySelectorAll('[data-seller-link]').forEach(a=>{
    a.addEventListener('click', function(ev){ ev.preventDefault();
      fetch(this.href,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ scope.innerHTML=html; attachSellerLinks(scope,id); });
    });
  });
}
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


