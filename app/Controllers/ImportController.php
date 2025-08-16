<?php
namespace App\Controllers;
use PDO; use function db; use function view; use function auth_user; use function csrf_check; use function csrf_token; use function flash;

class ImportController{
  private function requireBD(): void {
    $u = auth_user();
    if(!$u || !in_array($u['role_name'], ['admin','backdata_manager','backdata'])){ http_response_code(403); exit('Forbidden'); }
  }

  public function importForm(){ $this->requireBD(); view('backdata/import_form'); }

  public function importParse(){
    $this->requireBD();
    // If called via GET (e.g. pagination links accidentally point here), redirect to preview
    if ($_SERVER['REQUEST_METHOD'] === 'GET'){
      $qs = $_SERVER['QUERY_STRING'] ?? '';
      $loc = '/backdata/import/preview' . ($qs ? '?'.$qs : '');
      header('Location: ' . $loc);
      exit;
    }
    csrf_check();
    if(empty($_FILES['csv']['tmp_name'])){ flash('error','Sube un archivo CSV'); header('Location: /backdata/import'); return; }
    $baseName = trim($_POST['base_name'] ?? ''); if($baseName===''){ flash('error','Ingresa el nombre de la Base'); header('Location: /backdata/import'); return; }
    $tags = trim($_POST['tags'] ?? '');
    $fh = fopen($_FILES['csv']['tmp_name'], 'r'); if(!$fh){ flash('error','No se pudo leer el CSV'); header('Location: /backdata/import'); return; }
    // Detectar delimitador (coma, punto y coma o tab) para compatibilidad con Excel en español
    $probe = fgets($fh);
    if($probe === false){ flash('error','El archivo está vacío'); header('Location: /backdata/import'); return; }
    $comma = substr_count($probe, ',');
    $semicolon = substr_count($probe, ';');
    $tab = substr_count($probe, "\t");
    $delim = ','; if($semicolon > $comma && $semicolon >= $tab){ $delim = ';'; } elseif($tab > max($comma,$semicolon)){ $delim = "\t"; }
    rewind($fh);
    $rows=[]; $line=0; while(($data=fgetcsv($fh, 100000, $delim))!==false){ $line++; if($line==1 && isset($_POST['has_header'])){ continue; }
      $full_name = trim($data[0] ?? '');
      $phone = preg_replace('/[^0-9+]/','', trim($data[1] ?? ''));
      $email = trim($data[2] ?? '');
      $source = trim($data[3] ?? '');
      if($phone===''){ $rows[]=['full_name'=>$full_name,'phone'=>$phone,'email'=>$email,'source'=>$source,'status'=>'invalid']; continue; }
      $rows[]=['full_name'=>$full_name,'phone'=>$phone,'email'=>$email,'source'=>$source,'status'=>'pending'];
    }
    fclose($fh);
    // Check duplicates (only if not allowed)
    $allowDuplicates = isset($_POST['allow_duplicates']) && $_POST['allow_duplicates']=='on';
    if($rows && !$allowDuplicates){
      $in = implode(',', array_fill(0, count($rows), '?'));
      $phones = array_column($rows,'phone');
      $stmt = db()->prepare("SELECT phone FROM leads WHERE phone IN ($in)");
      $stmt->execute($phones); $exists = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));
      foreach($rows as &$r){ if($r['status']==='pending' && isset($exists[$r['phone']])) $r['status']='duplicate'; }
    }
    $_SESSION['import_rows'] = $rows;
    $_SESSION['import_base_name'] = $baseName;
    $_SESSION['import_tags'] = $tags;
    view('backdata/import_preview', [
      'rows'=>$rows,
      'base_name'=>$baseName,
      'tags'=>$tags,
      'allow_duplicates'=>$allowDuplicates,
      'counts'=>[
        'total'=>count($rows),
        'pending'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='pending')),
        'duplicates'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='duplicate')),
        'invalid'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='invalid')),
      ],
      'csrf'=>csrf_token(),
    ]);
  }

  public function importCommit(){ $this->requireBD(); csrf_check();
    $rows = $_SESSION['import_rows'] ?? [];
    $baseName = $_SESSION['import_base_name'] ?? '';
    $tags = $_SESSION['import_tags'] ?? '';
    $allowDuplicates = isset($_POST['allow_duplicates']) && $_POST['allow_duplicates']=='1';
    unset($_SESSION['import_rows'], $_SESSION['import_base_name'], $_SESSION['import_tags']);
    if(!$rows){ flash('error','No hay datos a importar'); header('Location: /backdata/import'); return; }
    if($baseName===''){ $baseName = 'Base '.date('Y-m-d H:i'); }
    $db = db();
    $db->beginTransaction();
    try{
      // Create batch
      $stmtB = $db->prepare('INSERT INTO import_batches(name, tags, created_by) VALUES(?,?,?)');
      $stmtB->execute([$baseName, $tags, auth_user()['id']]);
      $batchId = (int)$db->lastInsertId();
      // Insert leads linked to batch
      $ins = $db->prepare('INSERT INTO leads(full_name,phone,email,source_name,batch_id,imported_at) VALUES(?,?,?,?,?,NOW())');
      $inserted=0; foreach($rows as $r){
        $isValid = ($r['status']??'')==='pending' || ($allowDuplicates && ($r['status']??'')==='duplicate');
        if($isValid){
          try{ $ins->execute([$r['full_name'],$r['phone'],$r['email'],$r['source'],$batchId]); $inserted++; }catch(\Throwable $e){ /* ignore per-row */ }
        }
      }
      $db->commit();
    }catch(\Throwable $e){ $db->rollBack(); flash('error','Error creando la Base: '.$e->getMessage()); header('Location: /backdata/import'); return; }
    
    // Crear anuncio automático (visible a todos) indicando nueva base
    // Ahora opcional: se crea solo si el formulario envía create_announcement=1
    $createAnnouncement = isset($_POST['create_announcement']) && $_POST['create_announcement']=='1';
    if($createAnnouncement){
      try{
        $db->exec("CREATE TABLE IF NOT EXISTS announcements (id BIGINT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,body TEXT NOT NULL,audience VARCHAR(100) NOT NULL DEFAULT 'all',starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,ends_at DATETIME NULL,created_by INT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,KEY idx_ann_active (starts_at, ends_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $msg = 'Se importó la base "'.preg_replace('/["<>]/','',$baseName).'" con '.$inserted.' leads.';
        $stmtAnn = $db->prepare('INSERT INTO announcements(title,body,audience,starts_at,created_by) VALUES(?,?,?,?,?)');
        $stmtAnn->execute(['Nueva base: '.$baseName,$msg,'all',date('Y-m-d H:i:s'),auth_user()['id']]);
      }catch(\Throwable $e){ /* ignorar */ }
    }
    flash('success', 'Importados: '.$inserted);
    header('Location: /backdata/bases');
  }

  // Remove selected rows from the staged import (session)
  public function importRemoveSelected(){ $this->requireBD(); csrf_check();
    $selected = array_filter(array_map('intval', $_POST['selected_indices'] ?? []));
  if(empty($selected)){ flash('error','No hay filas seleccionadas'); header('Location: /backdata/import/preview'); return; }
    $rows = $_SESSION['import_rows'] ?? [];
  if(!$rows){ flash('error','No hay datos en staging'); header('Location: /backdata/import/preview'); return; }
    // Remove by offset indices safely
    $new = [];
    foreach($rows as $i => $r){ if(!in_array($i, $selected, true)) $new[] = $r; }
  $_SESSION['import_rows'] = $new;
  flash('success','Filas eliminadas: '.count($selected));
  $limit = (int)($_POST['limit'] ?? 50); $limit = in_array($limit,[20,50,100,500])?$limit:50;
  $page = max(1, (int)($_POST['page'] ?? 1));
  header('Location: /backdata/import/preview?limit='.$limit.'&page='.$page);
  }

    // GET handler to show the import preview (paginated view of staged rows)
    public function importPreview(){
      $this->requireBD();
      $rows = $_SESSION['import_rows'] ?? [];
      $baseName = $_SESSION['import_base_name'] ?? '';
      $tags = $_SESSION['import_tags'] ?? '';
      $allowDuplicates = false; // can't reliably know; default false
      $counts = [
        'total'=>count($rows),
        'pending'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='pending')),
        'duplicates'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='duplicate')),
        'invalid'=>count(array_filter($rows, fn($r)=>($r['status']??'')==='invalid')),
      ];

      // normalize limit/page from GET, clamp and redirect if out of range
      $limit = (int)($_GET['limit'] ?? 50); $limit = in_array($limit, [20,50,100,500]) ? $limit : 50;
      $page = max(1, (int)($_GET['page'] ?? 1));
      $total = count($rows);
      $pages = $total > 0 ? max(1, (int)ceil($total / $limit)) : 1;
      if ($page > $pages) {
        // redirect to last valid page
        header('Location: /backdata/import/preview?limit='.$limit.'&page='.$pages);
        exit;
      }

      view('backdata/import_preview', [
        'rows'=>$rows,
        'base_name'=>$baseName,
        'tags'=>$tags,
        'allow_duplicates'=>$allowDuplicates,
        'counts'=>$counts,
        'csrf'=>csrf_token(),
        'limit'=>$limit,
        'page'=>$page,
      ]);
    }
}
