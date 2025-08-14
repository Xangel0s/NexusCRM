<?php /** @var array $lead */ /** @var array $activities */ $content = ob_start(); ?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Lead #<?= (int)$lead['id'] ?> - <?= htmlspecialchars($lead['full_name'] ?? '') ?></h1>
    <a href="/seller/my-leads" class="btn btn-sm btn-outline-secondary">Volver</a>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <h5 class="card-title mb-3">Datos</h5>
        <dl class="row mb-0">
          <dt class="col-4">Nombre</dt><dd class="col-8"><?= htmlspecialchars($lead['full_name'] ?? '-') ?></dd>
          <dt class="col-4">Teléfono</dt><dd class="col-8"><?= htmlspecialchars($lead['phone'] ?? '-') ?></dd>
          <dt class="col-4">Email</dt><dd class="col-8"><?= htmlspecialchars($lead['email'] ?? '-') ?></dd>
          <dt class="col-4">Estado actual</dt><dd class="col-8"><?= status_pill($lead['status'] ?? '') ?></dd>
          <dt class="col-4">Asignado</dt><dd class="col-8"><?= htmlspecialchars($lead['assigned_at'] ?? '-') ?></dd>
        </dl>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <h5 class="card-title mb-3">Nueva actividad</h5>
        <form method="post" action="/seller/tipify" class="vstack gap-2">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
          <div>
            <label class="form-label">Estado</label>
            <select name="status" class="form-select form-select-sm" required>
              <option value="">Seleccione...</option>
              <option value="Contactado">Contactado</option>
              <option value="Interesado">Interesado</option>
              <option value="No responde">No responde</option>
              <option value="Venta cerrada">Venta cerrada</option>
            </select>
          </div>
          <div>
            <label class="form-label">Nota (opcional)</label>
            <textarea name="note" class="form-control form-control-sm" rows="3" maxlength="2000"></textarea>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm">Guardar</button>
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#releaseModal" data-lead="<?= (int)$lead['id'] ?>">Liberar</button>
          </div>
        </form>
      </div></div>
    </div>
  </div>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-3">Histórico de actividades</h5>
      <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
          <thead><tr><th title="ID de la actividad">Act #</th><th>Estado</th><th>Nota</th><th>Usuario</th><th>Fecha</th></tr></thead>
          <tbody>
            <?php if(empty($activities)): ?>
              <tr><td colspan="5" class="text-center text-muted py-3">Sin actividades</td></tr>
            <?php else: foreach($activities as $a): ?>
              <tr>
                <td title="ID de la actividad">#<?= (int)$a['id'] ?></td>
                <td><?= status_pill($a['status']) ?></td>
                <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($a['note'] ?? '-') ?>"><?= htmlspecialchars($a['note'] ?? '-') ?></td>
                <td><?= htmlspecialchars($a['user_name']) ?></td>
                <td><?= htmlspecialchars($a['created_at']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- Modal Liberar (detalle) -->
<div class="modal fade" id="releaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Liberar lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Al liberar este lead ya no aparecerá en tu listado y no podrás recuperarlo. ¿Deseas continuar?</p>
      </div>
      <div class="modal-footer">
        <form method="post" action="/seller/release" id="releaseFormDetail">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-sm btn-danger">Sí, liberar</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/../layouts/app.php'; ?>
