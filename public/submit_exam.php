<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /dashboard.php'); exit; }
$exam_id = (int)($_POST['exam_id'] ?? 0);
$answers = $_POST['answers'] ?? [];

// grade
$score = 0;
$total = 0;
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO submissions(exam_id, student_id, score) VALUES(?,?,0)');
    $stmt->execute([$exam_id, current_user()['id']]);
    $submission_id = $pdo->lastInsertId();

    $qs = $pdo->prepare('SELECT q.id FROM questions q WHERE q.exam_id=?');
    $qs->execute([$exam_id]);
    while ($q = $qs->fetch()) {
        $qid = (int)$q['id'];
        $selected = isset($answers[$qid]) ? (int)$answers[$qid] : null;
        $total++;
        if ($selected) {
            $ok = $pdo->prepare('SELECT is_correct FROM options WHERE id=? AND question_id=?');
            $ok->execute([$selected, $qid]);
            $row = $ok->fetch();
            if ($row && (int)$row['is_correct'] === 1) {
                $score++;
            }
        }
        $ins = $pdo->prepare('INSERT INTO answers(submission_id, question_id, selected_option_id) VALUES(?,?,?)');
        $ins->execute([$submission_id, $qid, $selected]);
    }
    $upd = $pdo->prepare('UPDATE submissions SET score=? WHERE id=?');
    $upd->execute([$score, $submission_id]);
    $pdo->commit();
    header('Location: /results.php?id=' . $submission_id);
    exit;
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo 'Submission failed';
}