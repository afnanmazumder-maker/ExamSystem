<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);
$user = current_user();

// Publish/unpublish actions
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'publish') {
        $stmt = $pdo->prepare($user['role']==='admin' ? 'UPDATE exams SET is_published=1 WHERE id=?' : 'UPDATE exams SET is_published=1 WHERE id=? AND created_by=?');
        $stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
    } elseif ($_GET['action'] === 'unpublish') {
        $stmt = $pdo->prepare($user['role']==='admin' ? 'UPDATE exams SET is_published=0 WHERE id=?' : 'UPDATE exams SET is_published=0 WHERE id=? AND created_by=?');
        $stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
    }
    header('Location: /exams_manage.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Exams</h1>
<a class="btn" href="/exams_create.php">Create New Exam</a>
<ul class="list">
<?php
  if ($user['role'] === 'admin') {
      $stmt = $pdo->query('SELECT e.*, u.name AS creator FROM exams e JOIN users u ON e.created_by=u.id ORDER BY e.created_at DESC');
  } else {
      $stmt = $pdo->prepare('SELECT * FROM exams WHERE created_by=? ORDER BY created_at DESC');
      $stmt->execute([$user['id']]);
  }
  foreach ($stmt as $exam): ?>
    <li>
      <strong><?php echo e($exam['title']); ?></strong>
      <?php if (isset($exam['creator'])): ?><em> by <?php echo e($exam['creator']); ?></em><?php endif; ?>
      <span class="tag <?php echo $exam['is_published'] ? 'green' : 'gray'; ?>"><?php echo $exam['is_published'] ? 'Published' : 'Draft'; ?></span>
      <a class="btn" href="/exam_add_question.php?exam_id=<?php echo (int)$exam['id']; ?>">Add Questions</a>
      <?php if ($exam['is_published']): ?>
        <a class="btn secondary" href="/exams_manage.php?action=unpublish&id=<?php echo (int)$exam['id']; ?>">Unpublish</a>
      <?php else: ?>
        <a class="btn" href="/exams_manage.php?action=publish&id=<?php echo (int)$exam['id']; ?>">Publish</a>
      <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
<?php include __DIR__ . '/../includes/footer.php'; ?>