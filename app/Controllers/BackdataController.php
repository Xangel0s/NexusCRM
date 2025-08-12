<?php
namespace App\Controllers;
use PDO; use function db; use function view; use function auth_user; use function csrf_check; use function csrf_token; use function flash;

class BackdataController{
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
    // Pendientes hoy: leads sin actividad hoy
    $pending = $dbh->prepare("SELECT COUNT(*) FROM leads l WHERE DATE(l.created_at)=? AND NOT EXISTS (SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id AND DATE(a.created_at)=?)");
    $pending->execute([$today,$today]); $pending_today = (int)$pending->fetchColumn();
    view('backdata/summary', compact('assigned_today','typed_today','pending_today'));
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

  public function bases(){ $this->requireBD();
    $db = db();
    $q = trim($_GET['q'] ?? '');
    $showArchived = isset($_GET['archived']) && $_GET['archived']=='1';
    $where = $showArchived ? 'b.archived_at IS NOT NULL' : 'b.archived_at IS NULL';
    $params = [];
    if($q!==''){ $where .= ' AND (b.name LIKE ? OR b.tags LIKE ?)'; $params[]='%'.$q.'%'; $params[]='%'.$q+'%'; }
    $sql = "SELECT b.id,b.name,b.tags,b.created_at,b.archived_at,u.name AS created_by_name,
              (SELECT COUNT(*) FROM leads l WHERE l.batch_id=b.id) AS total_leads,
              (SELECT COUNT(*) FROM lead_assignments la WHERE la.lead_id IN (SELECT id FROM leads WHERE batch_id=b.id)) AS assigned,
              (SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.lead_id IN (SELECT id FROM leads WHERE batch_id=b.id)) AS tipified
            FROM import_batches b
            JOIN users u ON u.id=b.created_by
            WHERE $where
            ORDER BY b.id DESC LIMIT 200";
    $stmt = $db->prepare($sql); $stmt->execute($params); $batches = $stmt->fetchAll();
    view('backdata/bases', compact('batches','q','showArchived'));
  }

