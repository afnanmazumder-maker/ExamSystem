<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $time_limit = (int)($_POST['time_limit_minutes'] ?? 60);
    if (!$title) {
        $error = 'Title is required';
    } elseif ($time_limit < 1 || $time_limit > 300) {
        $error = 'Time limit must be between 1 and 300 minutes';
    } else {
        $stmt = $pdo->prepare('INSERT INTO exams(title, description, created_by, is_published, time_limit_minutes) VALUES(?,?,?,0,?)');
        $stmt->execute([$title, $description, current_user()['id'], $time_limit]);
        $exam_id = $pdo->lastInsertId();
        header('Location: /exam_add_question.php?exam_id=' . $exam_id);
        exit;
    }
}
include __DIR__ . '/../includes/header.php';
?>
<h1>Create Exam</h1>
<?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
<form method="post">
  <label>Title
    <input type="text" name="title" required>
  </label>
  <label>Description
    <textarea name="description" rows="4"></textarea>
  </label>
  <label>Time Limit (minutes)
    <input type="number" name="time_limit_minutes" value="60" min="1" max="300" required>
  </label>
  <button type="submit">Create</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>