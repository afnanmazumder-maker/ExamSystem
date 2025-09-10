<?php
require_once __DIR__ . '/config.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return current_user() !== null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /index.php');
        exit;
    }
}

function require_role($roles) {
    require_login();
    $user = current_user();
    $roles = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        die('Forbidden');
    }
}

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}