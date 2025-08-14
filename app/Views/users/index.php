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
                    <th class="sortable" data-col="id">ID <i class="bi bi-arrow-down-up"></i></th>
                    <th class="sortable" data-col="name">Nombre <i class="bi bi-arrow-down-up"></i></th>
                    <th class="sortable" data-col="username">Usuario <i class="bi bi-arrow-down-up"></i></th>
                    <th class="sortable" data-col="role">Rol <i class="bi bi-arrow-down-up"></i></th>
                    <th class="sortable" data-col="active">Estado <i class="bi bi-arrow-down-up"></i></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
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
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    const tbody = document.getElementById('usersTableBody');
    let currentSort = { col: null, dir: null };
    function getCellValue(row, col) {
        if(col === 'active') return row.querySelector('span.badge').textContent.trim();
        if(col === 'role') return row.children[3].textContent.trim();
        return row.querySelector(`[data-col='${col}']`) ? row.querySelector(`[data-col='${col}']`).textContent.trim() : row.children[table.querySelector(`th[data-col='${col}']`).cellIndex].textContent.trim();
    }
    function sortTable(col, dir) {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            let va = getCellValue(a, col);
            let vb = getCellValue(b, col);
            if(col === 'id') { va = parseInt(va); vb = parseInt(vb); }
            if(dir === 'asc') return va > vb ? 1 : va < vb ? -1 : 0;
            else return va < vb ? 1 : va > vb ? -1 : 0;
        });
        rows.forEach(r => tbody.appendChild(r));
    }
    table.querySelectorAll('th.sortable').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            const col = th.getAttribute('data-col');
            let dir = 'desc';
            if(currentSort.col === col && currentSort.dir === 'desc') dir = 'asc';
            else if(currentSort.col === col && currentSort.dir === 'asc') dir = null;
            currentSort = { col: dir ? col : null, dir };
            table.querySelectorAll('th.sortable i').forEach(i => { i.className = 'bi bi-arrow-down-up'; });
            if(dir === 'desc') th.querySelector('i').className = 'bi bi-arrow-down';
            else if(dir === 'asc') th.querySelector('i').className = 'bi bi-arrow-up';
            if(dir) sortTable(col, dir); else location.reload();
        });
    });
});
</script>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>