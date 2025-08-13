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
    /* Visibilidad mejorada para controles del carrusel de anuncios */
    .carousel-dark-arrows .carousel-control-prev-icon,
    .carousel-dark-arrows .carousel-control-next-icon{
      background-color:rgba(0,0,0,.65);
      border-radius:50%;
      width:3rem; height:3rem;
      background-size:55% 55%;
      background-position:center; background-repeat:no-repeat;
      filter:none; opacity:1; box-shadow:0 0 6px rgba(0,0,0,.6);
    }
    .carousel-dark-arrows .carousel-control-prev,
    .carousel-dark-arrows .carousel-control-next{ width:4rem; }
    .carousel-dark-arrows .carousel-control-prev-icon{filter: invert(1);} /* hace flecha blanca */
    .carousel-dark-arrows .carousel-control-next-icon{filter: invert(1);}    
  </style>
  </head>
<body class="bg-light">
  <header class="navbar navbar-dark bg-dark brand-bar sticky-top">
    <div class="container-fluid">
  <a class="navbar-brand" href="/home" title="Inicio / Noticias">Nexus</a>
          <?php if($u = auth_user()): ?>
            <div class="ms-auto d-flex align-items-center gap-3 text-white small">
              <span class="fw-semibold"><?= htmlspecialchars($u['name']) ?></span>
              <span class="badge bg-secondary text-uppercase" style="letter-spacing:.5px;"><?= htmlspecialchars($u['role_name']) ?></span>
              <form method="post" action="/logout" class="d-inline m-0 p-0">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="btn btn-outline-light btn-sm">Salir</button>
              </form>
            </div>
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
            <a class="list-group-item list-group-item-action" href="/backdata/bases/progreso">Progreso Bases</a>
            <a class="list-group-item list-group-item-action" href="/backdata/sellers">Productividad</a>
            <a class="list-group-item list-group-item-action" href="/backdata/leads">Leads</a>
            <a class="list-group-item list-group-item-action" href="/backdata/assign">Asignar</a>
            <a class="list-group-item list-group-item-action" href="/backdata/import">Importar</a>
            <a class="list-group-item list-group-item-action" href="/announcements">Anuncios</a>
          <?php elseif(in_array($u['role_name'], ['backdata_manager','backdata'])): ?>
            <div class="list-group-item fw-bold text-uppercase small">Backdata</div>
            <a class="list-group-item list-group-item-action" href="/backdata/summary">Resumen</a>
            <a class="list-group-item list-group-item-action" href="/backdata/bases">Bases</a>
            <a class="list-group-item list-group-item-action" href="/backdata/bases/progreso">Progreso Bases</a>
            <a class="list-group-item list-group-item-action" href="/backdata/sellers">Productividad</a>
            <a class="list-group-item list-group-item-action" href="/backdata/leads">Leads</a>
            <a class="list-group-item list-group-item-action" href="/backdata/assign">Asignar</a>
            <a class="list-group-item list-group-item-action" href="/backdata/import">Importar</a>
            <a class="list-group-item list-group-item-action" href="/announcements">Anuncios</a>
          <?php elseif($u['role_name']==='seller'): ?>
            <div class="list-group-item fw-bold text-uppercase small">Vendedor</div>
            <a class="list-group-item list-group-item-action" href="/seller/my-leads">Mis Leads</a>
          <?php endif; ?>
        </div>
      </aside>
      <?php endif; ?>

      <main class="col p-3">
    <?php if($flash = get_flash()): ?>
      <?php if($flash['type'] === 'error'): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($flash['message']); ?></div>
      <?php elseif($flash['type'] === 'success'): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($flash['message']); ?></div>
      <?php endif; ?>
    <?php endif; ?>
        <?php echo $content ?? ''; ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
