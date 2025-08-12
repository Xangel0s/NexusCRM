<?php /** @var array $rows */ /** @var int $limit */ /** @var string $from */ /** @var string $to */ /** @var int $seller_id */ /** @var string $status */ /** @var array $statuses */ /** @var int|null $batch_id */ ?>
<div class="p-2">
  <div class="d-flex align-items-center gap-2 mb-2">
    <span class="small text-muted">Mostrar</span>
    <a class="btn btn-sm <?= $limit==20?'btn-primary':'btn-outline-primary' ?>" href="/backdata/seller/preview?seller_id=<?= (int)$seller_id ?>&from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?><?php if($batch_id): ?>&batch_id=<?= (int)$batch_id ?><?php endif; ?>&limit=20" data-seller-link>20</a>
    <a class="btn btn-sm <?= $limit==50?'btn-primary':'btn-outline-primary' ?>" href="/backdata/seller/preview?seller_id=<?= (int)$seller_id ?>&from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?><?php if($batch_id): ?>&batch_id=<?= (int)$batch_id ?><?php endif; ?>&limit=50" data-seller-link>50</a>
    <a class="btn btn-sm <?= $limit==100?'btn-primary':'btn-outline-primary' ?>" href="/backdata/seller/preview?seller_id=<?= (int)$seller_id ?>&from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?><?php if($batch_id): ?>&batch_id=<?= (int)$batch_id ?><?php endif; ?>&limit=100" data-seller-link>100</a>
    <span class="ms-auto small text-muted">Filtrar por tipificación</span>
    <select class="form-select form-select-sm" onchange="location.href='/backdata/seller/preview?seller_id=<?= (int)$seller_id ?>&from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?><?php if($batch_id): ?>&batch_id=<?= (int)$batch_id ?><?php endif; ?>&limit=<?= (int)$limit ?>&status='+encodeURIComponent(this.value)" style="width:auto">
      <option value="" <?= $status===''?'selected':'' ?>>Todos</option>
      <?php foreach($statuses as $st): ?>
        <option value="<?= htmlspecialchars($st) ?>" <?= $status===$st?'selected':'' ?>><?= htmlspecialchars($st) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-striped mb-0">
      <thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Email</th><th>Base</th><th>Asignado</th><th>Último estado</th><th>Por</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php if(empty($rows)): ?>
          <tr><td colspan="9" class="text-center text-muted">Sin resultados</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['full_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['base_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['assigned_at'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['last_status'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['last_by'] ?? '-') ?></td>
            <td><?= htmlspecialchars($r['last_at'] ?? '-') ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>


