<?php /** @var array $announcements */ $content = ob_start(); ?>
<div class="container-fluid">
    <h1 class="mb-1">Panel de Noticias</h1>
    <p class="text-muted">Actualizaciones y anuncios internos</p>
    <?php if(!empty($announcements)): ?>
    <div id="annCarousel" class="carousel slide mb-4 carousel-dark-arrows" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($announcements as $i=>$a): ?>
                <div class="carousel-item <?= $i===0?'active':'' ?>">
                    <div class="card shadow-sm">
                        <div class="card-body">
                                                                                        <?php if(!empty($a['image_path'])): ?>
                                                                                                <?php
                                                                                                    $img = $a['image_path'];
                                                                                                    // Normalizar: si es ruta local y no existe, intentar prefijo /public
                                                                                                    if(!preg_match('#^https?://#i',$img)){
                                                                                                        $doc = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '','/');
                                                                                                        if($doc){
                                                                                                            if(!file_exists($doc.$img) && file_exists($doc.'/public'.$img)){
                                                                                                                $img = '/public'.$img; // fallback
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                ?>
                                                                                                <div class="mb-3 text-center">
                                                                                                        <img src="<?= htmlspecialchars($img) ?>" alt="img" class="img-fluid" style="max-height:260px;object-fit:contain;">
                                                                                                </div>
                                                                                        <?php endif; ?>
                                            <h5 class="card-title mb-2"><?= htmlspecialchars($a['title']) ?></h5>
                            <div class="small text-muted mb-2">Vigente desde <?= htmlspecialchars($a['starts_at']) ?><?= $a['ends_at']? ' hasta '.htmlspecialchars($a['ends_at']) : '' ?></div>
                            <div class="card-text" style="max-height:260px; overflow:auto;"><?= nl2br(htmlspecialchars($a['body'])) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#annCarousel" data-bs-slide="prev" style="filter:drop-shadow(0 0 4px #000);opacity:.9"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previo</span></button>
    <button class="carousel-control-next" type="button" data-bs-target="#annCarousel" data-bs-slide="next" style="filter:drop-shadow(0 0 4px #000);opacity:.9"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Siguiente</span></button>
    </div>
    <?php else: ?>
        <div class="alert alert-info">Sin anuncios activos</div>
    <?php endif; ?>
    <div class="row g-3">
        <?php $u = auth_user(); if($u && in_array($u['role_name'], ['admin','backdata_manager','backdata'])): ?>
        <div class="col-md-6">
            <div class="card"><div class="card-body"><h5 class="card-title">Accesos</h5><p class="mb-0">Utiliza el menú lateral según tu rol.</p></div></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require __DIR__.'/layouts/app.php'; ?>