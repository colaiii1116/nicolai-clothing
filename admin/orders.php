<?php
declare(strict_types=1);

$adminTitle = 'Customer Orders Management';
$activeTab = 'orders';
require __DIR__ . '/includes/admin_header.php';

$pdo = get_db();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $orderStatus = trim((string)($_POST['order_status'] ?? ''));
        $paymentStatus = trim((string)($_POST['payment_status'] ?? ''));

        if ($orderId && $orderStatus) {
            update_order_status($orderId, $orderStatus, $paymentStatus ?: null);
            flash('success', "Order #{$orderId} updated to {$orderStatus} / {$paymentStatus}.");
            redirect('/admin/orders.php');
        }
    }
}

$statusFilter = trim((string)($_GET['status'] ?? 'ALL'));

$sql = "SELECT * FROM `orders` WHERE 1=1";
$params = [];
if ($statusFilter !== 'ALL' && $statusFilter !== '') {
    $sql .= " AND `order_status` = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY `created_at` DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

foreach ($orders as &$ord) {
    $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
    $itemStmt->execute([$ord['id']]);
    $ord['items'] = $itemStmt->fetchAll();
}
?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
  <div>
    <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.2em; font-size: 11px;">ORDERS &amp; FULFILLMENT</p>
    <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 38px; margin: 6px 0 0; font-weight: 500;">Customer Orders (<?= count($orders) ?>)</h1>
  </div>

  <!-- Status Filter Pills -->
  <div style="display: flex; gap: 8px; flex-wrap: wrap;">
    <?php foreach (['ALL', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st): ?>
      <a href="orders.php?status=<?= urlencode($st) ?>" class="btn-sm" style="background: <?= $statusFilter === $st ? 'var(--gold)' : 'rgba(30,27,24,0.8)' ?>; color: <?= $statusFilter === $st ? '#171513' : '#cfc6b7' ?>; border: 1px solid rgba(193,138,53,0.3); font-weight: 600;">
        <?= strtoupper($st) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($flashSuccess = flash('success')): ?>
  <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #6edb8f; padding: 12px 18px; margin-bottom: 25px; font-size: 13px;">
    <?= e($flashSuccess) ?>
  </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
  <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 50px; text-align: center; color: #9c9489;">
    <p>No orders found matching the selected filter.</p>
  </div>
<?php else: ?>
  <div style="display: flex; flex-direction: column; gap: 25px;">
    <?php foreach ($orders as $ord): ?>
      <?php
        $badgeClass = match($ord['order_status']) {
          'Processing' => 'badge-processing',
          'Shipped' => 'badge-shipped',
          'Delivered' => 'badge-delivered',
          'Cancelled' => 'badge-cancelled',
          default => 'badge-pending',
        };
      ?>
      <div style="background: rgba(30, 27, 24, 0.85); border: 1px solid rgba(193, 138, 53, 0.25); padding: 25px;">
        
        <!-- Header Row -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 15px; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
          <div>
            <span style="font-family: monospace; font-size: 16px; font-weight: 600; color: var(--gold);">
              ORDER # <?= e($ord['order_number']) ?>
            </span>
            <span style="color: #8a8277; font-size: 12px; margin-left: 15px;">
              Placed on <?= date('F j, Y \a\t g:i a', strtotime($ord['created_at'])) ?>
            </span>
          </div>

          <div style="display: flex; gap: 12px; align-items: center;">
            <span class="badge <?= $badgeClass ?>"><?= strtoupper(e($ord['order_status'])) ?></span>
            <span style="font-size: 16px; font-weight: 600; color: #fff; margin-left: 10px;">
              <?= format_price($ord['total_amount']) ?>
            </span>
          </div>
        </div>

        <!-- Customer and Shipping Details -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 20px; margin-bottom: 20px; font-size: 12px; background: rgba(15,13,12,0.5); padding: 15px; border: 1px solid rgba(255,255,255,0.04);">
          <div>
            <span style="color: var(--gold); font-size: 10px; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">CUSTOMER</span>
            <strong style="color: #fff; display: block;"><?= e($ord['customer_name']) ?></strong>
            <span style="color: #a39b90;"><?= e($ord['customer_email']) ?></span><br>
            <span style="color: #a39b90;"><?= e($ord['customer_phone']) ?></span>
          </div>

          <div>
            <span style="color: var(--gold); font-size: 10px; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">SHIPPING DESTINATION</span>
            <span style="color: #cfc6b7;"><?= e($ord['shipping_address']) ?></span><br>
            <span style="color: #cfc6b7;"><?= e($ord['city']) ?>, <?= e($ord['postal_code']) ?></span><br>
            <span style="color: #cfc6b7;"><?= e($ord['country']) ?></span>
          </div>

          <div>
            <span style="color: var(--gold); font-size: 10px; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">PAYMENT &amp; NOTES</span>
            <span style="color: #fff; font-weight: 500;"><?= e($ord['payment_method']) ?></span><br>
            <span style="color: #e8c37d;">Payment: <?= e($ord['payment_status']) ?></span>
            <?php if (!empty($ord['notes'])): ?>
              <p style="color: #9c9489; font-style: italic; margin-top: 6px;">"<?= e($ord['notes']) ?>"</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Line Items -->
        <div style="margin-bottom: 20px;">
          <strong style="color: #fff; font-size: 12px; letter-spacing: 0.05em; display: block; margin-bottom: 10px;">ORDERED ITEMS</strong>
          <div style="display: flex; flex-direction: column; gap: 8px;">
            <?php foreach ($ord['items'] as $item): ?>
              <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.25); padding: 8px 12px; border: 1px solid rgba(255,255,255,0.04); font-size: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <img src="../assets/products/<?= e($item['product_image']) ?>" alt="" style="width: 40px; height: 42px; object-fit: contain; background: #000; padding: 2px;">
                  <div>
                    <strong style="color: #fff;"><?= e($item['product_name']) ?></strong>
                    <span style="color: #9c9489; margin-left: 10px;">Color: <?= e($item['color']) ?> &bull; Size: <?= e($item['size']) ?></span>
                  </div>
                </div>
                <div>
                  <span style="color: #9c9489; margin-right: 15px;"><?= $item['quantity'] ?> &times; <?= format_price($item['unit_price']) ?></span>
                  <strong style="color: var(--gold);"><?= format_price($item['line_total']) ?></strong>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Order Update Form -->
        <form method="post" style="display: flex; gap: 12px; align-items: center; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 15px; flex-wrap: wrap;">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">

          <label style="font-size: 11px; color: #a39b90;">Fulfillment Status:
            <select name="order_status" style="background: rgba(15,13,12,0.9); border: 1px solid #4a433a; color: #fff; font-size: 11px; padding: 6px 10px; margin-left: 6px;">
              <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
              <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
              <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
              <option value="Cancelled" <?= $ord['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
          </label>

          <label style="font-size: 11px; color: #a39b90;">Payment Status:
            <select name="payment_status" style="background: rgba(15,13,12,0.9); border: 1px solid #4a433a; color: #fff; font-size: 11px; padding: 6px 10px; margin-left: 6px;">
              <option value="Pending" <?= $ord['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
              <option value="Confirmed" <?= $ord['payment_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
              <option value="Paid" <?= $ord['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
              <option value="Failed" <?= $ord['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
            </select>
          </label>

          <button type="submit" class="btn btn-gold btn-sm">UPDATE ORDER</button>
        </form>

      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

</main>
</body>
</html>
