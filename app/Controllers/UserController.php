<?php
namespace App\Controllers;

use function db;
use function view;
use function auth_user;
use function csrf_check;
use function flash;

class UserController {
    private function requireAdmin() {
        $user = auth_user();
        if (!$user || $user['role_name'] !== 'admin') {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public function index() {
        $this->requireAdmin();
        $users = db()->query("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.name");
        view('users/index', ['users' => $users->fetchAll()]);
    }

    public function create() {
        $this->requireAdmin();
        view('users/create');
    }

    public function store() {
        $this->requireAdmin();
        csrf_check();
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role_id = (int)($_POST['role_id'] ?? 0);
        $password = trim($_POST['password'] ?? '');
        $active = 1;
        if ($name && $username && $role_id && $password) {
            $stmt = db()->prepare('INSERT INTO users (name, username, email, role_id, password, active) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $username, $email, $role_id, $password, $active]);
            flash('success', 'Usuario creado correctamente');
        } else {
            flash('error', 'Datos incompletos');
        }
        header('Location: /users');
    }

    public function edit() {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            flash('error', 'Usuario no encontrado');
            header('Location: /users');
            return;
        }
        view('users/edit', ['user' => $user]);
    }

    public function update() {
        $this->requireAdmin();
        csrf_check();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role_id = (int)($_POST['role_id'] ?? 0);
        $active = (int)($_POST['active'] ?? 1);
        $password = trim($_POST['password'] ?? '');
        if ($id && $name && $username && $role_id) {
            if ($password !== '') {
                $stmt = db()->prepare('UPDATE users SET name=?, username=?, email=?, role_id=?, active=?, password=? WHERE id=?');
                $stmt->execute([$name, $username, $email, $role_id, $active, $password, $id]);
            } else {
                $stmt = db()->prepare('UPDATE users SET name=?, username=?, email=?, role_id=?, active=? WHERE id=?');
                $stmt->execute([$name, $username, $email, $role_id, $active, $id]);
            }
            flash('success', 'Usuario actualizado');
        } else {
            flash('error', 'Datos incompletos');
        }
        header('Location: /users');
    }

    public function toggle() {
        $this->requireAdmin();
        csrf_check();
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $stmt = db()->prepare('SELECT active FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if ($user) {
                $newActive = $user['active'] ? 0 : 1;
                $stmt = db()->prepare('UPDATE users SET active = ? WHERE id = ?');
                $stmt->execute([$newActive, $id]);
                flash('success', $newActive ? 'Usuario activado' : 'Usuario desactivado');
            } else {
                flash('error', 'Usuario no encontrado');
            }
        } else {
            flash('error', 'ID inválido');
        }
        header('Location: /users');
    }
}