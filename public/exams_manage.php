<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);
$user = current_user();

$message = '';
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'publish') {
        $stmt = $pdo->prepare($user['role']==='admin' ? 'UPDATE exams SET is_published=1 WHERE id=?' : 'UPDATE exams SET is_published=1 WHERE id=? AND created_by=?');
        $stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
        $message = 'Exam published successfully!';
    } elseif ($_GET['action'] === 'unpublish') {
        $stmt = $pdo->prepare($user['role']==='admin' ? 'UPDATE exams SET is_published=0 WHERE id=?' : 'UPDATE exams SET is_published=0 WHERE id=? AND created_by=?');
        $stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
        $message = 'Exam unpublished successfully!';
    } elseif ($_GET['action'] === 'delete' && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
        // Check if user has permission to delete this exam
        $check_stmt = $pdo->prepare($user['role']==='admin' ? 'SELECT id, title FROM exams WHERE id=?' : 'SELECT id, title FROM exams WHERE id=? AND created_by=?');
        $check_stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
        $exam_to_delete = $check_stmt->fetch();
        
        if ($exam_to_delete) {
            // Delete the exam (CASCADE will handle related records)
            $delete_stmt = $pdo->prepare($user['role']==='admin' ? 'DELETE FROM exams WHERE id=?' : 'DELETE FROM exams WHERE id=? AND created_by=?');
            $delete_stmt->execute($user['role']==='admin' ? [$id] : [$id, $user['id']]);
            $message = 'Exam "' . htmlspecialchars($exam_to_delete['title']) . '" deleted successfully!';
        } else {
            $message = 'Error: Exam not found or you do not have permission to delete it.';
        }
    }
    if ($message) {
        header('Location: /exams_manage.php?msg=' . urlencode($message));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Manage Exams</h1>
<?php if (isset($_GET['msg'])): ?>
  <div class="alert success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
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
    <li class="exam-card">
      <?php if (isset($exam['banner_image']) && $exam['banner_image']): ?>
        <div class="exam-banner">
          <img src="/<?php echo e($exam['banner_image']); ?>" alt="<?php echo e($exam['title']); ?> Banner" class="banner-image">
          <div class="banner-overlay">
            <h3 class="exam-title"><?php echo e($exam['title']); ?></h3>
            <?php if (isset($exam['creator'])): ?><p class="exam-creator">by <?php echo e($exam['creator']); ?></p><?php endif; ?>
            <span class="tag <?php echo $exam['is_published'] ? 'green' : 'gray'; ?>"><?php echo $exam['is_published'] ? 'Published' : 'Draft'; ?></span>
          </div>
        </div>
      <?php else: ?>
        <div class="exam-banner">
          <div class="banner-overlay">
            <h3 class="exam-title"><?php echo e($exam['title']); ?></h3>
            <?php if (isset($exam['creator'])): ?><p class="exam-creator">by <?php echo e($exam['creator']); ?></p><?php endif; ?>
            <span class="tag <?php echo $exam['is_published'] ? 'green' : 'gray'; ?>"><?php echo $exam['is_published'] ? 'Published' : 'Draft'; ?></span>
          </div>
        </div>
      <?php endif; ?>
      <div class="exam-content">
        <div class="exam-actions">
          <a class="btn" href="/exam_add_question.php?exam_id=<?php echo (int)$exam['id']; ?>">Add Questions</a>
          <?php if ($exam['is_published']): ?>
            <a class="btn secondary" href="/exams_manage.php?action=unpublish&id=<?php echo (int)$exam['id']; ?>">Unpublish</a>
          <?php else: ?>
            <a class="btn" href="/exams_manage.php?action=publish&id=<?php echo (int)$exam['id']; ?>">Publish</a>
          <?php endif; ?>
          <a class="btn btn-danger" href="#" onclick="confirmDelete(<?php echo (int)$exam['id']; ?>, '<?php echo addslashes($exam['title']); ?>')">Delete</a>
        </div>
      </div>
    </li>
<?php endforeach; ?>
</ul>

<script>
function confirmDelete(examId, examTitle) {
    if (confirm('Are you sure you want to delete the exam "' + examTitle + '"?\n\nThis action cannot be undone and will also delete:\n- All questions and options\n- All student submissions\n- All related data')) {
        window.location.href = '/exams_manage.php?action=delete&id=' + examId + '&confirm=yes';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>