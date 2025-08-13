<?php
namespace App\Controllers;

use function view;
use function auth_user;

class HomeController {
    public function index() {
        $user = auth_user();
        if (!$user) {
            header('Location: /login');
            exit;
        }
        
    // Mostrar dashboard de noticias para todos los roles
    $db = db();
    $now = date('Y-m-d H:i:s');
    $audRoles = [$user['role_name'],'all'];
    $in = implode(',', array_fill(0,count($audRoles),'?'));
                try{
                    $stmt = $db->prepare("SELECT * FROM announcements WHERE audience IN ($in) AND starts_at <= ? AND (ends_at IS NULL OR ends_at >= ?) ORDER BY id DESC LIMIT 20");
                    $stmt->execute([...$audRoles,$now,$now]);
                    $ann = $stmt->fetchAll();
                }catch(\PDOException $e){
                    if(str_contains($e->getMessage(),'42S02')){ // tabla inexistente
                        $db->exec("CREATE TABLE IF NOT EXISTS announcements (id BIGINT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,body TEXT NOT NULL,audience VARCHAR(100) NOT NULL DEFAULT 'all',starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,ends_at DATETIME NULL,created_by INT NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,KEY idx_ann_active (starts_at, ends_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                        $ann = [];
                    } else { throw $e; }
                }
    view('home', ['user'=>$user,'announcements'=>$ann]);
    }
}