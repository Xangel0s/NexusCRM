<?php
namespace App\Controllers;
use PDO; use function db; use function view; use function auth_user; use function csrf_check; use function csrf_token; use function flash;

class BackdataController{
  public function baseUnarchive(){
    $this->requireBD();
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if(!$id){ header('Location: /backdata/bases'); return; }
    db()->prepare('UPDATE import_batches SET archived_at=NULL WHERE id=? AND archived_at IS NOT NULL')->execute([$id]);
    flash('success','Base desarchivada');
    header('Location: /backdata/bases');
  }
  private function requireBD(): void {
    $u = auth_user();
    if(!$u || !in_array($u['role_name'], ['admin','backdata_manager','backdata'])){ http_response_code(403); exit('Forbidden'); }
  }

  public function summary(){ $this->requireBD();
    $today = date('Y-m-d');
    $dbh = db();
    // Asignados hoy (por assigned_at)
    $assigned = $dbh->prepare("SELECT COUNT(*) c FROM lead_assignments WHERE DATE(assigned_at)=? ");
    $assigned->execute([$today]); $assigned_today = (int)$assigned->fetchColumn();
    // Tipificados hoy (actividades creadas hoy)
    $typed = $dbh->prepare("SELECT COUNT(*) c FROM lead_activities WHERE DATE(created_at)=? ");
    $typed->execute([$today]); $typed_today = (int)$typed->fetchColumn();
    // Bases total hoy: leads creados hoy
    $bases_total = $dbh->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=?");
    $bases_total->execute([$today]); $bases_total_today = (int)$bases_total->fetchColumn();
    // Pendientes de asignar: leads creados hoy que no tienen asignación
    $pending_assign = $dbh->prepare("SELECT COUNT(*) FROM leads WHERE DATE(created_at)=? AND NOT EXISTS (SELECT 1 FROM lead_assignments la WHERE la.lead_id=leads.id)");
    $pending_assign->execute([$today]); $pending_assign_today = (int)$pending_assign->fetchColumn();
    view('backdata/summary', compact('assigned_today','typed_today','bases_total_today','pending_assign_today'));
  }

