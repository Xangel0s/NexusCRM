<?php $content=ob_start(); ?>
<h5 class="mb-3">Nuevo Anuncio</h5>
<form method="post" action="/announcements/store" class="vstack gap-3" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
  <div>
    <label class="form-label">Título</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div>
    <label class="form-label">Contenido (HTML simple permitido)</label>
    <textarea name="body" class="form-control" rows="5" required></textarea>
  </div>
  <div>
    <label class="form-label">Audiencia</label>
    <select name="audience" class="form-select">
      <option value="all">Todos</option>
      <option value="seller">Solo vendedores</option>
      <option value="backdata">Backdata</option>
      <option value="admin">Solo admin</option>
    </select>
  </div>
  <div>
    <label class="form-label">Imagen (opcional)</label>
    <input type="file" name="image" accept="image/*" class="form-control">
    <div class="form-text">Sube un archivo o deja vacío y usa una URL externa abajo.</div>
  </div>
  <div>
    <label class="form-label">URL de Imagen (opcional)</label>
    <input type="url" name="image_url" placeholder="https://..." class="form-control">
    <div class="form-text">Se usará solo si no subes archivo. Debe comenzar con http(s).</div>
  </div>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Inicio</label>
      <input type="datetime-local" name="starts_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Fin (opcional)</label>
      <input type="datetime-local" name="ends_at" class="form-control">
    </div>
  </div>
  <button class="btn btn-primary">Guardar</button>
</form>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
