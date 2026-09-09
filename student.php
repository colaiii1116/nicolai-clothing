<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';

require_login();
$user = current_user();
$userId = (int)$user['id'];

// Refresh user record from DB
$pdo = get_db();
$stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
$stmt->execute([$userId]);
$dbUser = $stmt->fetch() ?: $user;

$orders = fetch_user_orders($userId);

$page_title = 'My Account';
require __DIR__ . '/includes/header.php';
?>

<section class="student-page section-light" style="min-height: 85vh; padding: 60px 8vw;">
  <div class="student-shell" style="max-width: 1050px; margin: 0 auto;">
    <p class="eyebrow">CUSTOMER PORTAL</p>
    <h1>Hello, <?= e($dbUser['name']) ?>.</h1>
    <p class="lead">Welcome to your personal Nicolai Clothing account dashboard.</p>

    <?php if ($flashSuccess = flash('success')): ?>
      <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #2d7a46; padding: 12px; margin-bottom: 25px; font-size: 13px;">
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <!-- User Overview Cards -->
    <div class="student-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
      <article class="dashboard-card">
        <span>ACCOUNT EMAIL</span>
        <strong><?= e($dbUser['email']) ?></strong>
        <small>Role: <?= strtoupper(e($dbUser['role'])) ?></small>
      </article>

      <article class="dashboard-card">
        <span>TOTAL ORDERS</span>
        <strong><?= count($orders) ?> Orders Placed</strong>
        <small>Lifetime order count</small>
      </article>

      <article class="dashboard-card">
        <span>DELIVERY ADDRESS</span>
        <strong style="font-size: 13px; font-weight: 500;"><?= e($dbUser['address'] ?: 'Not specified yet') ?></strong>
        <small><?= e($dbUser['city'] ? $dbUser['city'] . ', ' . $dbUser['postal_code'] : 'Add at checkout') ?></small>
      </article>

      <article class="dashboard-card">
        <span>ACCOUNT STATUS</span>
        <strong style="color: #3b7e4f;">ACTIVE &bull; VERIFIED</strong>
        <small>Member since <?= substr($dbUser['created_at'] ?? date('Y-m-d'), 0, 10) ?></small>
      </article>
    </div>

    <?php if (is_admin()): ?>
      <div style="background: #171513; color: #fff; padding: 22px; margin-bottom: 40px; border-left: 4px solid var(--gold); display: flex; justify-content: space-between; align-items: center; flex-wrap: gap: 15px;">
        <div>
          <strong style="font-size: 15px; display: block; color: var(--gold);">Administrator Privileges Detected</strong>
          <span style="font-size: 12px; color: #b8afa3;">You have full administrative access to manage product catalog, inventory, and customer orders.</span>
        </div>
        <a href="admin/index.php" class="btn btn-gold" style="white-space: nowrap;">OPEN ADMIN DASHBOARD</a>
      </div>
    <?php endif; ?>

    <!-- Order History Section -->
    <div class="order-history-section" style="background: #fff; border: 1px solid #dfd7cb; padding: 30px; margin-bottom: 40px;">
      <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; margin-top: 0; margin-bottom: 20px; color: var(--ink); border-bottom: 1px solid #eee; padding-bottom: 12px;">
        My Order History
      </h3>

      <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 40px 10px; color: #7a7369;">
          <p style="font-size: 15px; margin-bottom: 15px;">You haven't placed any orders yet.</p>
          <a href="index.php#shop" class="btn btn-dark">EXPLORE THE COLLECTION</a>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid #cfc6b7; color: #5a5349; letter-spacing: 0.05em;">
                <th style="padding: 12px 10px;">ORDER #</th>
                <th style="padding: 12px 10px;">DATE</th>
                <th style="padding: 12px 10px;">ITEMS</th>
                <th style="padding: 12px 10px;">PAYMENT</th>
                <th style="padding: 12px 10px;">TOTAL</th>
                <th style="padding: 12px 10px;">STATUS</th>
                <th style="padding: 12px 10px;">ACTION</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $ord): ?>
                <?php
                  $statusColor = match($ord['order_status']) {
                    'Delivered' => '#2e7d32',
                    'Shipped' => '#1565c0',
                    'Processing' => '#f57f17',
                    'Cancelled' => '#c62828',
                    default => '#6a6358',
                  };
                  $itemCount = array_reduce($ord['items'], fn($sum, $i) => $sum + $i['quantity'], 0);
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                  <td style="padding: 14px 10px; font-weight: 600; font-family: monospace; font-size: 13px;">
                    <?= e($ord['order_number']) ?>
                  </td>
                  <td style="padding: 14px 10px; color: #6a6358;">
                    <?= date('M j, Y', strtotime($ord['created_at'])) ?>
                  </td>
                  <td style="padding: 14px 10px;">
                    <?= $itemCount ?> item<?= $itemCount > 1 ? 's' : '' ?>
                  </td>
                  <td style="padding: 14px 10px; color: #6a6358;">
                    <?= e($ord['payment_method']) ?>
                  </td>
                  <td style="padding: 14px 10px; font-weight: 600; color: var(--ink);">
                    <?= format_price($ord['total_amount']) ?>
                  </td>
                  <td style="padding: 14px 10px;">
                    <span style="display: inline-block; padding: 3px 8px; border-radius: 2px; font-size: 10px; font-weight: 600; letter-spacing: 0.05em; background: <?= $statusColor ?>15; color: <?= $statusColor ?>; border: 1px solid <?= $statusColor ?>40;">
                      <?= strtoupper(e($ord['order_status'])) ?>
                    </span>
                  </td>
                  <td style="padding: 14px 10px;">
                    <a href="order-confirmation.php?order=<?= urlencode($ord['order_number']) ?>" style="color: var(--gold); text-decoration: underline; font-weight: 500;">
                      View Receipt &rarr;
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Actions Row -->
    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
      <a class="btn btn-dark" href="logout.php">LOG OUT</a>
      <a class="btn btn-outline" href="index.php" style="border: 1px solid #333; color: #333;">CONTINUE SHOPPING</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