  public function leads(){ $this->requireBD();
    $db = db();
    $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
    $to = $_GET['to'] ?? date('Y-m-d');
    $status = trim($_GET['status'] ?? '');
    $assigned = $_GET['assigned'] ?? '';
    // Lista de estados disponibles
    $statuses = $db->query("SELECT DISTINCT status FROM leads ORDER BY status")->fetchAll(PDO::FETCH_COLUMN);
    // base filter
    $params = [$from.' 00:00:00', $to.' 23:59:59'];
    $where = 'l.created_at BETWEEN ? AND ?';
    if($status!==''){ $where .= ' AND l.status=?'; $params[] = $status; }
    if($assigned==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='t1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    if($assigned==='t0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    // Agrupar por día (compatible con ONLY_FULL_GROUP_BY)
    $sqlDays = "SELECT DATE(l.created_at) AS d,
                  COUNT(*) AS total,
                  SUM(CASE WHEN EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id) THEN 1 ELSE 0 END) AS assigned,
                  SUM(CASE WHEN EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id) THEN 1 ELSE 0 END) AS tipified
                FROM leads l
                WHERE $where
                GROUP BY DATE(l.created_at)
                ORDER BY d DESC";
    $stmt = $db->prepare($sqlDays); $stmt->execute($params); $days = $stmt->fetchAll();
    view('backdata/leads', ['days'=>$days,'from'=>$from,'to'=>$to,'status'=>$status,'assigned'=>$assigned,'statuses'=>$statuses]);
  }

  // (Legacy placeholder) Day preview route was referenced; implement simple JSON/HTML response of leads for a given day
  public function leadsDayPreview(){ $this->requireBD();
    // Params unify: accept 'date' (from view) and fallback to 'day'
    $day = $_GET['date'] ?? $_GET['day'] ?? date('Y-m-d');
    $status = trim($_GET['status'] ?? '');
    $assignedFilter = $_GET['assigned'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    $db = db();
    $where = 'DATE(l.created_at)=?';
    $params = [$day];
    if($status !== ''){ $where .= ' AND l.status=?'; $params[] = $status; }
    if($assignedFilter==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la2 WHERE la2.lead_id=l.id)'; }
    if($assignedFilter==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la2 WHERE la2.lead_id=l.id)'; }
    if($assignedFilter==='t1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_activities a2 WHERE a2.lead_id=l.id)'; }
    if($assignedFilter==='t0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_activities a2 WHERE a2.lead_id=l.id)'; }
    $sql = "SELECT l.id,l.full_name,l.phone,l.email,l.status,l.created_at,
        (SELECT la.assigned_at FROM lead_assignments la WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS assigned_at,
        (SELECT u.name FROM lead_assignments la JOIN users u ON u.id=la.seller_id WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_name
      FROM leads l WHERE $where ORDER BY l.id DESC LIMIT $limit";
    $stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
    if(isset($_GET['format']) && $_GET['format']==='json'){
      header('Content-Type: application/json'); echo json_encode(['day'=>$day,'rows'=>$rows]); return; }
  echo '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Estado</th><th>Asignado a</th><th>Creado</th><th>Asignado</th></tr></thead><tbody>';
    if(empty($rows)) echo '<tr><td colspan="7" class="text-center text-muted">Sin leads</td></tr>';
    foreach($rows as $r){
      $pill = age_status_pill($r['status'] ?? '', $r['created_at'] ?? null);
      echo '<tr>'
        .'<td>#'.(int)$r['id'].'</td>'
        .'<td>'.htmlspecialchars($r['full_name']??'').'</td>'
        .'<td>'.htmlspecialchars($r['phone']??'').'</td>'
        .'<td>'.$pill.'</td>'
        .'<td>'.htmlspecialchars($r['seller_name']??'-').'</td>'
        .'<td>'.htmlspecialchars($r['created_at']).'</td>'
        .'<td>'.htmlspecialchars($r['assigned_at']??'-').'</td>'
        .'</tr>';
    }
    echo '</tbody></table></div>';
  }

  public function bases(){ $this->requireBD();
    $db = db();
    $q = trim($_GET['q'] ?? '');
    $showArchived = isset($_GET['archived']) && $_GET['archived']=='1';
    $where = $showArchived ? 'b.archived_at IS NOT NULL' : 'b.archived_at IS NULL';
    $params = [];
  if($q!==''){ $where .= ' AND (b.name LIKE ? OR b.tags LIKE ?)'; $params[]='%'.$q.'%'; $params[]='%'.$q.'%'; }
  $sql = "SELECT b.id,b.name,b.tags,b.created_at,b.archived_at,u.name AS created_by_name,
      (SELECT COUNT(*) FROM leads l WHERE l.batch_id=b.id) AS total_leads,
      (SELECT COUNT(*) FROM lead_assignments la WHERE la.lead_id IN (SELECT id FROM leads WHERE batch_id=b.id)) AS assigned,
      (SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.lead_id IN (SELECT id FROM leads WHERE batch_id=b.id)) AS tipified
    FROM import_batches b
    JOIN users u ON u.id=b.created_by
    WHERE $where
    ORDER BY b.id DESC LIMIT 200";
  $stmt = $db->prepare($sql); $stmt->execute($params); $batches = $stmt->fetchAll();
  $noResults = (empty($batches) && $q !== '');
  view('backdata/bases', ['batches'=>$batches,'q'=>$q,'showArchived'=>$showArchived,'noResults'=>$noResults]);
  }

  // Nuevo módulo: Progreso de tipificación por base (similar a bases, pero centrado en avance y pendiente)
  public function basesProgress(){ $this->requireBD();
    $db = db();
  $q = trim($_GET['q'] ?? '');
  // Nuevo filtro de estado: all | active | archived (compatibilidad con checkbox anterior ?archived=1)
  $state = isset($_GET['state']) ? $_GET['state'] : (isset($_GET['archived']) && $_GET['archived']=='1' ? 'archived' : 'all');
    if(!in_array($state,['all','active','archived'])) $state='all';
    $where = '1=1';
    if($state==='active') $where .= ' AND b.archived_at IS NULL';
    if($state==='archived') $where .= ' AND b.archived_at IS NOT NULL';
    $params = [];
    if($q!==''){ $where .= ' AND (b.name LIKE ? OR b.tags LIKE ?)'; $params[]='%'.$q.'%'; $params[]='%'.$q.'%'; }
    $sql = "SELECT b.id,b.name,b.tags,b.created_at,b.archived_at,
              (SELECT COUNT(*) FROM leads l WHERE l.batch_id=b.id) AS total_leads,
              (SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.lead_id IN (SELECT id FROM leads WHERE batch_id=b.id)) AS tipified
            FROM import_batches b
            WHERE $where
            ORDER BY b.id DESC LIMIT 200";
    $stmt=$db->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
    foreach($rows as &$r){
      $r['pending'] = max(0, (int)$r['total_leads'] - (int)$r['tipified']);
      $r['progress_pct'] = $r['total_leads']>0 ? round(($r['tipified']/$r['total_leads'])*100,1) : 0;
      $r['status_label'] = $r['progress_pct']>=100 ? 'Completada' : ($r['archived_at']? 'Archivada' : 'En curso');
    }
    $noResults = (empty($rows) && $q!=='');
    view('backdata/bases_progress',[ 'rows'=>$rows,'q'=>$q,'state'=>$state,'noResults'=>$noResults ]);
  }

  public function baseDetail(){ $this->requireBD();
    $id = (int)($_GET['id'] ?? 0); if(!$id){ http_response_code(404); exit('Base no encontrada'); }
    $db = db();
    $base = $db->prepare('SELECT b.*, u.name created_by_name FROM import_batches b JOIN users u ON u.id=b.created_by WHERE b.id=?');
    $base->execute([$id]); $batch = $base->fetch(); if(!$batch){ http_response_code(404); exit('Base no encontrada'); }
    $q = trim($_GET['q'] ?? '');
    $assigned = $_GET['assigned'] ?? '';
  $typed = $_GET['typed'] ?? '';
  $statusFilter = trim($_GET['status'] ?? '');
    $where = 'l.batch_id=?'; $params = [$id];
  if($q!==''){ $where .= ' AND (l.phone LIKE ? OR l.full_name LIKE ? OR l.email LIKE ?)'; array_push($params,'%'.$q.'%','%'.$q.'%','%'.$q.'%'); }
    if($assigned==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
  if($typed==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
  if($typed==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
  if($statusFilter!==''){ $where .= ' AND (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1)=?'; $params[]=$statusFilter; }
    $sql = "SELECT l.*,
              (SELECT la.seller_id FROM lead_assignments la WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_id,
              (SELECT u.name FROM lead_assignments la JOIN users u ON u.id=la.seller_id WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_name,
              (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status,
              (SELECT a2.created_at FROM lead_activities a2 WHERE a2.lead_id=l.id ORDER BY a2.id DESC LIMIT 1) AS last_status_at,
              (SELECT u2.name FROM lead_activities a3 JOIN users u2 ON u2.id=a3.user_id WHERE a3.lead_id=l.id ORDER BY a3.id DESC LIMIT 1) AS last_status_by,
              (SELECT a4.note FROM lead_activities a4 WHERE a4.lead_id=l.id AND a4.note IS NOT NULL AND a4.note<>'' ORDER BY a4.id DESC LIMIT 1) AS last_note
            FROM leads l WHERE $where ORDER BY l.id DESC LIMIT 500";
  $stmt = $db->prepare($sql); $stmt->execute($params); $leads = $stmt->fetchAll();
  view('backdata/base_detail', ['batch'=>$batch,'leads'=>$leads,'q'=>$q,'assigned'=>$assigned,'typed'=>$typed,'statusFilter'=>$statusFilter]);
  }

  public function baseArchive(){ $this->requireBD(); csrf_check(); $id=(int)($_POST['id']??0); if(!$id){ header('Location: /backdata/bases'); return; }
    db()->prepare('UPDATE import_batches SET archived_at=NOW() WHERE id=? AND archived_at IS NULL')->execute([$id]);
    flash('success','Base archivada'); header('Location: /backdata/bases');
  }

  // Eliminar una base (y sus leads) con confirmación CSRF
  public function baseDelete(){ $this->requireBD(); csrf_check(); $id=(int)($_POST['id']??0); if(!$id){ header('Location: /backdata/bases'); return; }
    $db = db();
    $db->beginTransaction();
    try{
      // Borrar leads de la base (cascada eliminará asignaciones/actividades por FK)
      $del = $db->prepare('DELETE FROM leads WHERE batch_id=?');
      $del->execute([$id]);
      $deleted = $del->rowCount();
      // Borrar la base
      $db->prepare('DELETE FROM import_batches WHERE id=?')->execute([$id]);
      $db->commit();
      flash('success','Base eliminada. Leads borrados: '.$deleted);
      header('Location: /backdata/bases');
    }catch(\Throwable $e){
      $db->rollBack();
      flash('error','No se pudo eliminar: '.$e->getMessage());
      header('Location: /backdata/base?id='.$id);
    }
  }

  public function baseRename(){ $this->requireBD(); csrf_check(); $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $tags=trim($_POST['tags']??''); if(!$id||$name===''){ header('Location: /backdata/bases'); return; }
    db()->prepare('UPDATE import_batches SET name=?, tags=? WHERE id=?')->execute([$name,$tags,$id]);
    flash('success','Base actualizada'); header('Location: /backdata/bases');
  }

  /**
   * Muestra el modal de previsualización de una base con tipificación y status.
   * Valida y sanitiza parámetros, evita interpolación directa en SQL.
   */
  public function basePreview(){
    $this->requireBD();
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $limit = in_array($limit, array(20, 50, 100)) ? $limit : 20;
    if($id <= 0){ http_response_code(400); exit('Falta id'); }
    $db = db();
    // Métricas básicas de previsualización (usando consultas preparadas correctamente)
    $stmtTotal = $db->prepare("SELECT COUNT(*) FROM leads WHERE batch_id=?");
    $stmtTotal->execute([$id]);
    $total = (int)$stmtTotal->fetchColumn();
    $stmtAssigned = $db->prepare("SELECT COUNT(*) FROM lead_assignments WHERE lead_id IN (SELECT id FROM leads WHERE batch_id=?)");
    $stmtAssigned->execute([$id]);
    $assignedCount = (int)$stmtAssigned->fetchColumn();
    $stmtTipified = $db->prepare("SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.lead_id IN (SELECT id FROM leads WHERE batch_id=?)");
    $stmtTipified->execute([$id]);
    $tipifiedCount = (int)$stmtTipified->fetchColumn();
    $counts = [
      'total' => $total,
      'assigned' => $assignedCount,
      'tipified' => $tipifiedCount
    ];
    // Consulta principal usando parámetros seguros
    $sql = "SELECT l.id, l.full_name, l.phone, l.email, l.source_name, l.created_at,
      (SELECT la.seller_id FROM lead_assignments la WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_id,
      (SELECT u.name FROM lead_assignments la JOIN users u ON u.id=la.seller_id WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_name,
      (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status,
      (SELECT a2.created_at FROM lead_activities a2 WHERE a2.lead_id=l.id ORDER BY a2.id DESC LIMIT 1) AS last_status_at,
      (SELECT u2.name FROM lead_activities a3 JOIN users u2 ON u2.id=a3.user_id WHERE a3.lead_id=l.id ORDER BY a3.id DESC LIMIT 1) AS last_status_by,
      (SELECT a4.note FROM lead_activities a4 WHERE a4.lead_id=l.id AND a4.note IS NOT NULL AND a4.note<>'' ORDER BY a4.id DESC LIMIT 1) AS last_note
      FROM leads l WHERE l.batch_id=? ORDER BY l.id DESC LIMIT ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id, $limit]);
    $sample = $stmt->fetchAll();
    view('backdata/base_preview', ['leads'=>$sample,'counts'=>$counts,'limit'=>$limit,'id'=>$id]);
  }

  public function baseExport(){ $this->requireBD();
    $id = (int)($_GET['id'] ?? 0); if(!$id){ http_response_code(400); exit('Falta id'); }
    $db = db();
    $sql = "SELECT l.id,l.full_name,l.phone,l.email,l.source_name,l.created_at,
              (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status,
              (SELECT a.created_at FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status_at,
              (SELECT u.name FROM lead_activities a JOIN users u ON u.id=a.user_id WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status_by,
              (SELECT a.note FROM lead_activities a WHERE a.lead_id=l.id AND a.note IS NOT NULL AND a.note<>'' ORDER BY a.id DESC LIMIT 1) AS last_note
            FROM leads l WHERE l.batch_id=? ORDER BY l.id ASC";
    $stmt=$db->prepare($sql); $stmt->execute([$id]); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="base_'.$id.'_export.csv"');
    $out=fopen('php://output','w');
    fputcsv($out,['id','full_name','phone','email','source','created_at','last_status','last_status_at','last_status_by','last_note']);
    foreach($rows as $r){
      fputcsv($out,[
        $r['id'],$r['full_name'],$r['phone'],$r['email'],$r['source_name'],$r['created_at'],$r['last_status'],$r['last_status_at'],$r['last_status_by'],$r['last_note']
      ]);
    }
    fclose($out); exit;
  }

  public function assignForm(){ $this->requireBD();
    $db = db();
    $sellers = $db->query("SELECT id,name,username FROM users WHERE active=1 AND role_id=(SELECT id FROM roles WHERE name='seller' LIMIT 1) ORDER BY name")->fetchAll();
    $batches = $db->query("SELECT id,name FROM import_batches WHERE archived_at IS NULL ORDER BY id DESC LIMIT 200")->fetchAll();
    view('backdata/assign', compact('sellers','batches'));
  }

  public function assignPreview(){ $this->requireBD();
    $batchId = (int)($_GET['batch_id'] ?? 0); $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    if(!$batchId){ http_response_code(400); exit('Falta batch_id'); }
    $db = db();
    $rows = $db->query("SELECT l.id,l.full_name,l.phone,l.email,l.source_name,l.created_at FROM leads l LEFT JOIN lead_assignments la ON la.lead_id=l.id WHERE la.lead_id IS NULL AND l.batch_id=".$batchId." ORDER BY l.id DESC LIMIT $limit")->fetchAll();
    view('backdata/assign_preview', ['leads'=>$rows,'limit'=>$limit,'batch_id'=>$batchId]);
  }

  public function assignRun(){ $this->requireBD(); csrf_check();
    $seller_id = (int)($_POST['seller_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 0); if($qty<=0||$qty>200){ $qty = 200; }
    $batch_id = isset($_POST['batch_id']) && $_POST['batch_id']!=='' ? (int)$_POST['batch_id'] : null;
    if(!$seller_id){ flash('error','Selecciona vendedor'); header('Location: /backdata/assign'); return; }
    $db = db();
    $db->beginTransaction();
    try{
      // Pick leads not assigned yet (no row in lead_assignments), optionally filtered by Base or manual selection
      $limit = max(1, (int)$qty);
      $selected = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
      $useSelectedOnly = isset($_POST['use_selected_only']) && $_POST['use_selected_only']=='1';
      if(!empty($selected) && $useSelectedOnly){
        $in = implode(',', array_fill(0, count($selected), '?'));
        $q = $db->prepare("SELECT l.id FROM leads l LEFT JOIN lead_assignments la ON la.lead_id=l.id WHERE la.lead_id IS NULL AND l.id IN ($in) LIMIT $limit");
        $q->execute($selected);
        $ids = $q->fetchAll(PDO::FETCH_COLUMN);
      } elseif($batch_id){
        $sql = "SELECT l.id FROM leads l LEFT JOIN lead_assignments la ON la.lead_id=l.id WHERE la.lead_id IS NULL AND l.batch_id=? ORDER BY l.id ASC LIMIT $limit";
        $pick = $db->prepare($sql);
        $pick->execute([$batch_id]);
        $ids = $pick->fetchAll(PDO::FETCH_COLUMN);
      } else {
        $sql = "SELECT l.id FROM leads l LEFT JOIN lead_assignments la ON la.lead_id=l.id WHERE la.lead_id IS NULL ORDER BY l.id ASC LIMIT $limit";
        $pick = $db->query($sql);
        $ids = $pick->fetchAll(PDO::FETCH_COLUMN);
      }
      foreach($ids as $lid){
        $db->prepare("INSERT INTO lead_assignments(lead_id,seller_id) VALUES(?,?)")->execute([$lid,$seller_id]);
      }
      $op = $db->prepare("INSERT INTO assign_operations(operator_id,seller_id,qty) VALUES(?,?,?)");
      $op->execute([auth_user()['id'], $seller_id, count($ids)]);
      $db->commit();
      flash('success','Asignados: '.count($ids));
    }catch(\Throwable $e){ $db->rollBack(); flash('error','Error al asignar: '.$e->getMessage()); }
    header('Location: /backdata/assign');
  }


  // Sellers productivity overview
  public function sellers(){ $this->requireBD();
    try{
      $db = db();
      $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
      $to = $_GET['to'] ?? date('Y-m-d');
      $batchId = isset($_GET['batch_id']) && $_GET['batch_id']!=='' ? (int)$_GET['batch_id'] : null;
      // Sellers list with metrics
      $batchWhereAssign = '';
      $batchWhereBases = '';
      $batchWhereList = '';
      if($batchId){
        $batchWhereAssign = ' AND EXISTS(SELECT 1 FROM leads l2 WHERE l2.id=la.lead_id AND l2.batch_id='.((int)$batchId).')';
        $batchWhereBases = ' AND l3.batch_id='.((int)$batchId);
        $batchWhereList  = ' AND l2.batch_id='.((int)$batchId);
      }
      $q = trim($_GET['q'] ?? '');

      $sql = "SELECT u.id,u.name,u.username,
                (SELECT COUNT(*) FROM lead_assignments la WHERE la.seller_id=u.id AND la.assigned_at BETWEEN ? AND ? $batchWhereAssign) AS assigned_period,
                (SELECT COUNT(DISTINCT a.lead_id)
                   FROM lead_assignments laX
                   JOIN lead_activities a ON a.lead_id=laX.lead_id
                   WHERE laX.seller_id=u.id AND laX.assigned_at BETWEEN ? AND ? $batchWhereAssign) AS tipified_period,
                (SELECT COUNT(DISTINCT l3.batch_id) FROM lead_assignments la3 JOIN leads l3 ON l3.id=la3.lead_id WHERE la3.seller_id=u.id AND la3.assigned_at BETWEEN ? AND ? $batchWhereBases) AS bases_period,
                (SELECT COUNT(*) FROM lead_assignments laT WHERE laT.seller_id=u.id) AS assigned_total,
                (SELECT COUNT(DISTINCT aT.lead_id) FROM lead_activities aT WHERE aT.user_id=u.id) AS tipified_total,
                (SELECT COUNT(DISTINCT lAll.batch_id) FROM lead_assignments laAll JOIN leads lAll ON lAll.id=laAll.lead_id WHERE laAll.seller_id=u.id) AS bases_total,
                -- Inventario actual (última asignación vigente)
                (SELECT COUNT(*) FROM leads lC WHERE EXISTS(
                    SELECT 1 FROM lead_assignments laC
                    WHERE laC.lead_id=lC.id AND laC.id=(SELECT MAX(laC2.id) FROM lead_assignments laC2 WHERE laC2.lead_id=lC.id)
                      AND laC.seller_id=u.id
                )) AS current_assigned,
                (SELECT COUNT(DISTINCT aC.lead_id) FROM lead_activities aC
                   WHERE EXISTS(
                     SELECT 1 FROM lead_assignments laCL
                     WHERE laCL.lead_id=aC.lead_id AND laCL.id=(SELECT MAX(laCL2.id) FROM lead_assignments laCL2 WHERE laCL2.lead_id=aC.lead_id)
                       AND laCL.seller_id=u.id
                   )) AS current_tipified,
           (SELECT GROUP_CONCAT(DISTINCT b2.name ORDER BY b2.id SEPARATOR ', ')
             FROM lead_assignments la2
             JOIN leads l2 ON l2.id=la2.lead_id
             LEFT JOIN import_batches b2 ON b2.id=l2.batch_id
                   WHERE la2.seller_id=u.id AND la2.assigned_at BETWEEN ? AND ? $batchWhereList) AS base_names,
           (SELECT GROUP_CONCAT(DISTINCT b2.tags ORDER BY b2.id SEPARATOR ', ')
             FROM lead_assignments la2
             JOIN leads l2 ON l2.id=la2.lead_id
             LEFT JOIN import_batches b2 ON b2.id=l2.batch_id
                   WHERE la2.seller_id=u.id AND la2.assigned_at BETWEEN ? AND ? $batchWhereList) AS base_tags
          FROM users u
          WHERE u.active=1 AND u.role_id=(SELECT id FROM roles WHERE name='seller' LIMIT 1)";
      $params = [
        $from.' 00:00:00',$to.' 23:59:59', // assigned
        $from.' 00:00:00',$to.' 23:59:59', // tipified
        $from.' 00:00:00',$to.' 23:59:59', // bases
        $from.' 00:00:00',$to.' 23:59:59', // base_names
        $from.' 00:00:00',$to.' 23:59:59', // base_tags
      ];
      if($q !== ''){
        $sql .= " AND (u.name LIKE ? OR u.username LIKE ?)";
        $like = "%$q%"; $params[] = $like; $params[] = $like;
      }
      $sql .= " ORDER BY u.name";
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $sellers = $stmt->fetchAll();
      $batches = $db->query("SELECT id,name FROM import_batches ORDER BY id DESC LIMIT 200")->fetchAll();
      // Removed flash message (was repetitive). Pass count to view instead.
      // Normalizar estructura para vista (retrocompatibilidad de nombres)
      foreach($sellers as &$s){
        $s['assigned'] = (int)$s['assigned_period'];
        $s['tipified'] = (int)$s['tipified_period'];
        $s['bases'] = (int)$s['bases_period'];
        // Fallback a inventario actual si periodo es cero (evita mostrar 0% con totales >0)
        if($s['assigned']==0 && $s['current_assigned']>0){
          $s['assigned'] = (int)$s['current_assigned'];
          $s['tipified'] = (int)$s['current_tipified'];
          $s['__using_current'] = true;
        }
      }
      view('backdata/sellers', ['sellers'=>$sellers,'sellers_count'=>count($sellers),'from'=>$from,'to'=>$to,'batch_id'=>$batchId,'batches'=>$batches,'q'=>$q]);
    } catch(\Throwable $e){
      flash('error','Error: '.$e->getMessage());
      view('backdata/sellers', ['sellers'=>[],'sellers_count'=>0,'from'=>date('Y-m-d', strtotime('-7 days')),'to'=>date('Y-m-d'),'batch_id'=>null,'batches'=>[],'q'=>'']);
    }
  }

  public function sellerPreview(){ $this->requireBD();
    $db = db();
    $sid = (int)($_GET['seller_id'] ?? 0);
    $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
    $to = $_GET['to'] ?? date('Y-m-d');
    $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    $batchId = isset($_GET['batch_id']) && $_GET['batch_id']!=='' ? (int)$_GET['batch_id'] : null;
    $search = trim($_GET['search'] ?? '');
    if(!$sid){
      view('backdata/seller_preview', ['rows'=>[], 'limit'=>$limit, 'from'=>$from, 'to'=>$to, 'seller_id'=>$sid, 'batch_id'=>$batchId, 'search'=>$search, 'error'=>'Falta seller_id']);
      return;
    }
    $where = 'la.seller_id=? AND la.assigned_at BETWEEN ? AND ?';
    $params = [$sid, $from.' 00:00:00', $to.' 23:59:59'];
    if($batchId){ $where .= ' AND l.batch_id=?'; $params[] = $batchId; }
    if($search!==''){
      $where .= ' AND (l.full_name LIKE ? OR l.phone LIKE ? OR b.name LIKE ?)';
      $like = "%$search%"; $params[]=$like; $params[]=$like; $params[]=$like;
    }
    $sql = "SELECT l.id,l.full_name,l.phone,l.email,b.name AS base_name,b.tags AS base_tags,la.assigned_at,
              (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status,
              (SELECT u2.name FROM lead_activities a2 JOIN users u2 ON u2.id=a2.user_id WHERE a2.lead_id=l.id ORDER BY a2.id DESC LIMIT 1) AS last_by,
              (SELECT a3.created_at FROM lead_activities a3 WHERE a3.lead_id=l.id ORDER BY a3.id DESC LIMIT 1) AS last_at,
              (SELECT a4.note FROM lead_activities a4 WHERE a4.lead_id=l.id AND a4.note IS NOT NULL AND a4.note<>'' ORDER BY a4.id DESC LIMIT 1) AS last_note
            FROM lead_assignments la
            JOIN leads l ON l.id=la.lead_id
            LEFT JOIN import_batches b ON b.id=l.batch_id
            WHERE $where
            ORDER BY la.id DESC LIMIT $limit";
    $stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
    view('backdata/seller_preview', ['rows'=>$rows,'limit'=>$limit,'from'=>$from,'to'=>$to,'seller_id'=>$sid,'batch_id'=>$batchId,'search'=>$search]);
  }
}
