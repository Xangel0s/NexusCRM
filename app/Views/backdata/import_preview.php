<?php /** @var array $rows */ /** @var string $base_name */ /** @var string|null $tags */ /** @var array $counts */ /** @var string $csrf */ /** @var bool $allow_duplicates */ ?>
<?php ob_start(); ?>
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col"><h2>Previsualización de Importación</h2></div>
  </div>
  <div class="row mb-2">
    <div class="col-md-8 col-lg-6">
      <div class="alert alert-info">
        <div><strong>Base:</strong> <?= htmlspecialchars($base_name) ?></div>
        <?php if(!empty($tags)): ?>
          <div class="mt-1"><strong>Etiquetas:</strong> <?= htmlspecialchars($tags) ?></div>
        <?php endif; ?>
        <div class="mt-2 mb-1">
          <span class="badge bg-secondary">Total: <?= (int)$counts['total'] ?></span>
          <span class="badge bg-success">Válidos: <?= (int)$counts['pending'] ?></span>
          <span class="badge bg-warning text-dark">Duplicados: <?= (int)$counts['duplicates'] ?></span>
          <span class="badge bg-danger">Inválidos: <?= (int)$counts['invalid'] ?></span>
        </div>
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" id="allow_duplicates_preview" <?= $allow_duplicates? 'checked':'' ?> disabled>
          <label class="form-check-label" for="allow_duplicates_preview">Permitir importar duplicados</label>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <?php $limit = (int)($limit ?? 50); $limit = in_array($limit,[20,50,100,500])?$limit:50; $page = max(1,(int)($page??1)); $total = count($rows); $start = ($page-1)*$limit; $slice = array_slice($rows,$start,$limit); $pages = $total>0?max(1,ceil($total/$limit)):1; ?>
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>Muestra (<?= $total ?> filas) — mostrando <?= count($slice) ?> (<?= $start+1 ?>–<?= $start + count($slice) ?>)</div>
          <div class="d-flex gap-2 align-items-center">
            <label class="small text-muted">Por página</label>
            <select id="previewLimit" class="form-select form-select-sm" style="width:120px;">
              <option value="20" <?= $limit==20?'selected':'' ?>>20</option>
              <option value="50" <?= $limit==50?'selected':'' ?>>50</option>
              <option value="100" <?= $limit==100?'selected':'' ?>>100</option>
              <option value="500" <?= $limit==500?'selected':'' ?>>500</option>
            </select>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th><input type="checkbox" id="selectAllPreview"></th>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Teléfono</th>
                  <th>Email</th>
                  <th>Fuente</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
              <?php $i=$start; foreach($slice as $idx=>$r): $i++; ?>
                <tr>
                  <td><input type="checkbox" class="rowSelect" name="selected_indices[]" value="<?= $start + $idx ?>"></td>
                  <td><?= $i ?></td>
                  <td><?= htmlspecialchars($r['full_name']) ?></td>
                  <td><?= htmlspecialchars($r['phone']) ?></td>
                  <td><?= htmlspecialchars($r['email']) ?></td>
                  <td><?= htmlspecialchars($r['source']) ?></td>
                  <td>
                    <?php if(($r['status']??'')==='pending'): ?>
                      <span class="badge bg-success">válido</span>
                    <?php elseif(($r['status']??'')==='duplicate'): ?>
                      <span class="badge bg-warning text-dark">duplicado</span>
                    <?php else: ?>
                      <span class="badge bg-danger">inválido</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <a href="/backdata/import" class="btn btn-light">Volver</a>
          <form method="post" action="/backdata/import/remove-selected" id="removeForm" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="limit" value="<?= $limit ?>">
            <input type="hidden" name="page" value="<?= $page ?>">
            <input type="hidden" name="selected_indices[]" value="">
            <button type="submit" class="btn btn-outline-danger" id="removeSelectedBtn">Eliminar seleccionados</button>
          </form>
        <form method="post" action="/backdata/import/commit" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
          <input type="hidden" name="limit" value="<?= $limit ?>">
          <input type="hidden" name="page" value="<?= $page ?>">
          <input type="hidden" name="allow_duplicates" value="<?= $allow_duplicates? '1':'0' ?>">
          <div class="form-check mb-2 d-inline-block me-2">
            <input class="form-check-input" type="checkbox" name="create_announcement" id="create_announcement" value="1">
            <label class="form-check-label" for="create_announcement">Crear anuncio público indicando la nueva base</label>
          </div>
          <button class="btn btn-primary" type="submit">Confirmar importación</button>
        </form>
      </div>
      <?php if($total===0): ?>
        <div class="alert alert-info">No hay filas en la importación. Sube un CSV primero.</div>
      <?php else: ?>
      <nav class="mt-2"><ul class="pagination pagination-sm">
        <li class="page-item <?= ($page<=1)?'disabled':'' ?>"><a class="page-link" href="/backdata/import/preview?limit=<?= $limit ?>&page=<?= max(1,$page-1) ?>">&laquo; Prev</a></li>
        <?php for($p=1;$p<=max(1,min($pages,10));$p++): ?>
          <li class="page-item <?= ($p==$page)?'active':'' ?>"><a class="page-link" href="/backdata/import/preview?limit=<?= $limit ?>&page=<?= $p ?>"><?= $p ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= ($page>=$pages)?'disabled':'' ?>"><a class="page-link" href="/backdata/import/preview?limit=<?= $limit ?>&page=<?= min($pages,$page+1) ?>">Next &raquo;</a></li>
      </nav>
      <?php endif; ?>
      <script>
        (function(){
          document.getElementById('selectAllPreview').addEventListener('change', function(){ document.querySelectorAll('.rowSelect').forEach(cb=>cb.checked=this.checked); });
          document.getElementById('previewLimit').addEventListener('change', function(){ location.search = '?limit='+this.value+'&page=1'; });
          // Remove selected: copy checked indices into form
          document.getElementById('removeForm').addEventListener('submit', function(e){
            const sel = Array.from(document.querySelectorAll('.rowSelect:checked')).map(x=>x.value);
            if(sel.length===0){ e.preventDefault(); alert('No hay filas seleccionadas'); return; }
            // remove existing hidden inputs
            this.querySelectorAll('input[name="selected_indices[]"]').forEach(i=>i.remove());
            sel.forEach(v=>{ const ip=document.createElement('input'); ip.type='hidden'; ip.name='selected_indices[]'; ip.value=v; this.appendChild(ip); });
          });
        })();
      </script>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
