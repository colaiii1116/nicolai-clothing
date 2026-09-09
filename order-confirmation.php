<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';

$orderNumber = trim((string)($_GET['order'] ?? ''));
$order = $orderNumber ? fetch_order_by_number($orderNumber) : null;

$clearCart = !empty($_GET['clearcart']);

$page_title = 'Order Confirmation';
require __DIR__ . '/includes/header.php';
?>

<?php if ($clearCart): ?>
<script>
// Clear client-side cart storage upon confirmed order placement
try {
  localStorage.removeItem('nicolai_cart_v1');
  const badge = document.querySelector('#cartBadge');
  if (badge) badge.textContent = '0';
  const headerCount = document.querySelector('#cartCountHeader');
  if (headerCount) headerCount.textContent = '0';
} catch (e) {}
</script>
<?php endif; ?>

<section class="order-confirmation-page section-dark" style="min-height: 85vh; padding: 70px 8vw;">
  <div class="confirmation-box" style="max-width: 800px; margin: 0 auto; background: rgba(30, 27, 24, 0.85); border: 1px solid rgba(193, 138, 53, 0.3); padding: 45px; border-radius: 2px;">
    
    <?php if (!$order): ?>
      <div style="text-align: center; padding: 40px 0;">
        <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.25em;">NICOLAI CLOTHING</p>
        <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 38px; margin: 15px 0;">Order Not Found</h1>
        <p style="color: #b8afa3; margin-bottom: 30px;">We could not locate the specified order number.</p>
        <a href="index.php" class="btn btn-gold">RETURN HOME</a>
      </div>
    <?php else: ?>
      <div style="text-align: center; margin-bottom: 35px; border-bottom: 1px solid rgba(193, 138, 53, 0.2); padding-bottom: 30px;">
        <div style="width: 58px; height: 58px; margin: 0 auto 16px; border-radius: 50%; background: rgba(193, 138, 53, 0.15); border: 1px solid var(--gold); display: flex; align-items: center; justify-content: center; color: var(--gold); font-size: 26px;">
          ✓
        </div>
        <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.25em; font-size: 11px;">ORDER CONFIRMED</p>
        <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 42px; margin: 10px 0; font-weight: 500;">Thank You, <?= e($order['customer_name']) ?>!</h1>
        <p style="color: #b8afa3; font-size: 14px;">Your order has been placed successfully and is now being processed.</p>
        <div style="display: inline-block; background: rgba(15, 13, 12, 0.7); border: 1px solid rgba(193, 138, 53, 0.4); padding: 8px 20px; margin-top: 15px; font-family: monospace; font-size: 16px; color: var(--gold); letter-spacing: 0.1em;">
          ORDER # <?= e($order['order_number']) ?>
        </div>
      </div>

      <!-- Order Details Grid -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 35px; font-size: 13px;">
        <div style="background: rgba(20, 18, 16, 0.6); padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
          <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 18px; margin-top: 0; margin-bottom: 12px; color: var(--gold);">Shipping Details</h4>
          <p style="margin: 4px 0; color: #f6f4ef; font-weight: 500;"><?= e($order['customer_name']) ?></p>
          <p style="margin: 4px 0; color: #b8afa3;"><?= e($order['shipping_address']) ?></p>
          <p style="margin: 4px 0; color: #b8afa3;"><?= e($order['city']) ?>, <?= e($order['postal_code']) ?></p>
          <p style="margin: 4px 0; color: #b8afa3;"><?= e($order['country']) ?></p>
          <p style="margin: 4px 0; color: #b8afa3;">Phone: <?= e($order['customer_phone']) ?></p>
          <p style="margin: 4px 0; color: #b8afa3;">Email: <?= e($order['customer_email']) ?></p>
        </div>

        <div style="background: rgba(20, 18, 16, 0.6); padding: 20px; border: 1px solid rgba(255,255,255,0.06);">
          <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 18px; margin-top: 0; margin-bottom: 12px; color: var(--gold);">Order Information</h4>
          <p style="margin: 4px 0; color: #b8afa3;">Date: <strong style="color: #f6f4ef;"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></strong></p>
          <p style="margin: 4px 0; color: #b8afa3;">Order Status: <span style="display: inline-block; background: rgba(110, 219, 143, 0.15); color: #6edb8f; padding: 2px 8px; border-radius: 2px; font-size: 11px; font-weight: 600;"><?= e($order['order_status']) ?></span></p>
          <p style="margin: 4px 0; color: #b8afa3;">Payment Method: <strong style="color: #f6f4ef;"><?= e($order['payment_method']) ?></strong></p>
          <p style="margin: 4px 0; color: #b8afa3;">Payment Status: <strong style="color: #e8c37d;"><?= e($order['payment_status']) ?></strong></p>
        </div>
      </div>

      <!-- Items List -->
      <div style="margin-bottom: 30px;">
        <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; margin-bottom: 15px; color: #f6f4ef; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 8px;">
          Purchased Items
        </h4>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($order['items'] as $item): ?>
            <div style="display: flex; gap: 16px; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.04);">
              <img src="assets/products/<?= e($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" style="width: 60px; height: 65px; object-fit: contain; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); padding: 4px; border-radius: 2px;">
              <div style="flex: 1;">
                <strong style="color: #f6f4ef; font-size: 13px; display: block;"><?= e($item['product_name']) ?></strong>
                <span style="font-size: 11px; color: #a39b90;">Color: <?= e($item['color']) ?> &bull; Size: <?= e($item['size']) ?></span>
                <span style="font-size: 11px; color: #a39b90; margin-left: 10px;">Qty: <?= $item['quantity'] ?> &times; <?= format_price($item['unit_price']) ?></span>
              </div>
              <div style="color: var(--gold); font-weight: 600; font-size: 14px;">
                <?= format_price($item['line_total']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="margin-top: 20px; border-top: 1px solid rgba(193, 138, 53, 0.3); padding-top: 15px;">
          <div style="display: flex; justify-content: space-between; font-size: 13px; color: #b8afa3; margin-bottom: 8px;">
            <span>Subtotal</span>
            <span style="color: #f6f4ef;"><?= format_price($order['subtotal']) ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 13px; color: #b8afa3; margin-bottom: 12px;">
            <span>Express Delivery</span>
            <span style="color: #6edb8f; font-weight: 600;">FREE</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 18px; color: #f6f4ef; font-weight: 600;">
            <span style="font-family: 'Cormorant Garamond', serif; font-size: 22px;">Total Paid / Due</span>
            <span style="color: var(--gold); font-size: 20px;"><?= format_price($order['total_amount']) ?></span>
          </div>
        </div>
      </div>

      <div style="display: flex; gap: 15px; justify-content: center; margin-top: 35px;">
        <a href="index.php" class="btn btn-gold">CONTINUE SHOPPING</a>
        <?php if (is_logged_in()): ?>
          <a href="student.php" class="btn btn-outline" style="border: 1px solid rgba(193,138,53,0.5); color: #f6f4ef; padding: 12px 24px;">VIEW ALL ORDERS</a>
        <?php endif; ?>
      </div>

    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
