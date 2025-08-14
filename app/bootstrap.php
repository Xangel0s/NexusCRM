<?php
// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zona horaria
date_default_timezone_set('America/Mexico_City'); // Ajusta a tu zona horaria

// Iniciar sesión
session_start();

// Conexión a la base de datos
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
    $db   = 'nexus';
        $user = 'root';
        $pass = ''; // Ajusta según tu configuración de Laragon
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    return $pdo;
}

// Funciones de autenticación
function auth_user() {
    return $_SESSION['user'] ?? null;
}

// Funciones para vistas
function view($name, $data = []) {
    extract($data);
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    if ($isAjax) {
        // Renderizar solo la vista parcial
        include __DIR__ . "/Views/{$name}.php";
    } else {
        require __DIR__ . "/Views/{$name}.php";
    }
}

// Funciones para CSRF
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        exit('CSRF token validation failed');
    }
}

// Funciones para mensajes flash
function flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// UI helper: render status as colored pill
function status_pill(?string $status): string {
    $status = trim((string)$status);
    if($status==='') return '<span class="badge rounded-pill text-bg-secondary">-</span>';
    $key = mb_strtolower($status);
    // Normalize label mapping
    $mapLabel = [
        'cerrado' => 'Venta cerrada',
        'venta cerrada' => 'Venta cerrada',
        'contactado' => 'Contactado',
        'interesado' => 'Interesado',
        'no responde' => 'No responde',
        'new' => 'Nuevo',
        'duplicado' => 'Duplicado',
        'duplicate' => 'Duplicado',
    ];
    $label = $mapLabel[$key] ?? ucfirst($status);
    // Color mapping
    $mapClass = [
        'contactado' => 'text-bg-warning', // amarillo
        // interesado: un tono intermedio hacia verde
        'interesado' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
        'no responde' => 'text-bg-danger', // rojo
        'venta cerrada' => 'text-bg-success', // verde
        'cerrado' => 'text-bg-success',
        'new' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
        'duplicado' => 'text-bg-danger',
        'duplicate' => 'text-bg-danger',
    ];
    $class = $mapClass[$key] ?? 'text-bg-secondary';
    return '<span class="badge rounded-pill '.$class.'">'.htmlspecialchars($label).'</span>';
}

// Píldora por antigüedad: para status "new" cambia color según días desde creación
function age_status_pill(?string $status, ?string $createdAt): string {
    $status = trim((string)($status ?? ''));
    $key = mb_strtolower($status);
    if($key !== 'new'){
        return status_pill($status);
    }
    if(!$createdAt){
        return status_pill('new');
    }
    $ts = strtotime($createdAt);
    if($ts===false){
        return status_pill('new');
    }
    $days = floor((time() - $ts)/86400);
    if($days <= 14){
        // Verde clarito
        return '<span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">Nuevo</span>';
    }
    if($days <= 30){
        // Amarillo (reciente)
        return '<span class="badge rounded-pill text-bg-warning">Reciente</span>';
    }
    // Rojo (antiguo)
    return '<span class="badge rounded-pill text-bg-danger">Antiguo</span>';
}

// Etiqueta de rol legible en español a partir del slug interno
function role_label(string $role): string {
    switch($role) {
        case 'admin': return 'Administrador';
        case 'backdata_manager': return 'Backdata Manager';
        case 'backdata': return 'Backdata';
        case 'seller': return 'Vendedor';
        default:
            return ucfirst(str_replace('_',' ', $role));
    }
}

// Píldora para estado de Base según avance y estado
function base_status_pill(string $label, $progressPct, bool $archived): string{
    // Asegurar número
    $progress = is_numeric($progressPct) ? (float)$progressPct : 0.0;
    // Prioridad: Archivada > Completada > En curso
    if($archived){
        return '<span class="badge rounded-pill text-bg-secondary">Archivada</span>';
    }
    if($progress >= 100 || mb_strtolower($label)==='completada'){
        return '<span class="badge rounded-pill text-bg-success">Completada</span>';
    }
    // En curso: color por faltante
    $remaining = max(0, 100 - $progress);
    if($remaining <= 25){ // casi
        $class = 'text-bg-warning'; $txt = 'Casi lista';
    }elseif($remaining <= 60){
        $class = 'bg-success-subtle text-success-emphasis border border-success-subtle'; $txt = 'En curso';
    }else{
        $class = 'text-bg-danger'; $txt = 'Recién empieza';
    }
    return '<span class="badge rounded-pill '.$class.'">'.$txt.'</span>';
}