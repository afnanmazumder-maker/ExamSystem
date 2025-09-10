<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();
$user = current_user();
include __DIR__ . '/../includes/header.php';
?>
<h1>Welcome, <?php echo e($user['name']); ?>!</h1>

<?php if ($user['role'] === 'student'): ?>
  <section>
    <h2>Available Exams</h2>
    <ul class="list">
      <?php
      $stmt = $pdo->query('SELECT e.*, u.name AS creator FROM exams e JOIN users u ON e.created_by=u.id WHERE e.is_published=1 ORDER BY e.created_at DESC');
      foreach ($stmt as $exam): ?>
        <li>
          <strong><?php echo e($exam['title']); ?></strong> by <?php echo e($exam['creator']); ?>
          <div><?php echo e($exam['description']); ?></div>
          <a class="btn" href="/exam_take.php?id=<?php echo (int)$exam['id']; ?>">Take Exam</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
<?php else: ?>
  <section>
    <h2>Your Exams</h2>
    <a class="btn" href="/exams_create.php">Create New Exam</a>
    <ul class="list">
    <?php
      $stmt = $pdo->prepare('SELECT * FROM exams WHERE created_by = ? ORDER BY created_at DESC');
      $stmt->execute([$user['id']]);
      foreach ($stmt as $exam): ?>
        <li>
          <strong><?php echo e($exam['title']); ?></strong>
          <span class="tag <?php echo $exam['is_published'] ? 'green' : 'gray'; ?>"><?php echo $exam['is_published'] ? 'Published' : 'Draft'; ?></span>
          <a class="btn" href="/exams_manage.php">Manage</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php if ($user['role'] === 'admin'): ?>
    <section>
      <h2>Admin Tools</h2>
      <p>Default admin is created automatically. Build more tools as needed.</p>
    </section>
  <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>