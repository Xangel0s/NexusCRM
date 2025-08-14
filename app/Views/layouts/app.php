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
    /* ---- Soft UI polish ---- */
    :root{
      --nx-surface:#ffffff; --nx-border:#e9ecef; --nx-muted:#6c757d;
    }
    .card{border:1px solid var(--nx-border); border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,.04);}
    .card:hover{box-shadow:0 6px 20px rgba(0,0,0,.06); transform:translateY(-1px); transition:all .18s ease;}
    .table{border-radius:12px; overflow:hidden;}
    .table tr:hover{background:#f8fafc;}
    .btn{transition:transform .08s ease, box-shadow .2s ease;}
    .btn:active{transform:scale(.98)}
    .badge{border-radius:8px}
    .form-control,.form-select{border-radius:10px}
  .text-bg-warning{color:#664d03;background-color:#fff3cd}
  .text-bg-danger{color:#842029;background-color:#f8d7da}
  .text-bg-success{color:#0f5132;background-color:#d1e7dd}
  .text-bg-secondary{color:#41464b;background-color:#e2e3e5}
  .text-success-emphasis{color:#0f5132}
  .bg-success-subtle{background-color:#d1e7dd}
  .border-success-subtle{border-color:#badbcc !important}
    /* Sidebar section headers darker style */
    .sidebar .list-group-item.fw-bold.text-uppercase.small{
      background: #f1f3f5;
      color: #343a40;
      letter-spacing: .06em;
      border-top: 1px solid #e9ecef;
      border-bottom: 1px solid #e9ecef;
    }
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

  /* Success check animation (perfectly centered) */
  .checkmark{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#d1e7dd;border:2px solid #198754}
  .checkmark::after{content:"";display:block;width:32px;height:16px;border: solid #198754;border-width:0 0 6px 6px;transform: rotate(-45deg) scale(0);transform-origin:center;animation: pop .35s ease .25s forwards;border-radius:2px}
  @keyframes pop{to{transform: rotate(-45deg) scale(1)}}

  /* Error cross animation */
  .xmark{width:70px;height:70px;border-radius:50%;display:inline-block;position:relative;background:#f8d7da;border:2px solid #dc3545}
  .xmark::before,.xmark::after{content:"";position:absolute;left:20px;top:33px;width:30px;height:4px;background:#dc3545;border-radius:2px;transform-origin:center;opacity:0;animation: crossIn .35s ease .2s forwards}
  .xmark::before{transform:rotate(45deg)}
  .xmark::after{transform:rotate(-45deg)}
  @keyframes crossIn{to{opacity:1}}
  </style>
  </head>
<body class="bg-light">
  <header class="navbar navbar-dark bg-dark brand-bar sticky-top">
    <div class="container-fluid">
  <a class="navbar-brand" href="/home" title="Inicio / Noticias">Nexus</a>
          <?php if($u = auth_user()): ?>
            <div class="ms-auto d-flex align-items-center gap-3 text-white small">
              <span class="fw-semibold"><?= htmlspecialchars($u['name']) ?></span>
              <span class="badge bg-secondary text-uppercase" style="letter-spacing:.5px;"><?= htmlspecialchars(role_label($u['role_name'])) ?></span>
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
        <!-- Modal de error -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 text-center">
              <div class="mx-auto mb-3"><span class="xmark"></span></div>
              <h5 class="mb-2 text-danger">Ocurrió un problema</h5>
              <p class="text-muted mb-0" id="errorModalMsg"><?php echo htmlspecialchars($flash['message']); ?></p>
              <div class="mt-3">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function(){
            var el = document.getElementById('errorModal');
            if(el && window.bootstrap){ new bootstrap.Modal(el).show(); }
          });
        </script>
      <?php elseif($flash['type'] === 'success'): ?>
        <!-- Modal de éxito -->
        <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 text-center">
              <div class="mx-auto mb-3"><span class="checkmark"></span></div>
              <h5 class="mb-2">Operación exitosa</h5>
              <p class="text-muted mb-0" id="statusModalMsg"><?= htmlspecialchars($flash['message']) ?></p>
              <div class="mt-3">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continuar</button>
              </div>
            </div>
          </div>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function(){
            var el = document.getElementById('statusModal');
            if(el && window.bootstrap){ new bootstrap.Modal(el).show(); }
          });
        </script>
      <?php endif; ?>
    <?php endif; ?>
        <?php echo $content ?? ''; ?>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Reusable Preview Modal -->
  <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="previewModalTitle">Detalle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="previewModalBody">
          <div class="text-center text-muted">Cargando…</div>
        </div>
      </div>
    </div>
  </div>
  <script>
  // Open modal and load remote HTML into body
  document.addEventListener('click', function(e){
    const trigger = e.target.closest('[data-modal-fetch]');
    if(!trigger) return;
    e.preventDefault();
    const url = trigger.getAttribute('data-modal-fetch');
    const title = trigger.getAttribute('data-modal-title') || 'Detalle';
    const body = document.getElementById('previewModalBody');
    const titleEl = document.getElementById('previewModalTitle');
    titleEl.textContent = title;
    body.innerHTML = '<div class="text-center text-muted">Cargando…</div>';
    fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r=>r.text())
      .then(html=>{ body.innerHTML = html; })
      .catch(()=>{ body.innerHTML = '<div class="alert alert-danger">No se pudo cargar el contenido.</div>'; });
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
  });

  // Inside modal: handle pagination/limit links for base preview and others
  document.getElementById('previewModal').addEventListener('click', function(e){
    const link = e.target.closest('[data-preview-link]');
    if(link){
      e.preventDefault();
      const body = document.getElementById('previewModalBody');
      fetch(link.href, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ body.innerHTML = html; })
        .catch(()=>{ body.innerHTML = '<div class="alert alert-danger">No se pudo cargar.</div>'; });
      return;
    }
    // Leads day preview links inside modal
    const dayLink = e.target.closest('[data-day-link]');
    if(dayLink){
      e.preventDefault();
      const body = document.getElementById('previewModalBody');
      fetch(dayLink.href, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ body.innerHTML = html; })
        .catch(()=>{ body.innerHTML = '<div class="alert alert-danger">No se pudo cargar.</div>'; });
      return;
    }
    // Seller preview controls (limit and search)
    const limitBtn = e.target.closest('[data-seller-limit]');
    if(limitBtn){
      e.preventDefault();
      const root = document.querySelector('#previewModalBody [data-seller-ctx]');
      if(!root) return;
      const sid = root.getAttribute('data-seller-id');
      const from = root.getAttribute('data-from');
      const to = root.getAttribute('data-to');
      const batchId = root.getAttribute('data-batch-id');
      const searchInput = root.querySelector('.seller-search-input');
      const search = searchInput ? searchInput.value.trim() : '';
      const params = new URLSearchParams({ seller_id:sid, from, to, limit: limitBtn.getAttribute('data-seller-limit') });
      if(batchId) params.set('batch_id', batchId);
      if(search!=='') params.set('search', search);
      const body = document.getElementById('previewModalBody');
      fetch('/backdata/seller/preview?'+params.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ body.innerHTML=html; })
        .catch(()=>{ body.innerHTML='<div class="alert alert-danger">Error al recargar.</div>'; });
      return;
    }
    const searchBtn = e.target.closest('[data-seller-search]');
    if(searchBtn){
      const root = document.querySelector('#previewModalBody [data-seller-ctx]');
      if(!root) return;
      const sid = root.getAttribute('data-seller-id');
      const from = root.getAttribute('data-from');
      const to = root.getAttribute('data-to');
      const batchId = root.getAttribute('data-batch-id');
      const searchInput = root.querySelector('.seller-search-input');
      const search = searchInput ? searchInput.value.trim() : '';
      const params = new URLSearchParams({ seller_id:sid, from, to });
      if(batchId) params.set('batch_id', batchId);
      if(search!=='') params.set('search', search);
      const body = document.getElementById('previewModalBody');
      fetch('/backdata/seller/preview?'+params.toString(), {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.text()).then(html=>{ body.innerHTML=html; })
        .catch(()=>{ body.innerHTML='<div class="alert alert-danger">Error al buscar.</div>'; });
      return;
    }
  });

  // Enter key triggers seller search
  document.getElementById('previewModal').addEventListener('keydown', function(e){
    if(e.key==='Enter' && e.target && e.target.classList.contains('seller-search-input')){
      e.preventDefault();
      const searchBtn = document.querySelector('#previewModal [data-seller-search]');
      if(searchBtn){ searchBtn.click(); }
    }
  });
  </script>
</body></html>
