<?php
namespace App\Controllers;
use PDO; use function db; use function view; use function auth_user; use function csrf_check; use function csrf_token; use function flash;

class ImportController{
  private function requireBD(): void {
    $u = auth_user();
    if(!$u || !in_array($u['role_name'], ['admin','backdata_manager','backdata'])){ http_response_code(403); exit('Forbidden'); }
  }

  public function importForm(){ $this->requireBD(); view('backdata/import_form'); }

  public function importParse(){ $this->requireBD(); csrf_check();
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
    // Check duplicates
    if($rows){
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
      $inserted=0; foreach($rows as $r){ if(($r['status']??'')==='pending'){ try{ $ins->execute([$r['full_name'],$r['phone'],$r['email'],$r['source'],$batchId]); $inserted++; }catch(\Throwable $e){ /* ignore per-row */ } } }
      $db->commit();
    }catch(\Throwable $e){ $db->rollBack(); flash('error','Error creando la Base: '.$e->getMessage()); header('Location: /backdata/import'); return; }
    
    flash('success', 'Importados: '.$inserted);
    header('Location: /backdata/bases');
  }
}
