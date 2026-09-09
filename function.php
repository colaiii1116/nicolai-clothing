<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string { 
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); 
}

function redirect(string $path): never {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($scriptDir === '' || $scriptDir === '.') {
        $url = '/' . ltrim($path, '/');
    } else {
        $url = $scriptDir . '/' . ltrim($path, '/');
    }
    header('Location: ' . $url);
    exit;
}

function old(string $key, string $default = ''): string { 
    return e((string)($_POST[$key] ?? $default)); 
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function flash(string $key, ?string $value = null): ?string {
    if ($value !== null) { 
        $_SESSION['flash'][$key] = $value; 
        return null; 
    }
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function is_logged_in(): bool { 
    return !empty($_SESSION['user']['id']); 
}

function current_user(): ?array { 
    return $_SESSION['user'] ?? null; 
}

function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        // Pass the current page as ?redirect= so the user returns here after login
        $page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
        $skip = in_array($page, ['login.php', 'register.php', 'logout.php'], true);
        $dir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $dest = $dir . '/login.php' . ($skip ? '' : '?redirect=' . urlencode($page));
        header('Location: ' . $dest);
        exit;
    }
}

function require_admin(): void {
    if (!is_admin()) {
        flash('error', 'Access denied. Administrator privileges required.');
        // Works from both project root and /admin/ subdirectory
        $dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if (preg_match('#/admin$#i', $dir)) { $dir = dirname($dir); }
        header('Location: ' . $dir . '/login.php');
        exit;
    }
}

function format_price(float|string $price): string {
    $num = is_numeric($price) ? (float)$price : (float)preg_replace('/[^0-9.]/', '', (string)$price);
    return '$' . number_format($num, 2);
}

/**
 * Fetch products from database
 */
function fetch_products(?string $category = null, bool $onlyActive = true): array {
    $pdo = get_db();
    $sql = "SELECT * FROM `products` WHERE 1=1";
    $params = [];

    if ($onlyActive) {
        $sql .= " AND `is_active` = 1";
    }

    if ($category !== null && strtoupper($category) !== 'ALL' && trim($category) !== '') {
        $sql .= " AND UPPER(`category`) = UPPER(?)";
        $params[] = trim($category);
    }

    $sql .= " ORDER BY `featured` DESC, `id` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $results = [];
    foreach ($rows as $r) {
        $colors = !empty($r['colors_json']) ? json_decode($r['colors_json'], true) : [];
        $sizes = !empty($r['sizes_json']) ? json_decode($r['sizes_json'], true) : ['S', 'M', 'L', 'XL'];
        $results[] = [
            'id' => $r['id'],
            'name' => $r['name'],
            'base_name' => $r['base_name'],
            'price' => format_price((float)$r['price']),
            'price_num' => (float)$r['price'],
            'image' => $r['image'],
            'color' => $r['color'],
            'colors' => $colors ?: [$r['color'] => $r['image']],
            'sizes' => $sizes ?: ['S', 'M', 'L', 'XL'],
            'desc' => $r['description'],
            'category' => strtoupper($r['category']),
            'stock' => (int)$r['stock_quantity'],
            'is_active' => (bool)$r['is_active'],
            'featured' => (bool)$r['featured'],
        ];
    }
    return $results;
}

/**
 * Fetch a single product by ID
 */
function fetch_product_by_id(string $id): ?array {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) return null;

    $colors = !empty($r['colors_json']) ? json_decode($r['colors_json'], true) : [];
    $sizes = !empty($r['sizes_json']) ? json_decode($r['sizes_json'], true) : ['S', 'M', 'L', 'XL'];

    return [
        'id' => $r['id'],
        'name' => $r['name'],
        'base_name' => $r['base_name'],
        'price' => format_price((float)$r['price']),
        'price_num' => (float)$r['price'],
        'image' => $r['image'],
        'color' => $r['color'],
        'colors' => $colors ?: [$r['color'] => $r['image']],
        'sizes' => $sizes ?: ['S', 'M', 'L', 'XL'],
        'desc' => $r['description'],
        'category' => strtoupper($r['category']),
        'stock' => (int)$r['stock_quantity'],
        'is_active' => (bool)$r['is_active'],
        'featured' => (bool)$r['featured'],
    ];
}

/**
 * Get or create cart ID in database
 */
