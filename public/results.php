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
    if ($submission): 
        // Get total questions for percentage calculation
        $total_stmt = $pdo->prepare('SELECT COUNT(*) as total FROM questions WHERE exam_id = ?');
        $total_stmt->execute([$submission['exam_id']]);
        $total_questions = $total_stmt->fetch()['total'];
        $percentage = $total_questions > 0 ? round(($submission['score'] / $total_questions) * 100, 1) : 0;
    ?>
      <h1>Result: <?php echo e($submission['title']); ?></h1>
      <div class="result-summary">
        <p><strong>Score: <?php echo (int)$submission['score']; ?> / <?php echo $total_questions; ?> (<?php echo $percentage; ?>%)</strong></p>
        <p>Submitted: <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?></p>
        <?php if (!empty($submission['student'])): ?>
          <p>Student: <?php echo e($submission['student']); ?></p>
        <?php endif; ?>
      </div>
      
      <h2>Answer Review</h2>
      <div class="answer-review">
        <?php
        // Get all questions for this exam with answers
        $questions_stmt = $pdo->prepare('
            SELECT q.id, q.question_text,
                   a.selected_option_id,
                   GROUP_CONCAT(o.id ORDER BY o.id) as option_ids,
                   GROUP_CONCAT(o.option_text ORDER BY o.id SEPARATOR "||||") as option_texts,
                   GROUP_CONCAT(o.is_correct ORDER BY o.id) as correct_flags
            FROM questions q
            LEFT JOIN answers a ON q.id = a.question_id AND a.submission_id = ?
            LEFT JOIN options o ON q.id = o.question_id
            WHERE q.exam_id = ?
            GROUP BY q.id, q.question_text, a.selected_option_id
            ORDER BY q.id
        ');
        $questions_stmt->execute([$submission['id'], $submission['exam_id']]);
        $question_num = 1;
        
        foreach ($questions_stmt as $question):
            $option_ids = explode(',', $question['option_ids']);
            $option_texts = explode('||||', $question['option_texts']);
            $correct_flags = explode(',', $question['correct_flags']);
            $selected_option = $question['selected_option_id'];
            
            // Find correct answer
            $correct_option_id = null;
            for ($i = 0; $i < count($option_ids); $i++) {
                if ($correct_flags[$i] == '1') {
                    $correct_option_id = $option_ids[$i];
                    break;
                }
            }
            
            $is_correct = ($selected_option == $correct_option_id);
        ?>
        <div class="question-review <?php echo $is_correct ? 'correct' : 'incorrect'; ?>">
          <h3>Q<?php echo $question_num++; ?>: <?php echo e($question['question_text']); ?></h3>
          <div class="options-review">
            <?php for ($i = 0; $i < count($option_ids); $i++): 
                $option_id = $option_ids[$i];
                $option_text = $option_texts[$i];
                $is_selected = ($selected_option == $option_id);
                $is_correct_option = ($correct_flags[$i] == '1');
                
                $class = '';
                if ($is_correct_option) $class .= ' correct-answer';
                if ($is_selected) $class .= ' selected-answer';
                if ($is_selected && !$is_correct_option) $class .= ' wrong-selection';
            ?>
            <div class="option-review<?php echo $class; ?>">
              <span class="option-indicator">
                <?php if ($is_selected): ?>
                  <?php echo $is_correct_option ? '✓' : '✗'; ?>
                <?php elseif ($is_correct_option): ?>
                  ✓
                <?php endif; ?>
              </span>
              <span class="option-text"><?php echo e($option_text); ?></span>
              <?php if ($is_correct_option): ?>
                <span class="correct-label">Correct Answer</span>
              <?php endif; ?>
              <?php if ($is_selected && !$is_correct_option): ?>
                <span class="wrong-label">Your Answer</span>
              <?php endif; ?>
            </div>
            <?php endfor; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      
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