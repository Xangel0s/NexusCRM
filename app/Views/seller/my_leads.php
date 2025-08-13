<?php /** @var array $leads */ $content = ob_start(); ?>
<div class="container-fluid">
    <h1 class="mb-1">Mis Leads</h1>
    <p class="text-muted">Gestiona tus leads asignados</p>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Estado actual</th>
                            <th>Asignado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($leads)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No tienes leads asignados actualmente</td></tr>
                        <?php else: foreach($leads as $l): ?>
                            <tr>
                                <td>#<?= (int)$l['id'] ?></td>
                                <td><?= htmlspecialchars($l['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['last_status'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($l['assigned_at'] ?? '-') ?></td>
                                <td><a href="/seller/lead?id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-primary">Ver</a></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>