function get_or_create_cart_id(string $sessionId, ?int $userId = null): int {
    $pdo = get_db();
    
    // Check if user has an existing cart
    if ($userId !== null) {
        $stmt = $pdo->prepare("SELECT `id` FROM `carts` WHERE `user_id` = ? LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
    }

    // Check by session ID
    $stmt = $pdo->prepare("SELECT `id` FROM `carts` WHERE `session_id` = ? LIMIT 1");
    $stmt->execute([$sessionId]);
    $id = $stmt->fetchColumn();
    if ($id) {
        if ($userId !== null) {
            $pdo->prepare("UPDATE `carts` SET `user_id` = ? WHERE `id` = ?")->execute([$userId, $id]);
        }
        return (int)$id;
    }

    // Create new cart
    $stmt = $pdo->prepare("INSERT INTO `carts` (`session_id`, `user_id`) VALUES (?, ?)");
    $stmt->execute([$sessionId, $userId]);
    return (int)$pdo->lastInsertId();
}

/**
 * Retrieve all items in the active cart
 */
function get_cart_items(string $sessionId, ?int $userId = null): array {
    $pdo = get_db();
    $cartId = get_or_create_cart_id($sessionId, $userId);

    $stmt = $pdo->prepare("
        SELECT ci.*, p.name AS product_name, p.image AS default_image, p.stock_quantity, p.colors_json
        FROM `cart_items` ci
        JOIN `products` p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
        ORDER BY ci.id ASC
    ");
    $stmt->execute([$cartId]);
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $r) {
        $colors = !empty($r['colors_json']) ? json_decode($r['colors_json'], true) : [];
        $img = $colors[$r['color']] ?? $r['default_image'];
        $items[] = [
            'id' => (int)$r['id'],
            'cart_id' => (int)$r['cart_id'],
            'key' => $r['product_id'] . '__' . $r['color'] . '__' . $r['size'],
            'productId' => $r['product_id'],
            'name' => $r['product_name'],
            'color' => $r['color'],
            'size' => $r['size'],
            'qty' => (int)$r['quantity'],
            'price' => (float)$r['unit_price'],
            'image' => $img,
            'stock' => (int)$r['stock_quantity'],
        ];
    }
    return $items;
}

/**
 * Add or update an item in the cart
 */
function add_to_cart_db(string $sessionId, ?int $userId, string $productId, string $color, string $size, int $qty): array {
    $pdo = get_db();
    $cartId = get_or_create_cart_id($sessionId, $userId);

    $product = fetch_product_by_id($productId);
    if (!$product || !$product['is_active']) {
        return ['success' => false, 'message' => 'Product not found or unavailable.'];
    }

    if ($product['stock'] < $qty) {
        return ['success' => false, 'message' => "Only {$product['stock']} units available in stock."];
    }

    $unitPrice = $product['price_num'];

    // Check existing item
    $stmt = $pdo->prepare("SELECT `id`, `quantity` FROM `cart_items` WHERE `cart_id` = ? AND `product_id` = ? AND `color` = ? AND `size` = ?");
    $stmt->execute([$cartId, $productId, $color, $size]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = (int)$existing['quantity'] + $qty;
        if ($newQty > $product['stock']) {
            return ['success' => false, 'message' => "Cannot add more. Maximum available stock is {$product['stock']}."];
        }
        $pdo->prepare("UPDATE `cart_items` SET `quantity` = ?, `unit_price` = ? WHERE `id` = ?")
            ->execute([$newQty, $unitPrice, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO `cart_items` (`cart_id`, `product_id`, `color`, `size`, `quantity`, `unit_price`) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$cartId, $productId, $color, $size, $qty, $unitPrice]);
    }

    return ['success' => true, 'items' => get_cart_items($sessionId, $userId)];
}

/**
 * Update quantity of a cart item
 */
function update_cart_item_qty(string $sessionId, ?int $userId, string $key, int $newQty): array {
    $pdo = get_db();
    $cartId = get_or_create_cart_id($sessionId, $userId);

    $parts = explode('__', $key);
    if (count($parts) !== 3) {
        return ['success' => false, 'message' => 'Invalid item key.'];
    }
    [$productId, $color, $size] = $parts;

    if ($newQty <= 0) {
        $pdo->prepare("DELETE FROM `cart_items` WHERE `cart_id` = ? AND `product_id` = ? AND `color` = ? AND `size` = ?")
            ->execute([$cartId, $productId, $color, $size]);
    } else {
        $product = fetch_product_by_id($productId);
        if ($product && $newQty > $product['stock']) {
            return ['success' => false, 'message' => "Maximum available stock is {$product['stock']}."];
        }
        $pdo->prepare("UPDATE `cart_items` SET `quantity` = ? WHERE `cart_id` = ? AND `product_id` = ? AND `color` = ? AND `size` = ?")
            ->execute([$newQty, $cartId, $productId, $color, $size]);
    }

    return ['success' => true, 'items' => get_cart_items($sessionId, $userId)];
}

/**
 * Remove an item from cart
 */
function remove_cart_item_db(string $sessionId, ?int $userId, string $key): array {
    return update_cart_item_qty($sessionId, $userId, $key, 0);
}

/**
 * Clear all cart items
 */
function clear_cart_db(string $sessionId, ?int $userId): void {
    $pdo = get_db();
    $cartId = get_or_create_cart_id($sessionId, $userId);
    $pdo->prepare("DELETE FROM `cart_items` WHERE `cart_id` = ?")->execute([$cartId]);
}

/**
 * Create an order from current cart and customer details
 */
function create_order(array $customer, array $items): array {
    $pdo = get_db();
    $pdo->beginTransaction();

    try {
        if (empty($items)) {
            throw new Exception('Cart is empty.');
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            // Verify stock
            $p = fetch_product_by_id($item['productId']);
            if (!$p || $p['stock'] < $item['qty']) {
                $available = $p ? $p['stock'] : 0;
                throw new Exception("Product \"{$item['name']}\" has only {$available} units left in stock.");
            }
            $subtotal += ($item['price'] * $item['qty']);
        }

        $shippingFee = 0.00; // Free shipping
        $totalAmount = $subtotal + $shippingFee;

        // Generate unique order number
        $orderNumber = 'NC-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $userId = !empty($customer['user_id']) ? (int)$customer['user_id'] : null;

        $stmtOrder = $pdo->prepare("INSERT INTO `orders` (
            `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`,
            `shipping_address`, `city`, `postal_code`, `country`, `subtotal`, `shipping_fee`,
            `total_amount`, `payment_method`, `payment_status`, `order_status`, `notes`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', 'Processing', ?)");

        $stmtOrder->execute([
            $orderNumber,
            $userId,
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['address'],
            $customer['city'],
            $customer['postal_code'],
            $customer['country'] ?? 'Philippines',
            $subtotal,
            $shippingFee,
            $totalAmount,
            $customer['payment_method'],
            $customer['notes'] ?? null
        ]);

        $orderId = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO `order_items` (
            `order_id`, `product_id`, `product_name`, `product_image`, `color`, `size`, `quantity`, `unit_price`, `line_total`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmtStock = $pdo->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` - ? WHERE `id` = ?");

        foreach ($items as $item) {
            $lineTotal = $item['price'] * $item['qty'];
            $stmtItem->execute([
                $orderId,
                $item['productId'],
                $item['name'],
                $item['image'],
                $item['color'],
                $item['size'],
                $item['qty'],
                $item['price'],
                $lineTotal
            ]);
            $stmtStock->execute([$item['qty'], $item['productId']]);
        }

        $pdo->commit();

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total' => $totalAmount,
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}

/**
 * Fetch orders for a user
 */
function fetch_user_orders(int $userId): array {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `created_at` DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$ord) {
        $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();
    }
    return $orders;
}

/**
 * Fetch a single order by order number
 */
function fetch_order_by_number(string $orderNumber): ?array {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `order_number` = ? LIMIT 1");
    $stmt->execute([$orderNumber]);
    $ord = $stmt->fetch();
    if (!$ord) return null;

    $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
    $itemStmt->execute([$ord['id']]);
    $ord['items'] = $itemStmt->fetchAll();
    return $ord;
}

/**
 * Fetch all orders for admin
 */
function fetch_all_orders(): array {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT * FROM `orders` ORDER BY `created_at` DESC");
    $orders = $stmt->fetchAll();

    foreach ($orders as &$ord) {
        $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();
    }
    return $orders;
}

/**
 * Update order status
 */
function update_order_status(int $orderId, string $status, ?string $paymentStatus = null): bool {
    $pdo = get_db();
    if ($paymentStatus !== null) {
        $stmt = $pdo->prepare("UPDATE `orders` SET `order_status` = ?, `payment_status` = ? WHERE `id` = ?");
        return $stmt->execute([$status, $paymentStatus, $orderId]);
    } else {
        $stmt = $pdo->prepare("UPDATE `orders` SET `order_status` = ? WHERE `id` = ?");
        return $stmt->execute([$status, $orderId]);
    }
}

/**
 * Save contact message
 */
function save_contact_message(string $name, string $email, string $subject, string $message): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare("INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $subject, $message]);
}

/**
 * Save newsletter subscriber
 */
function save_newsletter_subscriber(string $email): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare("INSERT INTO `newsletter_subscribers` (`email`) VALUES (?) ON DUPLICATE KEY UPDATE `email` = VALUES(`email`)");
    return $stmt->execute([$email]);
}
