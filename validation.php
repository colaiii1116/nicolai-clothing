<?php
declare(strict_types=1);

function required(string $value, string $field): ?string {
    return trim($value) === '' ? $field . ' is required.' : null;
}
function validate_login(string $identity, string $password): array {
    $errors = [];
    if (($error = required($identity, 'Email')) !== null) $errors[] = $error;
    if (($error = required($password, 'Password')) !== null) $errors[] = $error;
    if ($identity !== '' && !filter_var($identity, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($password !== '' && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    return $errors;
}
