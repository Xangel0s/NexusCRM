<?php /** @var array $batch */ /** @var array $leads */ /** @var string|null $q */ /** @var string|null $assigned */ ?>
<?php ob_start(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
  <h5 class="mb-1"><i class="bi bi-file-earmark-text-fill me-2"></i>Base #<?= (int)$batch['id'] ?> — <?= htmlspecialchars($batch['name']) ?></h5>
    <div class="text-muted small">Etiquetas: <?= htmlspecialchars($batch['tags'] ?? '') ?> · Creada: <?= htmlspecialchars($batch['created_at']) ?><?= !empty($batch['archived_at'])? ' · Archivada: '.htmlspecialchars($batch['archived_at']):'' ?></div>
  </div>
  <div class="d-flex gap-2">
    <form method="post" action="/backdata/base/rename" class="d-flex gap-2">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
      <input class="form-control" name="name" value="<?= htmlspecialchars($batch['name']) ?>" placeholder="Nombre">
      <input class="form-control" name="tags" value="<?= htmlspecialchars($batch['tags'] ?? '') ?>" placeholder="Etiquetas">
      <button class="btn btn-outline-primary" type="submit">Guardar</button>
    </form>
    <?php if(empty($batch['archived_at'])): ?>
      <form method="post" action="/backdata/base/archive">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
        <button class="btn btn-outline-danger" type="submit">Archivar</button>
      </form>
    <?php else: ?>
      <form method="post" action="/backdata/base/unarchive">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
        <button class="btn btn-outline-success" type="submit">Desarchivar</button>
      </form>
    <?php endif; ?>
  <a href="/backdata/base/export?id=<?= (int)$batch['id'] ?>" class="btn btn-outline-secondary" title="Exportar CSV" target="_blank">Exportar CSV</a>
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">Eliminar</button>
  <a href="/backdata/bases" class="btn btn-secondary">Volver</a>
  </div>
  
</div>

  <!-- Modal: Confirmar eliminación de base -->
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Eliminar base</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Esta acción eliminará la base y todos sus leads de forma permanente. ¿Deseas continuar?</p>
          <div class="alert alert-warning small">Esta acción no se puede deshacer.</div>
          <div class="mt-3">
            <label for="confirm_name" class="form-label small">Escribe el nombre de la base para confirmar:</label>
            <input id="confirm_name" name="confirm_name" class="form-control" type="text" placeholder="Escribe el nombre EXACTO de la base aquí">
            <div id="confirmHelp" class="form-text small text-muted">Nombre de la base: <strong><?= htmlspecialchars($batch['name']) ?></strong></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form id="deleteForm" method="post" action="/backdata/base/delete" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
            <!-- confirm_name es rellenado por el usuario -->
            <button id="deleteBtn" type="submit" class="btn btn-danger" disabled>Eliminar definitivamente</button>
          </form>
        </div>
      </div>
    </div>
   </div>

  <script>
    (function(){
      const input = document.getElementById('confirm_name');
      const btn = document.getElementById('deleteBtn');
      const expected = <?= json_encode($batch['name']) ?>;
      if(!input || !btn) return;
      function check(){
        btn.disabled = input.value !== expected;
      }
      input.addEventListener('input', check);
      // Prevent accidental submit via Enter unless matches
      document.getElementById('deleteForm').addEventListener('submit', function(e){
        if(input.value !== expected){ e.preventDefault(); }
      });
    })();
  </script>

<form class="card mb-3" method="get" action="/backdata/base">
  <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
  <div class="card-body d-flex flex-wrap gap-2 align-items-end">
    <div class="flex-grow-1">
      <label class="form-label">Buscar</label>
      <input class="form-control" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Nombre, teléfono o email">
    </div>
    <div>
      <label class="form-label">Asignación</label>
      <select class="form-select" name="assigned">
        <option value="" <?= ($assigned==='')?'selected':'' ?>>Todos</option>
        <option value="1" <?= ($assigned==='1')?'selected':'' ?>>Asignados</option>
        <option value="0" <?= ($assigned==='0')?'selected':'' ?>>No asignados</option>
      </select>
    </div>
    <div>
      <label class="form-label">Tipificación</label>
      <select class="form-select" name="typed">
        <option value="" <?= (($_GET['typed'] ?? '')==='')?'selected':'' ?>>Todos</option>
        <option value="1" <?= (($_GET['typed'] ?? '')==='1')?'selected':'' ?>>Con status</option>
        <option value="0" <?= (($_GET['typed'] ?? '')==='0')?'selected':'' ?>>Sin status</option>
      </select>
    </div>
    <div>
      <label class="form-label">Status específico</label>
      <input class="form-control" name="status" value="<?= htmlspecialchars($_GET['status'] ?? '') ?>" placeholder="Ej: Interesado">
    </div>
    <div>
      <label class="form-label d-block">&nbsp;</label>
      <button class="btn btn-primary" type="submit">Filtrar</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive" style="width:100%; overflow-x:auto;">
      <table class="table table-striped table-hover mb-0" style="min-width:1200px;">
        <thead><tr>
          <th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Creado</th><th>Asignado a</th><th>Status</th><th>Tipificado por</th><th>Fecha status</th><th>Nota</th>
        </tr></thead>
        <tbody>
          <?php if(empty($leads)): ?>
            <tr><td colspan="11" class="text-center text-muted">Sin resultados</td></tr>
          <?php else: foreach($leads as $l): ?>
            <tr>
              <td>#<?= (int)$l['id'] ?></td>
              <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['source_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['created_at'] ?? '') ?></td>
              <td>
                <?php if(!empty($l['seller_id'])): ?>
                  <?= htmlspecialchars(($l['seller_name'] ?? '')) ?> (<?= '#'.(int)$l['seller_id'] ?>)
                <?php else: ?>-
                <?php endif; ?>
              </td>
              <td><?= status_pill($l['last_status'] ?? '') ?></td>
              <td><?= htmlspecialchars($l['last_status_by'] ?? '-') ?></td>
              <td><?= htmlspecialchars($l['last_status_at'] ?? '-') ?></td>
              <td style="max-width:400px; white-space:normal; overflow-wrap:anywhere;" title="<?= htmlspecialchars($l['last_note'] ?? '-') ?>"><?= htmlspecialchars($l['last_note'] ?? '-') ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


