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
         <?php if(isset($noResults) && $noResults): ?>
          <tr><td colspan="9" class="text-center" style="color: #222;">No se encontraron resultados para la búsqueda.</td></tr>
        <?php elseif(empty($batches)): ?>
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
              <a class="btn btn-sm btn-outline-secondary" href="#" data-modal-fetch="/backdata/base/preview?id=<?= (int)$b['id'] ?>&limit=20" data-modal-title="Base #<?= (int)$b['id'] ?> - Preview">Abrir información</a>
            </td>
          </tr>
          
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script></script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
