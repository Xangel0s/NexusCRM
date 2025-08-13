<?php
namespace App\Controllers;

use function db;
use function view;
use function auth_user;
use function csrf_check;

class SellerController {
    private function requireSeller() {
        $user = auth_user();
        if (!$user || $user['role_name'] !== 'seller') {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    private function ensureReleaseTable($db): void {
        // Crea la tabla de liberaciones si aún no existe (idempotente)
        $db->exec("CREATE TABLE IF NOT EXISTS lead_releases (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            lead_id BIGINT NOT NULL,
            seller_id INT NOT NULL,
            released_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_release (lead_id,seller_id),
            KEY idx_seller (seller_id,released_at),
            FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
            FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function myLeads() {
        $this->requireSeller();
                $u = auth_user();
                $db = db();
            // Asegurar tabla para poder filtrar (evita error si nunca se ha liberado un lead)
            $this->ensureReleaseTable($db);
                $status = trim($_GET['status'] ?? '');
                // Obtener lista de estados existentes (tipificaciones) para filtro
                $statusesStmt = $db->query("SELECT DISTINCT status FROM lead_activities ORDER BY status");
                $allStatuses = $statusesStmt->fetchAll(\PDO::FETCH_COLUMN);
                $where = "EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=?)";
                // Excluir liberados (release) por este vendedor
                $where .= " AND NOT EXISTS(SELECT 1 FROM lead_releases lr WHERE lr.lead_id=l.id AND lr.seller_id=? )";
                // Orden de parámetros debe coincidir con los placeholders en el SELECT y luego en el WHERE
                // 1) subselect assigned_at (seller_id=?), 2) WHERE (seller_id=?), 3) WHERE (seller_id=?)
                $params = [$u['id'],$u['id'],$u['id']];
                if($status !== ''){
                        $where .= " AND (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1)=?";
                        $params[] = $status;
                }
                $sql = "SELECT l.id,l.full_name,l.phone,l.email,l.status,
                                    (SELECT la.assigned_at FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=? ORDER BY la.id DESC LIMIT 1) AS assigned_at,
                                    (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status
                                FROM leads l
                                WHERE $where
                                ORDER BY l.id DESC LIMIT 500";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $leads = $stmt->fetchAll();
                view('seller/my_leads', ['leads'=>$leads,'status_filter'=>$status,'statuses'=>$allStatuses]);
    }

    public function leadDetail() {
        $this->requireSeller();
                $id = (int)($_GET['id'] ?? 0);
                if(!$id){ http_response_code(400); exit('Falta id'); }
                $u = auth_user();
                $db = db();
             $this->ensureReleaseTable($db);
                // Verificar que el lead esté asignado a este vendedor
                $check = $db->prepare("SELECT l.id,l.full_name,l.phone,l.email,l.status,
                         (SELECT la.assigned_at FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=? ORDER BY la.id DESC LIMIT 1) AS assigned_at
                 FROM leads l WHERE l.id=? AND EXISTS(SELECT 1 FROM lead_assignments la2 WHERE la2.lead_id=l.id AND la2.seller_id=?) AND NOT EXISTS(SELECT 1 FROM lead_releases lr WHERE lr.lead_id=l.id AND lr.seller_id=?)");
             $check->execute([$u['id'],$id,$u['id'],$u['id']]);
                $lead = $check->fetch();
                if(!$lead){ http_response_code(404); exit('Lead no asignado a ti'); }
                // Actividades (histórico más reciente primero)
                $acts = $db->prepare("SELECT a.id,a.status,a.note,a.created_at,u.name user_name FROM lead_activities a JOIN users u ON u.id=a.user_id WHERE a.lead_id=? ORDER BY a.id DESC LIMIT 200");
                $acts->execute([$id]);
                $activities = $acts->fetchAll();
                view('seller/lead_detail', ['lead'=>$lead,'activities'=>$activities]);
    }

    public function tipify() {
        $this->requireSeller();
        csrf_check();
    $lead_id = (int)($_POST['lead_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $note = trim($_POST['note'] ?? '');
    if(!$lead_id || $status===''){ flash('error','Faltan datos'); header('Location: /seller/lead?id='.$lead_id); return; }
    $u = auth_user();
    $db = db();
    $this->ensureReleaseTable($db);
    // Verificar asignación
    $own = $db->prepare('SELECT 1 FROM lead_assignments WHERE lead_id=? AND seller_id=? ORDER BY id DESC LIMIT 1');
    $own->execute([$lead_id,$u['id']]);
    if(!$own->fetchColumn()){ flash('error','Lead no asignado a ti'); header('Location: /seller/my-leads'); return; }
    // Verificar que no haya sido liberado previamente
    $rel = $db->prepare('SELECT 1 FROM lead_releases WHERE lead_id=? AND seller_id=?');
    $rel->execute([$lead_id,$u['id']]);
    if($rel->fetchColumn()){ flash('error','Ya liberaste este lead'); header('Location: /seller/my-leads'); return; }
    // Insertar actividad
    $ins = $db->prepare('INSERT INTO lead_activities(lead_id,user_id,status,note) VALUES(?,?,?,?)');
    $ins->execute([$lead_id,$u['id'],$status,$note!==''?$note:null]);
    flash('success','Actividad registrada');
    header('Location: /seller/lead?id='.$lead_id);
    }

    public function release(){
        $this->requireSeller();
        csrf_check();
        $lead_id = (int)($_POST['lead_id'] ?? 0);
        if(!$lead_id){ flash('error','Falta lead'); header('Location: /seller/my-leads'); return; }
        $u = auth_user();
        $db = db();
    // Crear tabla si no existe (primera vez que se usa la funcionalidad)
    $this->ensureReleaseTable($db);
        // Verificar asignación actual
        $own = $db->prepare('SELECT 1 FROM lead_assignments WHERE lead_id=? AND seller_id=? ORDER BY id DESC LIMIT 1');
        $own->execute([$lead_id,$u['id']]);
        if(!$own->fetchColumn()){ flash('error','No tienes este lead'); header('Location: /seller/my-leads'); return; }
        // Verificar si ya lo liberó
        $rel = $db->prepare('SELECT 1 FROM lead_releases WHERE lead_id=? AND seller_id=?');
        $rel->execute([$lead_id,$u['id']]);
        if($rel->fetchColumn()){ flash('info','Ya estaba liberado'); header('Location: /seller/my-leads'); return; }
        // Insertar liberación
        $ins = $db->prepare('INSERT INTO lead_releases(lead_id,seller_id) VALUES(?,?)');
        $ins->execute([$lead_id,$u['id']]);
        flash('success','Lead liberado');
        header('Location: /seller/my-leads');
    }
}