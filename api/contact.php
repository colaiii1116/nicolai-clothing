<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$name    = trim((string)($data['name']    ?? ''));
$email   = trim((string)($data['email']   ?? ''));
$subject = trim((string)($data['subject'] ?? ''));
$message = trim((string)($data['message'] ?? ''));

// Basic validation
if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($message) < 5) {
    echo json_encode(['success' => false, 'message' => 'Message is too short.']);
    exit;
}

try {
    $pdo  = get_db();
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$name, $email, $subject, $message]);

    // Optional: send email notification (uses PHP mail — works if SMTP is configured)
    $to      = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'micolaifaith@gmail.com';
    $headers = implode("\r\n", [
        "From: Nicolai Clothing Contact <no-reply@nicolaiclothing.com>",
        "Reply-To: {$name} <{$email}>",
        "Content-Type: text/plain; charset=UTF-8",
    ]);
    $body = "New Contact Message\n"
          . "===================\n"
          . "Name:    {$name}\n"
          . "Email:   {$email}\n"
          . "Subject: {$subject}\n\n"
          . "Message:\n{$message}\n";
    @mail($to, "[Nicolai] {$subject}", $body, $headers);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent. We\'ll get back to you soon.',
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}