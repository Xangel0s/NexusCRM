<?php /** @var array $user */ ?>
<?php $u = auth_user(); ?>
<?php ob_start(); ?>
<div class="container">
  <h2 class="mb-4">Editar usuario</h2>
  <form method="post" action="/users-update">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
      <label for="username" class="form-label">Usuario</label>
      <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Contraseña (dejar vacío para no cambiar)</label>
      <input type="password" class="form-control" id="password" name="password">
    </div>
    <div class="mb-3">
      <label for="role_id" class="form-label">Rol</label>
      <select class="form-select" id="role_id" name="role_id" required>
        <option value="1" <?php if(($user['role_id']??'')==1) echo 'selected'; ?>>Administrador</option>
        <option value="2" <?php if(($user['role_id']??'')==2) echo 'selected'; ?>>Backdata Manager</option>
        <option value="3" <?php if(($user['role_id']??'')==3) echo 'selected'; ?>>Backdata</option>
        <option value="4" <?php if(($user['role_id']??'')==4) echo 'selected'; ?>>Vendedor</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="active" class="form-label">Estado</label>
      <select class="form-select" id="active" name="active" required>
        <option value="1" <?php if(($user['active']??'')==1) echo 'selected'; ?>>Activo</option>
        <option value="0" <?php if(($user['active']??'')==0) echo 'selected'; ?>>Desactivado</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar cambios</button>
    <a href="/users" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/app.php'; ?>
