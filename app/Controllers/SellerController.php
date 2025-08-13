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

    public function myLeads() {
        $this->requireSeller();
                $u = auth_user();
                $db = db();
                // Obtener leads asignados al vendedor (última asignación)
                $stmt = $db->prepare("SELECT l.id,l.full_name,l.phone,l.email,l.status,
                        (SELECT la.assigned_at FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=? ORDER BY la.id DESC LIMIT 1) AS assigned_at,
                        (SELECT a.status FROM lead_activities a WHERE a.lead_id=l.id ORDER BY a.id DESC LIMIT 1) AS last_status
                    FROM leads l
                    WHERE EXISTS(SELECT 1 FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=?)
                    ORDER BY l.id DESC LIMIT 500");
                $stmt->execute([$u['id'],$u['id']]);
                $leads = $stmt->fetchAll();
                view('seller/my_leads', ['leads'=>$leads]);
    }

    public function leadDetail() {
        $this->requireSeller();
                $id = (int)($_GET['id'] ?? 0);
                if(!$id){ http_response_code(400); exit('Falta id'); }
                $u = auth_user();
                $db = db();
                // Verificar que el lead esté asignado a este vendedor
                $check = $db->prepare("SELECT l.id,l.full_name,l.phone,l.email,l.status,
                         (SELECT la.assigned_at FROM lead_assignments la WHERE la.lead_id=l.id AND la.seller_id=? ORDER BY la.id DESC LIMIT 1) AS assigned_at
                     FROM leads l WHERE l.id=? AND EXISTS(SELECT 1 FROM lead_assignments la2 WHERE la2.lead_id=l.id AND la2.seller_id=?)");
                $check->execute([$u['id'],$id,$u['id']]);
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
    // Verificar asignación
    $own = $db->prepare('SELECT 1 FROM lead_assignments WHERE lead_id=? AND seller_id=? ORDER BY id DESC LIMIT 1');
    $own->execute([$lead_id,$u['id']]);
    if(!$own->fetchColumn()){ flash('error','Lead no asignado a ti'); header('Location: /seller/my-leads'); return; }
    // Insertar actividad
    $ins = $db->prepare('INSERT INTO lead_activities(lead_id,user_id,status,note) VALUES(?,?,?,?)');
    $ins->execute([$lead_id,$u['id'],$status,$note!==''?$note:null]);
    flash('success','Actividad registrada');
    header('Location: /seller/lead?id='.$lead_id);
    }
}