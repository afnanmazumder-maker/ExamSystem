<?php
require_once __DIR__ . '/auth.php';
$user = current_user();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$bg_class = '';
switch($current_page) {
  case 'index':
    $bg_class = 'login-bg';
    break;
  case 'register':
    $bg_class = 'register-bg';
    break;
  case 'dashboard':
    $bg_class = 'dashboard-bg';
    break;
  case 'exam_take':
    $bg_class = 'exam-bg';
    break;
  case 'results':
    $bg_class = 'results-bg';
    break;
  default:
    $bg_class = 'dashboard-bg';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Exam System</title>
  <link rel="stylesheet" href="/assets/css/styles.css">
  <script defer src="/assets/js/app.js"></script>
</head>
<body>
<!-- Page Background -->
<div class="page-bg <?php echo $bg_class; ?>"></div>
<header class="site-header">
  <div class="container">
    <div class="brand">Exam System</div>
    <nav>
      <ul>
        <?php if ($user): ?>
          <li><a href="/dashboard.php">Dashboard</a></li>
          <li><a href="/results.php">Results</a></li>
          <?php if (in_array($user['role'], ['teacher','admin'], true)): ?>
            <li><a href="/exams_create.php">Create Exam</a></li>
            <li><a href="/exams_manage.php">Manage Exams</a></li>
          <?php endif; ?>
          <li><a href="/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="/index.php">Login</a></li>
          <li><a href="/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>
<main class="container main content-overlay" style="margin-top: 2rem; padding: 2rem;">