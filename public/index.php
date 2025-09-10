<?php
require_once __DIR__ . '/../includes/helpers.php';
ensure_default_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($email, $password)) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
include __DIR__ . '/../includes/header.php';
?>
<section class="auth">
  <h1>Login</h1>
  <?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post">
    <label>Email
      <input type="email" name="email" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
  </form>
  <p>No account? <a href="/register.php">Register</a></p>
  <p>Default admin: admin@example.com / admin123</p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>