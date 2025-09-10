<?php
require_once __DIR__ . '/config.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function ensure_default_admin() {
    global $pdo;
    try {
        $count = (int)$pdo->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch()['c'];
        if ($count === 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users(name, email, password_hash, role) VALUES(?, ?, ?, ?)');
            $stmt->execute(['Admin', 'admin@example.com', $hash, 'admin']);
        }
    } catch (Throwable $e) {
    }
}