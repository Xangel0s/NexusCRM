<?php /** @var array $roles */ ?>
<?php $u = auth_user(); ?>
<?php ob_start(); ?>
<div class="container">
  <h2 class="mb-4">Crear usuario</h2>
  <form method="post" action="/users-store">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
      <label for="username" class="form-label">Usuario</label>
      <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="mb-3">
      <label for="role_id" class="form-label">Rol</label>
      <select class="form-select" id="role_id" name="role_id" required>
        <option value="">Selecciona un rol</option>
        <option value="1">Administrador</option>
        <option value="2">Backdata Manager</option>
        <option value="3">Backdata</option>
        <option value="4">Vendedor</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Crear usuario</button>
    <a href="/users" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
