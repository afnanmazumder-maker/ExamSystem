<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$session_id = (int)($input['session_id'] ?? 0);

if (!$session_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid session ID']);
    exit;
}

// Verify the session belongs to the current user
$verify_stmt = $pdo->prepare('SELECT id FROM exam_sessions WHERE id = ? AND student_id = ? AND is_completed = 0');
$verify_stmt->execute([$session_id, current_user()['id']]);

if (!$verify_stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized or session not found']);
    exit;
}

// Update last activity
$update_stmt = $pdo->prepare('UPDATE exam_sessions SET last_activity = CURRENT_TIMESTAMP WHERE id = ?');
$update_stmt->execute([$session_id]);

echo json_encode(['success' => true]);
?>