<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
require_once __DIR__ . '/validation.php';

function authenticate(string $identity, string $password): bool {
    $identity = strtolower(trim($identity));
    $pdo = get_db();

    $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
    $stmt->execute([$identity]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    if (!headers_sent()) {
        session_regenerate_id(true);
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role'],
        'phone' => $user['phone'] ?? '',
        'address' => $user['address'] ?? '',
        'city' => $user['city'] ?? '',
        'postal_code' => $user['postal_code'] ?? '',
        'country' => $user['country'] ?? 'Philippines',
        'logged_in_at' => date('c'),
    ];

    // Associate active cart with this user
    $sessionId = session_id();
    $pdo->prepare("UPDATE `carts` SET `user_id` = ? WHERE `session_id` = ?")->execute([(int)$user['id'], $sessionId]);

    return true;
}

function process_login(): array {
    $identity = (string)($_POST['identity'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $errors = validate_login($identity, $password);
    if ($errors) return $errors;

    if (!authenticate($identity, $password)) {
        return ['Invalid email address or password.'];
    }

    return [];
}
