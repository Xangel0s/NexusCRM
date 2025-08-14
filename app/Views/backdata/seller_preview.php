<?php /** Simplificada: solo búsqueda por nombre, teléfono o base. */ ?>
<?php /** @var array $rows */ /** @var int $limit */ /** @var string $from */ /** @var string $to */ /** @var int $seller_id */ /** @var int|null $batch_id */ /** @var string|null $error */ /** @var string $search */ ?>
<div class="p-2">
  <?php if(isset($error) && $error): ?>
    <div class="alert alert-danger text-center" style="font-size:1.2em; padding:32px;">Error: <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted">Mostrar</span>
    <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="#" data-seller-limit="20">20</a>
    <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="#" data-seller-limit="50">50</a>
    <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="#" data-seller-limit="100">100</a>
    <input type="text" class="form-control form-control-sm ms-auto" id="seller-search-input" placeholder="Nombre, teléfono o base" value="<?= htmlspecialchars($search) ?>" style="max-width:240px;" />
    <button class="btn btn-sm btn-outline-secondary" type="button" id="seller-search-btn">Buscar</button>
  </div>
  <div class="table-responsive" style="overflow-x:auto;">
    <table class="table table-sm table-striped mb-0" style="min-width: 1000px;">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Base</th><th>Etiquetas</th><th>Asignado</th><th>Último estado</th><th>Por</th><th>Fecha</th><th>Nota</th></tr></thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="11" class="text-center text-muted" style="background:#fffbe6; padding:32px; font-size:1.2em;">Sin resultados</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['full_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['base_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['base_tags'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['assigned_at'] ?? '') ?></td>
            <td><?= status_pill($r['last_status'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['last_by'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['last_at'] ?? '-') ?></td>
            <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($r['last_note'] ?? '-') ?>"><?= htmlspecialchars($r['last_note'] ?? '-') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


