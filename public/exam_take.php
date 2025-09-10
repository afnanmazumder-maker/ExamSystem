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

$check_stmt = $pdo->prepare('SELECT id FROM submissions WHERE exam_id=? AND student_id=?');
$check_stmt->execute([$exam_id, current_user()['id']]);
if ($check_stmt->fetch()) {
    die('You have already taken this exam.');
}

include __DIR__ . '/../includes/header.php';
?>
<h1><?php echo e($exam['title']); ?></h1>
<p><?php echo e($exam['description']); ?></p>
<div class="exam-timer">
  <h3>Time Remaining: <span id="timer"><?php echo $exam['time_limit_minutes']; ?>:00</span></h3>
</div>
<form method="post" action="/submit_exam.php" id="exam-form">
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

<script>
let timeLeft = <?php echo $exam['time_limit_minutes'] * 60; ?>;
const timerElement = document.getElementById('timer');
const examForm = document.getElementById('exam-form');

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
        alert('Time is up! The exam will be submitted automatically.');
        examForm.submit();
        return;
    }
    
    timeLeft--;
}

const timerInterval = setInterval(updateTimer, 1000);

updateTimer();

setTimeout(() => {
    if (timeLeft <= 300) {
        alert('Warning: Only 5 minutes remaining!');
    }
}, (timeLeft - 300) * 1000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>