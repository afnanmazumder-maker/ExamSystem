<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);
$user = current_user();
$exam_id = (int)($_GET['exam_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM exams WHERE id=?');
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();
if (!$exam || ($user['role'] !== 'admin' && $exam['created_by'] != $user['id'])) {
    http_response_code(404);
    die('Exam not found');
}

$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $options = $_POST['options'] ?? [];
    $correct = (int)($_POST['correct'] ?? -1);
    if (!$question_text || count($options) < 2) {
        $error = 'Provide a question and at least two options';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO questions(exam_id, question_text, type) VALUES(?, ?, "mcq")');
            $stmt->execute([$exam_id, $question_text]);
            $qid = $pdo->lastInsertId();
            foreach ($options as $idx => $opt) {
                $stmt = $pdo->prepare('INSERT INTO options(question_id, option_text, is_correct) VALUES(?, ?, ?)');
                $stmt->execute([$qid, trim($opt), $idx == $correct ? 1 : 0]);
            }
            $pdo->commit();
            header('Location: /exam_add_question.php?exam_id=' . $exam_id);
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Failed to add question';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<h1>Add Question to: <?php echo e($exam['title']); ?></h1>
<?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
<form method="post">
  <label>Question
    <textarea name="question_text" rows="3" required></textarea>
  </label>
  <div class="options">
    <h3>Options</h3>
    <?php for ($i=0;$i<4;$i++): ?>
      <div class="option-row">
        <input type="radio" name="correct" value="<?php echo $i; ?>" <?php echo $i===0?'checked':''; ?>>
        <input type="text" name="options[]" placeholder="Option text" required>
      </div>
    <?php endfor; ?>
  </div>
  <button type="submit">Add Question</button>
</form>

<h2>Current Questions</h2>
<ol>
<?php
$q = $pdo->prepare('SELECT * FROM questions WHERE exam_id=?');
$q->execute([$exam_id]);
foreach ($q as $row) {
    echo '<li>'.e($row['question_text']).'</li>';
}
?>
</ol>
<a class="btn" href="/exams_manage.php">Back to Manage</a>
<?php include __DIR__ . '/../includes/footer.php'; ?>