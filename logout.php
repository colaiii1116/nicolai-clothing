<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';

// Clear all session data
$_SESSION = [];

// Expire the session cookie immediately
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool)$params['secure'],
        (bool)$params['httponly']
    );
}

// Destroy the session on the server
session_destroy();

// Start a fresh session to deliver the success flash message on the login page
session_start();
flash('success', 'You have been successfully logged out.');

// Use the redirect() helper so subdirectory paths resolve correctly
redirect('/login.php');