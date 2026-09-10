<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = get_db()->query(
            "SELECT id, name, rating, review_text, created_at
             FROM reviews WHERE is_approved = 1
             ORDER BY created_at DESC LIMIT 20"
        );
        echo json_encode(['success' => true, 'reviews' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not load reviews.']);
    }
    exit;
}

if ($method === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true) ?? [];
    $name   = trim((string)($data['name']   ?? ''));
    $rating = (int)($data['rating']         ?? 5);
    $text   = trim((string)($data['review'] ?? ''));

    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if ($text === '') $errors[] = 'Review cannot be empty.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';

    if ($errors) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    try {
        $userId = is_logged_in() ? ((int)(current_user()['id'] ?? 0) ?: null) : null;
        $stmt   = get_db()->prepare(
            "INSERT INTO reviews (user_id, name, rating, review_text, is_approved)
             VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->execute([$userId, $name, $rating, $text]);
        $id  = (int)get_db()->lastInsertId();
        $row = get_db()->prepare("SELECT id, name, rating, review_text, created_at FROM reviews WHERE id = ?");
        $row->execute([$id]);
        echo json_encode(['success' => true, 'review' => $row->fetch()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not save review.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed.']);