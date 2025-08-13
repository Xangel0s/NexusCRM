<?php
namespace App\Controllers;
use PDO; use function db; use function view; use function auth_user; use function csrf_check; use function csrf_token; use function flash;

class AnnouncementController {
  private function requireAdmin(){ $u=auth_user(); if(!$u||!in_array($u['role_name'],['admin','backdata_manager'])){ http_response_code(403); exit('Forbidden'); } }

  public function index(){ $this->requireAdmin();
    $db=db();
    try{
      $rows=$db->query("SELECT a.*,u.name creator FROM announcements a JOIN users u ON u.id=a.created_by ORDER BY a.id DESC LIMIT 200")->fetchAll();
    }catch(\PDOException $e){
      // Si la tabla no existe, crearla rápidamente y continuar (primera vez)
      if(str_contains($e->getMessage(),'42S02')){
        $db->exec("CREATE TABLE IF NOT EXISTS announcements (id BIGINT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,body TEXT NOT NULL,audience VARCHAR(100) NOT NULL DEFAULT 'all',starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,ends_at DATETIME NULL,created_by INT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,KEY idx_ann_active (starts_at, ends_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $rows=[];
      } else { throw $e; }
    }
    view('announcements/index',['rows'=>$rows]); }
  public function create(){ $this->requireAdmin(); view('announcements/form',[ 'a'=>null ]); }
  public function store(){ $this->requireAdmin(); csrf_check();
    $title=trim($_POST['title']??''); $body=trim($_POST['body']??''); $audience=trim($_POST['audience']??'all');
    $starts=$_POST['starts_at']??date('Y-m-d H:i:s'); $ends=$_POST['ends_at']??null; if($ends==='') $ends=null;
    if($title===''||$body===''){ flash('error','Faltan campos'); header('Location: /announcements/create'); return; }
    $db=db();
    // Asegurar columna image_path
    try{ $db->query("SELECT image_path FROM announcements LIMIT 1"); }
    catch(\PDOException $e){ if(str_contains($e->getMessage(),'Unknown column')){ $db->exec("ALTER TABLE announcements ADD COLUMN image_path VARCHAR(255) NULL AFTER body"); } }
    $imagePath=null;
    // 1) Archivo subido tiene prioridad
    if(!empty($_FILES['image']['tmp_name'])){
      $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
      if(!in_array($ext,['jpg','jpeg','png','gif','webp'])){ flash('error','Formato de imagen no permitido'); header('Location: /announcements/create'); return; }
      $dir = dirname(__DIR__,2).'/public/uploads/ann'; if(!is_dir($dir)) @mkdir($dir,0777,true);
      $fname = 'ann_'.time().'_'.mt_rand(1000,9999).'.'.$ext;
      if(move_uploaded_file($_FILES['image']['tmp_name'],$dir.'/'.$fname)){
        // Detect si el DOCUMENT_ROOT ya es /public. Si no, guardamos con prefijo /public para que sea accesible.
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '','/');
        if($docRoot && str_ends_with(str_replace('\\','/',$docRoot),'/public')){
          $imagePath = '/uploads/ann/'.$fname; // docroot = public
        } else {
          $imagePath = '/public/uploads/ann/'.$fname; // docroot = raíz del proyecto => incluir public
        }
      }
    } else {
      // 2) URL externa si no hay archivo
      $imageUrl = trim($_POST['image_url'] ?? '');
      if($imageUrl !== ''){
        // Validar URL básica
        if(filter_var($imageUrl, FILTER_VALIDATE_URL) && preg_match('#^https?://#i',$imageUrl)){
          // Sanitizar longitud
          if(strlen($imageUrl) > 255){ $imageUrl = substr($imageUrl,0,255); }
          $imagePath = $imageUrl;
        } else {
          flash('error','URL de imagen inválida'); header('Location: /announcements/create'); return; }
      }
    }
    $stmt=$db->prepare('INSERT INTO announcements(title,body,audience,image_path,starts_at,ends_at,created_by) VALUES(?,?,?,?,?,?,?)');
    $stmt->execute([$title,$body,$audience,$imagePath,$starts,$ends,auth_user()['id']]);
    flash('success','Anuncio creado'); header('Location: /announcements');
  }
  public function delete(){ $this->requireAdmin(); csrf_check(); $id=(int)($_POST['id']??0); if(!$id){ header('Location: /announcements'); return; }
    db()->prepare('DELETE FROM announcements WHERE id=?')->execute([$id]); flash('success','Eliminado'); header('Location: /announcements'); }
}
