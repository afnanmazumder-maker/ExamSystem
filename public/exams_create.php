<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role(['teacher','admin']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $time_limit = (int)($_POST['time_limit_minutes'] ?? 60);
    $banner_image = null;
    
    if (!$title) {
        $error = 'Title is required';
    } elseif ($time_limit < 1 || $time_limit > 300) {
        $error = 'Time limit must be between 1 and 300 minutes';
    } else {
        // Handle banner image upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['banner_image']['type'];
            $file_size = $_FILES['banner_image']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Invalid file type. Please upload JPEG, PNG, GIF, or WebP images only.';
            } elseif ($file_size > 5 * 1024 * 1024) { // 5MB limit
                $error = 'File size too large. Maximum 5MB allowed.';
            } else {
                $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
                $banner_filename = 'banner_' . uniqid() . '.' . $file_extension;
                $upload_path = __DIR__ . '/uploads/banners/' . $banner_filename;
                
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_path)) {
                    $banner_image = 'uploads/banners/' . $banner_filename;
                } else {
                    $error = 'Failed to upload banner image.';
                }
            }
        }
        
        if (!$error) {
            $stmt = $pdo->prepare('INSERT INTO exams(title, description, banner_image, created_by, is_published, time_limit_minutes) VALUES(?,?,?,?,0,?)');
            $stmt->execute([$title, $description, $banner_image, current_user()['id'], $time_limit]);
            $exam_id = $pdo->lastInsertId();
            header('Location: /exam_add_question.php?exam_id=' . $exam_id);
            exit;
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<h1>Create Exam</h1>
<?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <label>Title
    <input type="text" name="title" required>
  </label>
  <label>Description
    <textarea name="description" rows="4"></textarea>
  </label>
  <label>Banner Image (Optional)
    <input type="file" name="banner_image" accept="image/*">
    <small>Upload a banner image for your exam (JPEG, PNG, GIF, WebP - Max 5MB)</small>
  </label>
  <label>Time Limit (minutes)
    <input type="number" name="time_limit_minutes" value="60" min="1" max="300" required>
  </label>
  <button type="submit">Create</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>