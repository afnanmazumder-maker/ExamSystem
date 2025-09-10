<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();
$user = current_user();
include __DIR__ . '/../includes/header.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($user['role'] === 'student') {
        $stmt = $pdo->prepare('SELECT s.*, e.title FROM submissions s JOIN exams e ON s.exam_id=e.id WHERE s.id=? AND s.student_id=?');
        $stmt->execute([$id, $user['id']]);
    } else {
        $stmt = $pdo->prepare('SELECT s.*, e.title, u.name as student FROM submissions s JOIN exams e ON s.exam_id=e.id JOIN users u ON s.student_id=u.id WHERE s.id=?');
        $stmt->execute([$id]);
    }
    $submission = $stmt->fetch();
    if ($submission): ?>
      <h1>Result: <?php echo e($submission['title']); ?></h1>
      <p>Score: <strong><?php echo (int)$submission['score']; ?></strong></p>
      <?php if (!empty($submission['student'])): ?>
        <p>Student: <?php echo e($submission['student']); ?></p>
      <?php endif; ?>
      <a class="btn" href="/results.php">Back to Results</a>
    <?php else: ?>
      <p>Result not found.</p>
    <?php endif; ?>
<?php } else { ?>
  <?php if ($user['role'] === 'student'): ?>
    <h1>Your Results</h1>
    <ul class="list">
    <?php
      $stmt = $pdo->prepare('SELECT s.*, e.title FROM submissions s JOIN exams e ON s.exam_id=e.id WHERE s.student_id=? ORDER BY s.submitted_at DESC');
      $stmt->execute([$user['id']]);
      foreach ($stmt as $row): ?>
        <li>
          <strong><?php echo e($row['title']); ?></strong>
          <span>Score: <?php echo (int)$row['score']; ?></span>
          <a class="btn" href="/results.php?id=<?php echo (int)$row['id']; ?>">View</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <h1>All Submissions (by Exam)</h1>
    <form method="get">
      <label>Exam ID <input type="number" name="exam_id" value="<?php echo isset($_GET['exam_id'])?(int)$_GET['exam_id']:''; ?>"></label>
      <button type="submit">Filter</button>
    </form>
    <ul class="list">
    <?php
      if (isset($_GET['exam_id'])) {
        $exam_id = (int)$_GET['exam_id'];
        $stmt = $pdo->prepare('SELECT s.*, e.title, u.name as student FROM submissions s JOIN exams e ON s.exam_id=e.id JOIN users u ON s.student_id=u.id WHERE s.exam_id=? ORDER BY s.submitted_at DESC');
        $stmt->execute([$exam_id]);
        foreach ($stmt as $row): ?>
          <li>
            <strong><?php echo e($row['title']); ?></strong> by <?php echo e($row['student']); ?> - Score: <?php echo (int)$row['score']; ?>
            <a class="btn" href="/results.php?id=<?php echo (int)$row['id']; ?>">View</a>
          </li>
        <?php endforeach; ?>
      <?php } else { echo '<li>Enter an Exam ID to view submissions.</li>'; } ?>
    </ul>
  <?php endif; ?>
<?php } ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>