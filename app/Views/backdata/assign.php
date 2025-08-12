<?php /** @var array $sellers */ /** @var array $batches */ ob_start(); ?>
<h5 class="mb-3">Asignar leads</h5>
<form method="post" action="/backdata/assign" class="row g-3">
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
  <div class="col-md-6">
    <label class="form-label">Base (opcional)</label>
    <select name="batch_id" class="form-select">
      <option value="">Todas las bases</option>
      <?php if(!empty($batches)): foreach($batches as $b): ?>
        <option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars('#'.$b['id'].' - '.$b['name']); ?></option>
      <?php endforeach; endif; ?>
    </select>
    <div class="form-text">Si seleccionas una Base, sólo se asignarán leads de ese lote.</div>
  </div>
  <div class="col-md-6">
    <label class="form-label">Vendedor</label>
    <select name="seller_id" class="form-select" required>
      <option value="">Seleccione...</option>
      <?php foreach($sellers as $s): ?>
        <option value="<?php echo (int)$s['id']; ?>"><?php echo htmlspecialchars($s['name'].' ('.$s['username'].')'); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Cantidad</label>
    <div class="input-group">
      <input type="number" name="qty" class="form-control" value="100" min="1" max="200">
      <div class="input-group-text">
        <input class="form-check-input mt-0" type="checkbox" id="use_selected_only" name="use_selected_only" value="1">&nbsp;
        <label class="ms-1" for="use_selected_only">Solo seleccionados</label>
      </div>
    </div>
    <div class="form-text">Marca “Solo seleccionados” para ignorar la cantidad y asignar únicamente los que marcaste.</div>
  </div>
  <div class="col-12">
    <div id="assign-preview" class="card d-none">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>Previsualización de la Base seleccionada</div>
        <div class="small text-muted">Selecciona manualmente (click) los leads a asignar</div>
      </div>
      <div class="card-body p-0" id="assign-preview-body"></div>
    </div>
  </div>
  <div class="col-12 d-flex gap-2">
    <a href="/backdata/leads" class="btn btn-light">Volver</a>
    <button class="btn btn-primary">Asignar</button>
  </div>
</form>
<script>
const batchSelect = document.querySelector('select[name="batch_id"]');
const preview = document.getElementById('assign-preview');
const previewBody = document.getElementById('assign-preview-body');
const form = document.querySelector('form[action="/backdata/assign"]');
const selectedInputName = 'selected_ids[]';

batchSelect.addEventListener('change', ()=>{
  const id = batchSelect.value;
  if(!id){ preview.classList.add('d-none'); previewBody.innerHTML=''; return; }
  fetch('/backdata/assign/preview?batch_id='+id+'&limit=20',{headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(r=>r.text()).then(html=>{ previewBody.innerHTML=html; preview.classList.remove('d-none'); attachAssignPreview(previewBody); })
});

function attachAssignPreview(scope){
  // toggle selection on row click
  scope.querySelectorAll('[data-row-id]').forEach(tr=>{
    tr.addEventListener('click', ()=>{
      tr.classList.toggle('table-active');
      const id = tr.getAttribute('data-row-id');
      const existing = form.querySelector('input[type="hidden"][name="'+selectedInputName+'"][value="'+id+'"]');
      if(tr.classList.contains('table-active')){
        if(!existing){ const i=document.createElement('input'); i.type='hidden'; i.name=selectedInputName; i.value=id; form.appendChild(i); }
      } else {
        if(existing){ existing.remove(); }
      }
    });
  });
  // rebind size selector links
  scope.querySelectorAll('[data-assign-preview-link]').forEach(a=>{
    a.addEventListener('click', function(ev){ ev.preventDefault();
      fetch(this.href,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ scope.innerHTML=html; attachAssignPreview(scope); })
    });
  });
}
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php';
