<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
require_login(); // Must be logged in to checkout

$sessionId = session_id();
$currentUser = current_user();
$userId = $currentUser['id'] ?? null;

$cartItems = get_cart_items($sessionId, $userId ? (int)$userId : null);
$totalCount = array_reduce($cartItems, fn($sum, $i) => $sum + $i['qty'], 0);
$subtotal = array_reduce($cartItems, fn($sum, $i) => $sum + ($i['price'] * $i['qty']), 0.0);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session has expired. Please try submitting again.';
    } elseif (empty($cartItems)) {
        $errors[] = 'Your shopping bag is empty. Please add items before checking out.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $city = trim((string)($_POST['city'] ?? ''));
        $postalCode = trim((string)($_POST['postal_code'] ?? ''));
        $country = trim((string)($_POST['country'] ?? 'Philippines'));
        $paymentMethod = trim((string)($_POST['payment_method'] ?? 'Cash on Delivery (COD)'));
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($name === '') $errors[] = 'Please enter your full name.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if ($phone === '') $errors[] = 'Please enter a contact phone number.';
        if ($address === '') $errors[] = 'Please enter your delivery street address.';
        if ($city === '') $errors[] = 'Please enter your city/municipality.';
        if ($postalCode === '') $errors[] = 'Please enter your postal code.';

        if (empty($errors)) {
            $customerData = [
                'user_id' => $userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'country' => $country,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ];

            $orderResult = create_order($customerData, $cartItems);

            if ($orderResult['success']) {
                clear_cart_db($sessionId, $userId ? (int)$userId : null);
                // Also trigger client-side localStorage cleanup via query param
                redirect('/order-confirmation.php?order=' . urlencode($orderResult['order_number']) . '&clearcart=1');
            } else {
                $errors[] = $orderResult['message'] ?? 'Failed to place order. Please try again.';
            }
        }
    }
}

$page_title = 'Checkout';
require __DIR__ . '/includes/header.php';
?>

