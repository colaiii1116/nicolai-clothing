<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$email = trim((string)($data['email'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$saved = save_newsletter_subscriber($email);
if ($saved) {
    echo json_encode([
        'success' => true,
        'message' => 'You have successfully subscribed to Nicolai Clothing drops and updates!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not subscribe at this time. Please try again.']);
}
