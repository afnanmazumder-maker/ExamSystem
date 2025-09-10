<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
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
<main class="container main">