<?php /** @var array $leads */ /** @var string $status_filter */ /** @var array $statuses */ $content = ob_start(); ?>
<div class="container-fluid">
    <h1 class="mb-1">Mis Leads</h1>
    <p class="text-muted">Gestiona tus leads asignados</p>
    <div class="card">
        <div class="card-body">
                        <form method="get" class="row g-2 align-items-end mb-3">
                                <div class="col-auto">
                                        <label class="form-label mb-1">Status</label>
                                        <select name="status" class="form-select form-select-sm">
                                                <option value="">Todos</option>
                                                <?php foreach($statuses as $s): ?>
                                                        <option value="<?= htmlspecialchars($s) ?>" <?= $status_filter===$s?'selected':'' ?>><?= htmlspecialchars($s) ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                </div>
                                <div class="col-auto">
                                        <button class="btn btn-sm btn-primary">Filtrar</button>
                                </div>
                                <?php if($status_filter!==''): ?>
                                <div class="col-auto">
                                        <a href="/seller/my-leads" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                                </div>
                                <?php endif; ?>
                        </form>
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
                                <td><?= status_pill($l['last_status'] ?? '') ?></td>
                                <td><?= htmlspecialchars($l['assigned_at'] ?? '-') ?></td>
                                                                <td class="d-flex gap-1">
                                                                        <a href="/seller/lead?id=<?= (int)$l['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#releaseModal" data-lead="<?= (int)$l['id'] ?>">Liberar</button>
                                                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Liberar -->
<div class="modal fade" id="releaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Liberar lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que deseas liberar este lead? Ya no volverá a aparecer en tu lista. Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <form method="post" action="/seller/release" id="releaseForm" class="ms-auto">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="lead_id" id="releaseLeadId" value="">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-sm btn-danger">Sí, liberar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('shown.bs.modal', function(e){
    if(e.target.id==='releaseModal'){
        const btn = e.relatedTarget;
        if(btn && btn.getAttribute('data-lead')){
            document.getElementById('releaseLeadId').value = btn.getAttribute('data-lead');
        }
    }
});
</script>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>