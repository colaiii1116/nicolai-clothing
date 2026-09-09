<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';

header('Content-Type: application/json');

$sessionId = session_id();
$userId = is_logged_in() ? (int)$_SESSION['user']['id'] : null;

$method = $_SERVER['REQUEST_METHOD'];

// ── Require login for all write (POST) actions ──────────────
if ($method === 'POST') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode([
            'success'  => false,
            'auth'     => false,
            'message'  => 'Please log in to add products to your cart.',
        ]);
        exit;
    }
}

if ($method === 'GET') {
    $items = get_cart_items($sessionId, $userId);
    $totalCount = array_reduce($items, fn($sum, $i) => $sum + $i['qty'], 0);
    $subtotal = array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['qty']), 0.0);

    echo json_encode([
        'success' => true,
        'items' => $items,
        'totalCount' => $totalCount,
        'subtotal' => $subtotal,
        'formattedSubtotal' => format_price($subtotal),
    ]);
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $action = $data['action'] ?? 'add';

    if ($action === 'add') {
        $productId = trim((string)($data['productId'] ?? ''));
        $color = trim((string)($data['color'] ?? 'Black'));
        $size = trim((string)($data['size'] ?? 'M'));
        $qty = max(1, (int)($data['qty'] ?? 1));

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required.']);
            exit;
        }

        $res = add_to_cart_db($sessionId, $userId, $productId, $color, $size, $qty);
        if (!$res['success']) {
            echo json_encode($res);
            exit;
        }

        $items = $res['items'];
        $totalCount = array_reduce($items, fn($sum, $i) => $sum + $i['qty'], 0);
        $subtotal = array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['qty']), 0.0);

        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart.',
            'items' => $items,
            'totalCount' => $totalCount,
            'subtotal' => $subtotal,
            'formattedSubtotal' => format_price($subtotal),
        ]);
        exit;
    }

    if ($action === 'update') {
        $key = trim((string)($data['key'] ?? ''));
        $qty = (int)($data['qty'] ?? 1);

        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'Item key is required.']);
            exit;
        }

        $res = update_cart_item_qty($sessionId, $userId, $key, $qty);
        $items = $res['items'] ?? [];
        $totalCount = array_reduce($items, fn($sum, $i) => $sum + $i['qty'], 0);
        $subtotal = array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['qty']), 0.0);

        echo json_encode([
            'success' => $res['success'] ?? true,
            'message' => $res['message'] ?? 'Cart updated.',
            'items' => $items,
            'totalCount' => $totalCount,
            'subtotal' => $subtotal,
            'formattedSubtotal' => format_price($subtotal),
        ]);
        exit;
    }

    if ($action === 'remove') {
        $key = trim((string)($data['key'] ?? ''));
        if (!$key) {
            echo json_encode(['success' => false, 'message' => 'Item key is required.']);
            exit;
        }

        $res = remove_cart_item_db($sessionId, $userId, $key);
        $items = $res['items'] ?? [];
        $totalCount = array_reduce($items, fn($sum, $i) => $sum + $i['qty'], 0);
        $subtotal = array_reduce($items, fn($sum, $i) => $sum + ($i['price'] * $i['qty']), 0.0);

        echo json_encode([
            'success' => true,
            'items' => $items,
            'totalCount' => $totalCount,
            'subtotal' => $subtotal,
            'formattedSubtotal' => format_price($subtotal),
        ]);
        exit;
    }

    if ($action === 'clear') {
        clear_cart_db($sessionId, $userId);
        echo json_encode([
            'success' => true,
            'items' => [],
            'totalCount' => 0,
            'subtotal' => 0,
            'formattedSubtotal' => '$0.00',
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
