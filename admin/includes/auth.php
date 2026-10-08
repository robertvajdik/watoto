<?php
require_once __DIR__ . '/../../includes/functions.php';

function admin_user() {
    return $_SESSION['admin'] ?? null;
}

function require_admin() {
    if (!admin_user()) {
        redirect(SITE_URL . '/admin/index.php');
    }
}

function admin_login($username, $password) {
    global $pdo;
    $st = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
    $st->execute([$username, $username]);
    $u = $st->fetch();
    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['admin'] = [
            'id' => $u['id'],
            'username' => $u['username'],
            'email' => $u['email'],
            'full_name' => $u['full_name'] ?: $u['username'],
            'role' => $u['role'],
        ];
        return true;
    }
    return false;
}

function admin_logout() {
    unset($_SESSION['admin']);
    session_regenerate_id(true);
}
