<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
require_once __DIR__ . '/validation.php';

if (is_logged_in()) {
    redirect('/student.php');
}

$errors = [];
$redirectDest = trim((string)($_GET['redirect'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session token is invalid. Please try again.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $redirectDest = trim((string)($_POST['redirect'] ?? $redirectDest));

        if ($name === '') $errors[] = 'Please enter your full name.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long.';
        if ($password !== $passwordConfirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $pdo = get_db();
            // Check if email exists
            $stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email address already exists. Please log in.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $insert = $pdo->prepare("INSERT INTO `users` (`email`, `password_hash`, `name`, `role`, `phone`) VALUES (?, ?, ?, 'customer', ?)");
                $insert->execute([$email, $hash, $name, $phone]);
                $newUserId = (int)$pdo->lastInsertId();

                if (!headers_sent()) {
                    session_regenerate_id(true);
                }
                $_SESSION['user'] = [
                    'id' => $newUserId,
                    'email' => $email,
                    'name' => $name,
                    'role' => 'customer',
                    'phone' => $phone,
                    'logged_in_at' => date('c'),
                ];

                // Re-associate active cart
                $sessionId = session_id();
                $pdo->prepare("UPDATE `carts` SET `user_id` = ? WHERE `session_id` = ?")->execute([$newUserId, $sessionId]);

                flash('success', 'Your account has been created successfully!');

                if ($redirectDest === 'checkout.php') {
                    redirect('/checkout.php');
                } else {
                    redirect('/student.php');
                }
            }
        }
    }
}

$page_title = 'Create Account';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-page section-dark">
  <div class="auth-card">
    <p class="eyebrow">NICOLAI CLOTHING</p>
    <h1>Create an Account.</h1>
    <p class="muted">Join Nicolai to track your orders and enjoy exclusive privileges.</p>

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

      <label>Full Name
        <input type="text" name="name" value="<?= old('name') ?>" required autocomplete="name">
      </label>

      <label>Email Address
        <input type="email" name="email" value="<?= old('email') ?>" required autocomplete="email">
      </label>

      <label>Phone Number
        <input type="tel" name="phone" value="<?= old('phone') ?>" placeholder="+63 9XX XXX XXXX" autocomplete="tel">
      </label>

      <label>Password (min 8 characters)
        <input type="password" name="password" required autocomplete="new-password">
      </label>

      <label>Confirm Password
        <input type="password" name="password_confirm" required autocomplete="new-password">
      </label>

      <button class="btn btn-gold" type="submit">CREATE ACCOUNT</button>
    </form>

    <p style="margin-top: 25px; font-size: 12px; color: #b8afa3; text-align: center;">
      Already have an account? <a href="login.php<?= $redirectDest ? '?redirect=' . urlencode($redirectDest) : '' ?>" style="color: var(--gold); text-decoration: underline;">Log in here</a>.
    </p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