<section class="checkout-page section-dark" style="min-height: 85vh; padding: 60px 8vw;">
  <div class="checkout-container" style="max-width: 1100px; margin: 0 auto;">
    <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.25em; font-size: 11px; margin-bottom: 8px;">SECURE CHECKOUT</p>
    <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 500; margin-bottom: 30px;">Complete Your Order.</h1>

    <?php if ($errors): ?>
      <div class="form-errors" role="alert" style="background: rgba(180, 40, 40, 0.15); border: 1px solid rgba(220, 60, 60, 0.4); color: #ffb4b4; padding: 16px 20px; border-radius: 4px; margin-bottom: 30px;">
        <?php foreach ($errors as $error): ?>
          <p style="margin: 4px 0; font-size: 13px;">✕ <?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($cartItems)): ?>
      <div class="checkout-empty" style="text-align: center; padding: 60px 20px; background: rgba(35, 31, 28, 0.6); border: 1px solid rgba(193, 138, 53, 0.2);">
        <p style="font-size: 18px; color: #cfc6b7; margin-bottom: 20px;">Your shopping bag is currently empty.</p>
        <a href="index.php#shop" class="btn btn-gold">DISCOVER PRODUCTS</a>
      </div>
    <?php else: ?>
      <div class="checkout-grid" style="display: grid; grid-template-columns: 1fr 420px; gap: 45px; align-items: start;">
        
        <!-- Left: Customer & Shipping Information -->
        <div class="checkout-form-col">
          <form method="post" novalidate id="checkoutForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <!-- Customer Identity -->
            <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 26px; margin-bottom: 25px;">
              <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-top: 0; margin-bottom: 18px; color: #f6f4ef; border-bottom: 1px solid rgba(193,138,53,0.2); padding-bottom: 10px;">
                1. Customer Information
              </h3>
              <?php if (!is_logged_in()): ?>
                <p style="font-size: 12px; color: #b8afa3; margin-bottom: 15px;">
                  Already have an account? <a href="login.php?redirect=checkout.php" style="color: var(--gold); text-decoration: underline;">Log in</a> to speed up checkout.
                </p>
              <?php endif; ?>

              <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                FULL NAME *
                <input type="text" name="name" required value="<?= old('name', $currentUser['name'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
              </label>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                  EMAIL ADDRESS *
                  <input type="email" name="email" required value="<?= old('email', $currentUser['email'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
                </label>
                <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                  PHONE NUMBER *
                  <input type="tel" name="phone" required placeholder="+63 9XX XXX XXXX" value="<?= old('phone', $currentUser['phone'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
                </label>
              </div>
            </div>

            <!-- Delivery Address -->
            <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 26px; margin-bottom: 25px;">
              <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-top: 0; margin-bottom: 18px; color: #f6f4ef; border-bottom: 1px solid rgba(193,138,53,0.2); padding-bottom: 10px;">
                2. Shipping Address
              </h3>

              <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                STREET ADDRESS *
                <input type="text" name="address" required placeholder="House/Unit #, Street, Barangay" value="<?= old('address', $currentUser['address'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
              </label>

              <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                  CITY / MUNICIPALITY *
                  <input type="text" name="city" required placeholder="Dumaguete City" value="<?= old('city', $currentUser['city'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
                </label>
                <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                  POSTAL CODE *
                  <input type="text" name="postal_code" required placeholder="6200" value="<?= old('postal_code', $currentUser['postal_code'] ?? '') ?>" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none;">
                </label>
              </div>

              <label style="display:block; margin-bottom: 14px; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                COUNTRY
                <input type="text" name="country" readonly value="Philippines" style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(20, 18, 16, 0.6); border: 1px solid #3d3730; color: #9c9489; font-size: 13px; outline: none;">
              </label>

              <label style="display:block; margin-bottom: 0; font-size: 11px; letter-spacing: 0.1em; color: #b8afa3;">
                ORDER NOTES (OPTIONAL)
                <textarea name="notes" rows="2" placeholder="Special delivery instructions or landmark..." style="width: 100%; box-sizing: border-box; margin-top: 6px; padding: 12px; background: rgba(15, 13, 12, 0.8); border: 1px solid #4a433a; color: #fff; font-size: 13px; outline: none; resize: vertical;"><?= old('notes') ?></textarea>
              </label>
            </div>

            <!-- Payment Method -->
            <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 26px; margin-bottom: 25px;">
              <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; margin-top: 0; margin-bottom: 18px; color: #f6f4ef; border-bottom: 1px solid rgba(193,138,53,0.2); padding-bottom: 10px;">
                3. Payment Method
              </h3>

              <div style="display: flex; flex-direction: column; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 12px; padding: 14px; background: rgba(20, 18, 16, 0.7); border: 1px solid rgba(193, 138, 53, 0.3); cursor: pointer;">
                  <input type="radio" name="payment_method" value="Cash on Delivery (COD)" checked style="accent-color: var(--gold);">
                  <div>
                    <strong style="display: block; font-size: 13px; color: #fff;">Cash on Delivery (COD)</strong>
                    <small style="color: #9c9489;">Pay upon receiving your package at your doorstep.</small>
                  </div>
                </label>

                <label style="display: flex; align-items: center; gap: 12px; padding: 14px; background: rgba(20, 18, 16, 0.7); border: 1px solid #3d3730; cursor: pointer;">
                  <input type="radio" name="payment_method" value="GCash / Maya / Bank Transfer" style="accent-color: var(--gold);">
                  <div>
                    <strong style="display: block; font-size: 13px; color: #fff;">GCash / Maya / Online Bank</strong>
                    <small style="color: #9c9489;">Electronic transfer details provided on confirmation.</small>
                  </div>
                </label>

                <label style="display: flex; align-items: center; gap: 12px; padding: 14px; background: rgba(20, 18, 16, 0.7); border: 1px solid #3d3730; cursor: pointer;">
                  <input type="radio" name="payment_method" value="Credit / Debit Card" style="accent-color: var(--gold);">
                  <div>
                    <strong style="display: block; font-size: 13px; color: #fff;">Credit / Debit Card</strong>
                    <small style="color: #9c9489;">Visa, Mastercard, JCB, or American Express.</small>
                  </div>
                </label>
              </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; padding: 16px; font-size: 14px; letter-spacing: 0.2em; font-weight: 600;">
              PLACE ORDER &bull; <?= format_price($subtotal) ?>
            </button>
          </form>
        </div>

        <!-- Right: Order Summary -->
        <div class="checkout-summary-col" style="background: rgba(30, 27, 24, 0.75); border: 1px solid rgba(193, 138, 53, 0.25); padding: 26px; position: sticky; top: 90px;">
          <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-top: 0; margin-bottom: 20px; color: #f6f4ef; border-bottom: 1px solid rgba(193,138,53,0.2); padding-bottom: 10px;">
            Order Summary (<?= $totalCount ?>)
          </h3>

          <div class="summary-items" style="max-height: 380px; overflow-y: auto; margin-bottom: 20px; padding-right: 5px;">
            <?php foreach ($cartItems as $item): ?>
              <div style="display: flex; gap: 14px; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); align-items: center;">
                <img src="assets/products/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" style="width: 55px; height: 60px; object-fit: contain; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); padding: 4px; border-radius: 2px;">
                <div style="flex: 1;">
                  <strong style="display: block; font-size: 12px; color: #f6f4ef;"><?= e($item['name']) ?></strong>
                  <span style="font-size: 11px; color: #a39b90;">Color: <?= e($item['color']) ?> &bull; Size: <?= e($item['size']) ?></span>
                  <div style="font-size: 11px; color: #a39b90; margin-top: 2px;">Qty: <?= $item['qty'] ?> &times; <?= format_price($item['price']) ?></div>
                </div>
                <div style="font-weight: 600; font-size: 13px; color: var(--gold);">
                  <?= format_price($item['price'] * $item['qty']) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="display: flex; justify-content: space-between; font-size: 13px; color: #b8afa3; margin-bottom: 10px;">
            <span>Subtotal</span>
            <span style="color: #fff; font-weight: 500;"><?= format_price($subtotal) ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 13px; color: #b8afa3; margin-bottom: 18px;">
            <span>Express Shipping</span>
            <span style="color: #6edb8f; font-weight: 600; letter-spacing: 0.05em;">FREE</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 18px; color: #f6f4ef; border-top: 1px solid rgba(193,138,53,0.3); padding-top: 15px; margin-bottom: 20px;">
            <span style="font-family: 'Cormorant Garamond', serif; font-size: 20px;">Total</span>
            <span style="font-weight: 600; color: var(--gold);"><?= format_price($subtotal) ?></span>
          </div>

          <div style="font-size: 11px; color: #8e867b; line-height: 1.5; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 15px;">
            <p style="margin: 4px 0;">✓ 100% Authentic Nicolai Merchandise</p>
            <p style="margin: 4px 0;">✓ Complimentary Express Delivery Across Philippines</p>
            <p style="margin: 4px 0;">✓ 30-Day Hassle-Free Returns &amp; Exchanges</p>
          </div>
        </div>

      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
