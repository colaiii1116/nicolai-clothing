<?php
declare(strict_types=1);

$adminTitle = 'Overview Dashboard';
$activeTab = 'dashboard';
require __DIR__ . '/includes/admin_header.php';

$pdo = get_db();

// Handle quick order status update from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? 'Processing'));
        $paymentStatus = trim((string)($_POST['payment_status'] ?? 'Pending'));
        update_order_status($orderId, $status, $paymentStatus);
        flash('success', "Order #{$orderId} updated to {$status}.");
        redirect('/admin/index.php');
    }
}

// 1. Metrics
$totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM `products` WHERE `is_active` = 1")->fetchColumn();
$totalStock = (int)$pdo->query("SELECT SUM(`stock_quantity`) FROM `products`")->fetchColumn();
$lowStock = (int)$pdo->query("SELECT COUNT(*) FROM `products` WHERE `stock_quantity` < 15")->fetchColumn();

$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
$totalRevenue = (float)$pdo->query("SELECT SUM(`total_amount`) FROM `orders` WHERE `order_status` != 'Cancelled'")->fetchColumn();

$unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM `contact_messages` WHERE `status` = 'New'")->fetchColumn();
$subscribers = (int)$pdo->query("SELECT COUNT(*) FROM `newsletter_subscribers`")->fetchColumn();

// 2. Recent Orders
$stmtRecent = $pdo->query("SELECT * FROM `orders` ORDER BY `created_at` DESC LIMIT 6");
$recentOrders = $stmtRecent->fetchAll();
foreach ($recentOrders as &$o) {
    $stmtIt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` = ?");
    $stmtIt->execute([$o['id']]);
    $o['items'] = $stmtIt->fetchAll();
}
?>

<div style="margin-bottom: 35px;">
  <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.2em; font-size: 11px;">ADMINISTRATION CONTROL</p>
  <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 38px; margin: 6px 0 0; font-weight: 500;">Store Overview &amp; Metrics</h1>
</div>

<?php if ($flashSuccess = flash('success')): ?>
  <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #6edb8f; padding: 12px 18px; margin-bottom: 25px; font-size: 13px;">
    <?= e($flashSuccess) ?>
  </div>
<?php endif; ?>

<!-- Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
  <div class="stat-card">
    <span>TOTAL REVENUE</span>
    <strong><?= format_price($totalRevenue) ?></strong>
    <small>Across <?= $totalOrders ?> orders</small>
  </div>

  <div class="stat-card">
    <span>TOTAL ORDERS</span>
    <strong><?= $totalOrders ?></strong>
    <small><a href="orders.php" style="color:var(--gold); text-decoration:underline;">Manage orders &rarr;</a></small>
  </div>

  <div class="stat-card">
    <span>PRODUCTS CATALOG</span>
    <strong><?= $totalProducts ?></strong>
    <small><?= $activeProducts ?> active &bull; <?= $totalStock ?> units in stock</small>
  </div>

  <div class="stat-card">
    <span>CUSTOMER INQUIRIES</span>
    <strong><?= $unreadMessages ?> New</strong>
    <small><?= $subscribers ?> Newsletter subscribers</small>
  </div>
</div>

<!-- Quick Action Buttons -->
<div style="display: flex; gap: 15px; margin-bottom: 35px; flex-wrap: wrap;">
  <a href="products.php#new" class="btn btn-gold btn-sm" style="padding: 10px 20px;">+ ADD NEW PRODUCT</a>
  <a href="products.php" class="btn btn-outline btn-sm" style="border: 1px solid rgba(193,138,53,0.4); color: #fff; padding: 10px 20px;">MANAGE INVENTORY</a>
  <a href="orders.php" class="btn btn-outline btn-sm" style="border: 1px solid rgba(193,138,53,0.4); color: #fff; padding: 10px 20px;">VIEW ALL ORDERS</a>
  <a href="messages.php" class="btn btn-outline btn-sm" style="border: 1px solid rgba(193,138,53,0.4); color: #fff; padding: 10px 20px;">CUSTOMER INQUIRIES</a>
</div>

<!-- Recent Orders Table -->
<div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 25px; margin-bottom: 40px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px;">
    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; margin: 0; color: #f6f4ef;">Recent Orders</h3>
    <a href="orders.php" style="color: var(--gold); font-size: 12px; text-decoration: underline;">View all &rarr;</a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <p style="color: #9c9489; text-align: center; padding: 30px;">No customer orders placed yet.</p>
  <?php else: ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ORDER #</th>
            <th>CUSTOMER</th>
            <th>DATE</th>
            <th>ITEMS</th>
            <th>TOTAL</th>
            <th>PAYMENT</th>
            <th>ORDER STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $ro): ?>
            <?php
              $badgeClass = match($ro['order_status']) {
                'Processing' => 'badge-processing',
                'Shipped' => 'badge-shipped',
                'Delivered' => 'badge-delivered',
                'Cancelled' => 'badge-cancelled',
                default => 'badge-pending',
              };
              $itemCount = array_reduce($ro['items'], fn($sum, $i) => $sum + $i['quantity'], 0);
            ?>
            <tr>
              <td style="font-family: monospace; font-weight: 600; color: var(--gold);">
                <?= e($ro['order_number']) ?>
              </td>
              <td>
                <strong style="display:block; color: #fff;"><?= e($ro['customer_name']) ?></strong>
                <small style="color: #9c9489;"><?= e($ro['customer_phone']) ?></small>
              </td>
              <td style="color: #9c9489;">
                <?= date('M j, Y, g:i a', strtotime($ro['created_at'])) ?>
              </td>
              <td>
                <?= $itemCount ?> item<?= $itemCount > 1 ? 's' : '' ?>
              </td>
              <td style="font-weight: 600; color: #fff;">
                <?= format_price($ro['total_amount']) ?>
              </td>
              <td>
                <span style="font-size: 11px;"><?= e($ro['payment_method']) ?></span><br>
                <small style="color: #e8c37d;"><?= e($ro['payment_status']) ?></small>
              </td>
              <td>
                <span class="badge <?= $badgeClass ?>"><?= strtoupper(e($ro['order_status'])) ?></span>
              </td>
              <td>
                <form method="post" style="display: flex; gap: 6px; align-items: center;">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="action" value="update_order_status">
                  <input type="hidden" name="order_id" value="<?= $ro['id'] ?>">
                  <select name="status" style="background: rgba(15,13,12,0.9); border: 1px solid #4a433a; color: #fff; font-size: 11px; padding: 5px;">
                    <option value="Pending" <?= $ro['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $ro['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $ro['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Delivered" <?= $ro['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $ro['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                  </select>
                  <button type="submit" class="btn-sm" style="background: var(--gold); border: none; color: #171513; font-weight: 600; cursor: pointer;">Save</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

</main>
</body>
</html>
