<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
            $stmt->execute([$name, $email, $hash, in_array($role, ['student','teacher']) ? $role : 'student']);
            $success = 'Registration successful. You can now login.';
        } catch (Throwable $e) {
            $error = 'Registration failed. Email may already be used.';
        }
    }
}
include __DIR__ . '/../includes/header.php';
?>
<section class="auth">
  <h1>Register</h1>
  <?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?php echo e($success); ?></div><?php endif; ?>
  <form method="post">
    <label>Name
      <input type="text" name="name" required>
    </label>
    <label>Email
      <input type="email" name="email" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <label>Role
      <select name="role">
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
      </select>
    </label>
    <button type="submit">Register</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>