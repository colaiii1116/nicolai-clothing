<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
require_login();
$user = current_user();
$page_title = 'Success';
require __DIR__ . '/includes/header.php';
?>
<section class="success-page section-dark">
  <div class="success-box">
    <p class="eyebrow">NICOLAI CLOTHING</p>
    <div class="success-mark">✓</div>
    <h1>Login successful.</h1>
    <p>Welcome, <?= e($user['name']) ?>. Your account is authenticated and ready.</p>
    <div class="success-actions"><a class="btn btn-gold" href="student.php">STUDENT AREA</a><a class="btn btn-outline" href="index.php">HOME</a></div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
