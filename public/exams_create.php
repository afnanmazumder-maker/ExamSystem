<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if (!$title) {
        $error = 'Title is required';
    } else {
        $stmt = $pdo->prepare('INSERT INTO exams(title, description, created_by, is_published) VALUES(?,?,?,0)');
        $stmt->execute([$title, $description, current_user()['id']]);
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
  <button type="submit">Create</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>