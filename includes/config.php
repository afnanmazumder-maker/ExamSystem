<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Railway MySQL configuration
$MYSQL_URL = getenv('MYSQL_URL');
if ($MYSQL_URL) {
    $url = parse_url($MYSQL_URL);
    $DB_HOST = $url['host'];
    $DB_NAME = ltrim($url['path'], '/');
    $DB_USER = $url['user'];
    $DB_PASS = $url['pass'];
    $DB_PORT = $url['port'] ?? 3306;
} else {
    // Local development fallback
    $DB_HOST = getenv('DB_HOST') ?: 'db';
    $DB_NAME = getenv('DB_NAME') ?: 'examsys';
    $DB_USER = getenv('DB_USER') ?: 'examsys';
    $DB_PASS = getenv('DB_PASS') ?: 'examsys123';
    $DB_PORT = 3306;
}

try {
    $pdo = new PDO("mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    die('Database connection failed.');
}

$BASE_URL = '/';