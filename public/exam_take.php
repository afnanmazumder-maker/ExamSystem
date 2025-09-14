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

// Check for existing exam session or create new one
$session_stmt = $pdo->prepare('SELECT * FROM exam_sessions WHERE exam_id=? AND student_id=? AND is_completed=0');
$session_stmt->execute([$exam_id, current_user()['id']]);
$exam_session = $session_stmt->fetch();

if (!$exam_session) {
    // Create new exam session
    $create_session = $pdo->prepare('INSERT INTO exam_sessions (exam_id, student_id) VALUES (?, ?)');
    $create_session->execute([$exam_id, current_user()['id']]);
    $session_id = $pdo->lastInsertId();
    
    // Get the newly created session
    $session_stmt->execute([$exam_id, current_user()['id']]);
    $exam_session = $session_stmt->fetch();
} else {
    // Update last activity for existing session
    $update_activity = $pdo->prepare('UPDATE exam_sessions SET last_activity = CURRENT_TIMESTAMP WHERE id = ?');
    $update_activity->execute([$exam_session['id']]);
}

// Calculate elapsed time and remaining time
$start_time = new DateTime($exam_session['started_at']);
$current_time = new DateTime();
$elapsed_seconds = $current_time->getTimestamp() - $start_time->getTimestamp();
$total_time_seconds = $exam['time_limit_minutes'] * 60;
$remaining_seconds = max(0, $total_time_seconds - $elapsed_seconds);

// If time is up, redirect or show message
if ($remaining_seconds <= 0) {
    echo '<div class="alert alert-danger">Time is up! This exam has expired.</div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
<h1><?php echo e($exam['title']); ?></h1>
<p><?php echo e($exam['description']); ?></p>
<div class="exam-timer">
  <h3>Time Remaining: <span id="timer"><?php echo floor($remaining_seconds / 60) . ':' . str_pad($remaining_seconds % 60, 2, '0', STR_PAD_LEFT); ?></span></h3>
  <p><small>Exam started at: <?php echo date('H:i:s', strtotime($exam_session['started_at'])); ?></small></p>
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
let timeLeft = <?php echo $remaining_seconds; ?>;
const timerElement = document.getElementById('timer');
const examForm = document.getElementById('exam-form');
const sessionId = <?php echo $exam_session['id']; ?>;

// Update session activity every 30 seconds
setInterval(() => {
    fetch('/update_session_activity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ session_id: sessionId })
    });
}, 30000);

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