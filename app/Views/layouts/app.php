<?php $u = auth_user(); ?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nexus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{min-height:100vh;}
    .sidebar{min-height:100vh;}
    .sidebar .nav-link{color:#333;}
    .sidebar .nav-link.active{background:#0d6efd;color:#fff;}
    .brand-bar{height:56px}
  </style>
  </head>
<body class="bg-light">
  <header class="navbar navbar-dark bg-dark brand-bar sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">Nexus</a>
      <?php if($u): ?>
      <form method="post" action="/logout" class="m-0">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <button class="btn btn-sm btn-outline-light">Salir</button>
      </form>
      <?php endif; ?>
    </div>
  </header>

  <div class="container-fluid">
    <div class="row">
      <?php if($u): ?>
      <aside class="col-12 col-md-3 col-lg-2 bg-white border-end sidebar p-0">
        <div class="list-group list-group-flush">
          <?php if($u['role_name']==='admin'): ?>
            <div class="list-group-item fw-bold text-uppercase small">Administración</div>
            <a class="list-group-item list-group-item-action" href="/users">Usuarios</a>
            <div class="list-group-item fw-bold text-uppercase small">Backdata</div>
            <a class="list-group-item list-group-item-action" href="/backdata/summary">Resumen</a>
            <a class="list-group-item list-group-item-action" href="/backdata/bases">Bases</a>
            <a class="list-group-item list-group-item-action" href="/backdata/leads">Leads</a>
            <a class="list-group-item list-group-item-action" href="/backdata/assign">Asignar</a>
            <a class="list-group-item list-group-item-action" href="/backdata/import">Importar</a>
          <?php elseif(in_array($u['role_name'], ['backdata_manager','backdata'])): ?>
            <div class="list-group-item fw-bold text-uppercase small">Backdata</div>
            <a class="list-group-item list-group-item-action" href="/backdata/summary">Resumen</a>
            <a class="list-group-item list-group-item-action" href="/backdata/bases">Bases</a>
            <a class="list-group-item list-group-item-action" href="/backdata/leads">Leads</a>
            <a class="list-group-item list-group-item-action" href="/backdata/assign">Asignar</a>
            <a class="list-group-item list-group-item-action" href="/backdata/import">Importar</a>
          <?php elseif($u['role_name']==='seller'): ?>
            <div class="list-group-item fw-bold text-uppercase small">Vendedor</div>
            <a class="list-group-item list-group-item-action" href="/seller/my-leads">Mis Leads</a>
          <?php endif; ?>
        </div>
      </aside>
      <?php endif; ?>

      <main class="col p-3">
        <?php if($m=flash('error')): ?><div class="alert alert-danger"><?php echo htmlspecialchars($m); ?></div><?php endif; ?>
        <?php if($m=flash('success')): ?><div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div><?php endif; ?>
        <?php echo $content ?? ''; ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
