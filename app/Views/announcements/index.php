<?php $content=ob_start(); ?>
<h5 class="mb-3">Anuncios</h5>
<a href="/announcements/create" class="btn btn-sm btn-primary mb-3">Nuevo</a>
<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>ID</th><th>Img</th><th>Título</th><th>Audiencia</th><th>Inicio</th><th>Fin</th><th>Creador</th><th></th></tr></thead>
  <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td>#<?= (int)$r['id'] ?></td>
        <td><?php if(!empty($r['image_path'])): ?><img src="<?= htmlspecialchars($r['image_path']) ?>" style="height:40px;width:60px;object-fit:cover;" alt="img"><?php else: ?>-<?php endif; ?></td>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['audience']) ?></td>
        <td><?= htmlspecialchars($r['starts_at']) ?></td>
        <td><?= htmlspecialchars($r['ends_at'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['creator']) ?></td>
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
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
