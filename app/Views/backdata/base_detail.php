<?php /** @var array $batch */ /** @var array $leads */ /** @var string|null $q */ /** @var string|null $assigned */ ?>
<?php ob_start(); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-1">Base #<?= (int)$batch['id'] ?> — <?= htmlspecialchars($batch['name']) ?></h5>
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
    <a href="/backdata/bases" class="btn btn-secondary">Volver</a>
  </div>
  
</div>

<form class="card mb-3" method="get" action="/backdata/base">
  <input type="hidden" name="id" value="<?= (int)$batch['id'] ?>">
  <div class="card-body d-flex gap-2 align-items-end">
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
      <label class="form-label d-block">&nbsp;</label>
      <button class="btn btn-primary" type="submit">Filtrar</button>
    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead><tr>
          <th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Fuente</th><th>Creado</th><th>Asignado a</th>
        </tr></thead>
        <tbody>
          <?php if(empty($leads)): ?>
            <tr><td colspan="7" class="text-center text-muted">Sin resultados</td></tr>
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
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>


