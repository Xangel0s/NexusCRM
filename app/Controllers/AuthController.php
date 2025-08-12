<?php
namespace App\Controllers;
use PDO; use function view; use function csrf_check; use function csrf_token; use function db; use function flash;
class AuthController{
  public function loginForm(){ view('auth/login'); }
  public function login(){ csrf_check(); $u=trim($_POST['username']??''); $p=trim((string)($_POST['password']??'')); if($u===''){ flash('error','Usuario requerido'); header('Location: /login'); return; }
    $stmt=db()->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id WHERE u.username=? AND u.active=1 LIMIT 1');
    $stmt->execute([$u]); $row=$stmt->fetch();
    if(!$row){
      // Always show specific debug to identify the cause during setup
      flash('error','Usuario no encontrado o inactivo');
      header('Location: /login'); return;
    }
    $hash = (string)$row['password_hash'];
    $ok = false;
    // Modo desarrollo: aceptar bcrypt o texto plano
    if ($hash !== '') {
      if (str_starts_with($hash, '$2')) {
        $ok = password_verify($p, $hash) || hash_equals($hash, $p);
      } else {
        $ok = hash_equals($hash, $p);
      }
    }
    if(!$ok){ flash('error','Usuario o contraseña incorrectos'); header('Location: /login'); return; }
    $_SESSION['user']=['id'=>$row['id'],'name'=>$row['name'],'username'=>$row['username'],'role_id'=>$row['role_id'],'role_name'=>$row['role_name']];
    session_regenerate_id(true); header('Location: /');
  }
  public function logout(){ csrf_check(); $_SESSION=[]; session_destroy(); header('Location: /login'); }
}