  public function baseDetail(){ $this->requireBD();
    $id = (int)($_GET['id'] ?? 0); if(!$id){ http_response_code(404); exit('Base no encontrada'); }
    $db = db();
    $base = $db->prepare('SELECT b.*, u.name created_by_name FROM import_batches b JOIN users u ON u.id=b.created_by WHERE b.id=?');
    $base->execute([$id]); $batch = $base->fetch(); if(!$batch){ http_response_code(404); exit('Base no encontrada'); }
    $q = trim($_GET['q'] ?? '');
    $assigned = $_GET['assigned'] ?? '';
    $where = 'l.batch_id=?'; $params = [$id];
    if($q!==''){ $where .= ' AND (l.phone LIKE ? OR l.full_name LIKE ? OR l.email LIKE ?)'; array_push($params,'%'.$q.'%','%'.$q+'%','%'.$q+'%'); }
    if($assigned==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    $sql = "SELECT l.*,
              (SELECT la.seller_id FROM lead_assignments la WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_id,
              (SELECT u.name FROM lead_assignments la JOIN users u ON u.id=la.seller_id WHERE la.lead_id=l.id ORDER BY la.id DESC LIMIT 1) AS seller_name
            FROM leads l WHERE $where ORDER BY l.id DESC LIMIT 500";
    $stmt = $db->prepare($sql); $stmt->execute($params); $leads = $stmt->fetchAll();
    view('backdata/base_detail', ['batch'=>$batch,'leads'=>$leads,'q'=>$q,'assigned'=>$assigned]);
  }

  public function baseArchive(){ $this->requireBD(); csrf_check(); $id=(int)($_POST['id']??0); if(!$id){ header('Location: /backdata/bases'); return; }
    db()->prepare('UPDATE import_batches SET archived_at=NOW() WHERE id=? AND archived_at IS NULL')->execute([$id]);
    flash('success','Base archivada'); header('Location: /backdata/bases');
  }

  public function baseRename(){ $this->requireBD(); csrf_check(); $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $tags=trim($_POST['tags']??''); if(!$id||$name===''){ header('Location: /backdata/bases'); return; }
    db()->prepare('UPDATE import_batches SET name=?, tags=? WHERE id=?')->execute([$name,$tags,$id]);
    flash('success','Base actualizada'); header('Location: /backdata/bases');
  }

  public function basePreview(){ $this->requireBD();
    $id = (int)($_GET['id'] ?? 0); $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    if(!$id){ http_response_code(400); exit('Falta id'); }
    $db = db();
    // métricas básicas de previsualización
    $counts = [
      'total' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE batch_id=".(int)$id)->fetchColumn(),
      'assigned' => (int)$db->query("SELECT COUNT(*) FROM lead_assignments WHERE lead_id IN (SELECT id FROM leads WHERE batch_id=".(int)$id.")")->fetchColumn(),
      'tipified' => (int)$db->query("SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.lead_id IN (SELECT id FROM leads WHERE batch_id=".(int)$id.")")->fetchColumn()
    ];
    $sample = $db->query("SELECT id,full_name,phone,email,source_name,created_at FROM leads WHERE batch_id=".(int)$id." ORDER BY id DESC LIMIT $limit")->fetchAll();
    view('backdata/base_preview', ['leads'=>$sample,'counts'=>$counts,'limit'=>$limit,'id'=>$id]);
  }

  public function baseExport(){ $this->requireBD();
    $id = (int)($_GET['id'] ?? 0); if(!$id){ http_response_code(400); exit('Falta id'); }
    $db = db();
    $rows = $db->query("SELECT id, full_name, phone, email, source_name, created_at FROM leads WHERE batch_id=".(int)$id." ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="base_'.$id.'.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','full_name','phone','email','source','created_at']);
    foreach($rows as $r){ fputcsv($out, [$r['id'],$r['full_name'],$r['phone'],$r['email'],$r['source_name'],$r['created_at']]); }
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

  public function leadsDayPreview(){ $this->requireBD();
    $db = db();
    $date = $_GET['date'] ?? date('Y-m-d');
    $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    $status = trim($_GET['status'] ?? '');
    $assigned = $_GET['assigned'] ?? '';
    $where = 'DATE(l.created_at)=?'; $params = [$date];
    if($status!==''){ $where .= ' AND l.status=?'; $params[]=$status; }
    if($assigned==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='t1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    if($assigned==='t0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    $sql = "SELECT l.id,l.full_name,l.phone,l.email,l.source_name,l.status,l.created_at FROM leads l WHERE $where ORDER BY l.id DESC LIMIT $limit";
    $stmt = $db->prepare($sql); $stmt->execute($params); $leads = $stmt->fetchAll();
    view('backdata/leads_day_preview', ['leads'=>$leads,'limit'=>$limit,'date'=>$date]);
  }

  public function leadsExport(){ $this->requireBD();
    $db = db();
    $date = $_GET['date'] ?? date('Y-m-d');
    $status = trim($_GET['status'] ?? '');
    $assigned = $_GET['assigned'] ?? '';
    $where = 'DATE(l.created_at)=?'; $params = [$date];
    if($status!==''){ $where .= ' AND l.status=?'; $params[]=$status; }
    if($assigned==='1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id)'; }
    if($assigned==='t1'){ $where .= ' AND EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    if($assigned==='t0'){ $where .= ' AND NOT EXISTS(SELECT 1 FROM lead_activities a WHERE a.lead_id=l.id)'; }
    $sql = "SELECT l.id,l.full_name,l.phone,l.email,l.source_name,l.status,l.created_at FROM leads l WHERE $where ORDER BY l.id DESC";
    $stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads_'.date('Y-m-d', strtotime($date)).'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['id','full_name','phone','email','source','status','created_at']);
    foreach($rows as $r){ fputcsv($out, [$r['id'],$r['full_name'],$r['phone'],$r['email'],$r['source_name'],$r['status'],$r['created_at']]); }
    fclose($out); exit;
  }

  // Sellers productivity overview
  public function sellers(){ $this->requireBD();
    $db = db();
    $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
    $to = $_GET['to'] ?? date('Y-m-d');
    $batchId = isset($_GET['batch_id']) && $_GET['batch_id']!=='' ? (int)$_GET['batch_id'] : null;
    // Sellers list with metrics
    $params = [$from.' 00:00:00', $to.' 23:59:59', $from.' 00:00:00', $to.' 23:59:59'];
    $batchWhereAssign = '';
    $batchWhereBases = '';
    if($batchId){ $batchWhereAssign = ' AND EXISTS(SELECT 1 FROM leads l2 WHERE l2.id=la.lead_id AND l2.batch_id='.((int)$batchId).')';
                  $batchWhereBases = ' AND l3.batch_id='.((int)$batchId); }
    $sql = "SELECT u.id,u.name,u.username,
              (SELECT COUNT(*) FROM lead_assignments la WHERE la.seller_id=u.id AND la.assigned_at BETWEEN ? AND ? $batchWhereAssign) AS assigned,
              (SELECT COUNT(DISTINCT a.lead_id) FROM lead_activities a WHERE a.user_id=u.id AND a.created_at BETWEEN ? AND ?) AS tipified,
              (SELECT COUNT(DISTINCT l3.batch_id) FROM lead_assignments la3 JOIN leads l3 ON l3.id=la3.lead_id WHERE la3.seller_id=u.id AND la3.assigned_at BETWEEN ? AND ? $batchWhereBases) AS bases
            FROM users u
            WHERE u.active=1 AND u.role_id=(SELECT id FROM roles WHERE name='seller' LIMIT 1)
            ORDER BY u.name";
    $stmt = $db->prepare($sql);
    $stmt->execute([$from.' 00:00:00',$to.' 23:59:59',$from.' 00:00:00',$to.' 23:59:59',$from.' 00:00:00',$to.' 23:59:59']);
    $sellers = $stmt->fetchAll();
    $batches = $db->query("SELECT id,name FROM import_batches ORDER BY id DESC LIMIT 200")->fetchAll();
    view('backdata/sellers', ['sellers'=>$sellers,'from'=>$from,'to'=>$to,'batch_id'=>$batchId,'batches'=>$batches]);
  }

  public function sellerPreview(){ $this->requireBD();
    $db = db();
    $sid = (int)($_GET['seller_id'] ?? 0); if(!$sid){ http_response_code(400); exit('Falta seller_id'); }
    $from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
    $to = $_GET['to'] ?? date('Y-m-d');
    $limit = (int)($_GET['limit'] ?? 20); $limit = in_array($limit,[20,50,100])?$limit:20;
    $status = trim($_GET['status'] ?? '');
    $batchId = isset($_GET['batch_id']) && $_GET['batch_id']!=='' ? (int)$_GET['batch_id'] : null;
    $where = 'la.seller_id=? AND la.assigned_at BETWEEN ? AND ?';
    $params = [$sid, $from.' 00:00:00', $to.' 23:59:59'];
    if($batchId){ $where .= ' AND l.batch_id=?'; $params[] = $batchId; }
    $sql = "SELECT l.id,l.full_name,l.phone,l.email, l.source_name, b.name AS base_name, la.assigned_at,
              (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status,
              (SELECT u2.name FROM lead_activities a2 JOIN users u2 ON u2.id=a2.user_id WHERE a2.lead_id=l.id ORDER BY a2.id DESC LIMIT 1) AS last_by,
              (SELECT a3.created_at FROM lead_activities a3 WHERE a3.lead_id=l.id ORDER BY a3.id DESC LIMIT 1) AS last_at
            FROM lead_assignments la
            JOIN leads l ON l.id=la.lead_id
            LEFT JOIN import_batches b ON b.id=l.batch_id
            WHERE $where
            ORDER BY la.id DESC LIMIT $limit";
    $stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
    if($status!==''){ $rows = array_values(array_filter($rows, fn($r)=> (string)($r['last_status']??'') === $status)); }
    // Available statuses for filter (top 10)
    $sts = $db->query("SELECT DISTINCT status FROM lead_activities ORDER BY status LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
    view('backdata/seller_preview', ['rows'=>$rows,'limit'=>$limit,'from'=>$from,'to'=>$to,'seller_id'=>$sid,'status'=>$status,'statuses'=>$sts,'batch_id'=>$batchId]);
  }
}
