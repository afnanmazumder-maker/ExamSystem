<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('student');
$exam_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM exams WHERE id=? AND is_published=1');
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();
if (!$exam) { http_response_code(404); die('Exam not found or not available'); }
include __DIR__ . '/../includes/header.php';
?>
<h1><?php echo e($exam['title']); ?></h1>
<p><?php echo e($exam['description']); ?></p>
<form method="post" action="/submit_exam.php">
  <input type="hidden" name="exam_id" value="<?php echo (int)$exam['id']; ?>">
  <?php
  $qs = $pdo->prepare('SELECT * FROM questions WHERE exam_id=?');
  $qs->execute([$exam['id']]);
  $num = 1;
  foreach ($qs as $q) {
      echo '<div class="question">';
      echo '<h3>Q'.$num++.': '.e($q['question_text']).'</h3>';
      $opts = $pdo->prepare('SELECT * FROM options WHERE question_id=?');
      $opts->execute([$q['id']]);
      foreach ($opts as $opt) {
          echo '<label class="option"><input type="radio" name="answers['.$q['id'].']" value="'.$opt['id'].'" required> '.e($opt['option_text'])."</label>";
      }
      echo '</div>';
  }
  ?>
  <button type="submit">Submit</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>