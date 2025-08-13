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
        <div class="card-header">Muestra (primeros 50)</div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Teléfono</th>
                  <th>Email</th>
                  <th>Fuente</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
              <?php $i=0; foreach($rows as $r): if(++$i>50) break; ?>
                <tr>
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
      <form method="post" action="/backdata/import/commit" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="allow_duplicates" value="<?= $allow_duplicates? '1':'0' ?>">
        <div class="d-flex gap-2">
          <a href="/backdata/import" class="btn btn-light">Volver</a>
          <button class="btn btn-primary" type="submit">Confirmar importación</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
