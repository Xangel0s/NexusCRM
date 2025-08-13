<?php /** @var array $data */ ?>
<?php $csrf = csrf_token(); ?>
<?php ob_start(); ?>
<div class="container-fluid">
  <div class="row mb-3">
    <div class="col"><h2>Importar Leads (CSV)</h2></div>
  </div>
  <div class="row">
    <div class="col-md-8 col-lg-6">
      <div class="card">
        <div class="card-body">
          <form action="/backdata/import/parse" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="mb-3">
              <label class="form-label">Archivo CSV</label>
              <input class="form-control" type="file" name="csv" accept=".csv" required>
              <div class="form-text">Formato esperado: name, phone, email, source. Acepta separadores coma, punto y coma o tabulación. Primera fila puede ser encabezado.</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Nombre de Base</label>
              <input class="form-control" type="text" name="base_name" placeholder="Ej. Campaña Julio" required>
              <div class="form-text">Este nombre identificará el lote de importación (Base).</div>
            </div>
            <div class="mb-3">
              <label class="form-label">Etiquetas (opcional)</label>
              <input class="form-control" type="text" name="tags" placeholder="Ej. campaña:julio, canal:facebook">
              <div class="form-text">Puedes separar por comas para facilitar los filtros.</div>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="has_header" id="has_header" checked>
              <label class="form-check-label" for="has_header">El CSV tiene encabezado</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="allow_duplicates" id="allow_duplicates">
              <label class="form-check-label" for="allow_duplicates">Permitir importar duplicados (mismo teléfono)</label>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit">Previsualizar</button>
              <a class="btn btn-secondary" href="/backdata/summary">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
