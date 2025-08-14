<?php $content = ob_start(); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><i class="bi bi-people-fill me-2"></i>Usuarios</h1>
        <a href="/users-create" class="btn btn-primary">Nuevo usuario</a>
    </div>
    
    <form method="get" action="/users" class="mb-3">
        <div class="input-group" style="max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o usuario" value="<?= htmlspecialchars($search ?? '') ?>">
            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><a href="?order=id" class="text-decoration-none text-dark">ID <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="?order=name" class="text-decoration-none text-dark">Nombre <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="?order=username" class="text-decoration-none text-dark">Usuario <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="?order=role" class="text-decoration-none text-dark">Rol <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="?order=active" class="text-decoration-none text-dark">Estado <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars(role_label($user['role_name'])) ?></td>
                    <td>
                        <span class="badge bg-<?= $user['active'] ? 'success' : 'danger' ?>">
                            <?= $user['active'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <a href="/users-edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form method="post" action="/users-toggle" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                <?= $user['active'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>