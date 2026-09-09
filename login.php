<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/login_function.php';

$redirectDest = trim((string)($_GET['redirect'] ?? ''));

if (is_logged_in()) {
    if (is_admin()) {
        redirect('/admin/index.php');
    }
    // Honour any safe redirect destination (basename only, no traversal)
    if ($redirectDest !== '' && preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $redirectDest)) {
        redirect('/'. $redirectDest);
    }
    redirect('/student.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectDest = trim((string)($_POST['redirect'] ?? $redirectDest));
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session token is invalid. Please try again.';
    } else {
        $errors = process_login();
        if (!$errors) {
            if (is_admin()) {
                redirect('/admin/index.php');
            }
            // Honour any safe redirect destination (basename only, no traversal)
            if ($redirectDest !== '' && preg_match('/^[a-zA-Z0-9_\-]+\.php$/', $redirectDest)) {
                redirect('/'. $redirectDest);
            }
            redirect('/student.php');
        }
    }
}

$page_title = 'Login';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page section-dark">
  <div class="auth-card">
    <p class="eyebrow">NICOLAI CLOTHING</p>
    <h1>Welcome back.</h1>
    <p class="muted">Sign in to your Nicolai account.</p>

    <?php if ($flashSuccess = flash('success')): ?>
      <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #6edb8f; padding: 12px; margin-bottom: 20px; font-size: 13px; text-align: center;">
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <?php if ($flashError = flash('error')): ?>
      <div style="background: rgba(220, 60, 60, 0.15); border: 1px solid rgba(220, 60, 60, 0.4); color: #ffb4b4; padding: 12px; margin-bottom: 20px; font-size: 13px; text-align: center;">
        <?= e($flashError) ?>
      </div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="form-errors" role="alert">
        <?php foreach ($errors as $error): ?>
          <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="redirect" value="<?= e($redirectDest) ?>">

      <label>Email Address
        <input type="email" name="identity" value="<?= old('identity') ?>" autocomplete="username" required>
      </label>

      <label>Password
        <input type="password" name="password" autocomplete="current-password" required>
      </label>

      <button class="btn btn-gold" type="submit">LOGIN</button>
    </form>

    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08); text-align: center; font-size: 12px; color: #b8afa3;">
      <p style="margin-bottom: 8px;">Don't have an account yet?</p>
      <a href="register.php<?= $redirectDest ? '?redirect=' . urlencode($redirectDest) : '' ?>" style="color: var(--gold); text-decoration: underline; font-weight: 500;">
        Create a New Account &rarr;
      </a>
    </div>

    <div class="demo-note" style="margin-top: 25px; background: rgba(255,255,255,0.03); border: 1px dashed rgba(193,138,53,0.3); padding: 12px; font-size: 11px; color: #9c9489; line-height: 1.5;">
      <strong>Demo Credentials:</strong><br>
      Customer: <code style="color:var(--gold);">student@nicolai.local</code> / <code style="color:var(--gold);">Nicolai123!</code><br>
      Administrator: <code style="color:var(--gold);">admin@nicolai.local</code> / <code style="color:var(--gold);">Admin123!</code>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
