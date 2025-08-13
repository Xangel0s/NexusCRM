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