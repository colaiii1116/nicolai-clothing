<?php
if (!isset($page_title)) $page_title = SITE_NAME;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Nicolai Clothing — timeless style and premium quality.">
<title><?= e($page_title) ?> | <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="index.php" aria-label="Nicolai Clothing home"><span>NICOLAI</span><small>CLOTHING</small></a>
  <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav">MENU</button>
  <nav id="main-nav" class="main-nav">
    <a href="index.php">HOME</a>
    <a href="info.php">ABOUT</a>
    <a href="index.php#shop">SHOP</a>
    <a href="index.php#collections">COLLECTIONS</a>
    <a href="index.php#lookbook">LOOKBOOK</a>
    <a href="index.php#contact">CONTACT</a>
    <?php if (is_admin()): ?>
      <a class="nav-admin" href="admin/index.php" style="color: var(--gold); font-weight: 600;">ADMIN</a>
    <?php endif; ?>
    <?php if (is_logged_in()): $__navUser = current_user(); $__firstName = explode(' ', $__navUser['name'] ?? 'Account')[0]; ?>
      <div class="nav-account-group">
        <span class="nav-user-greeting">Hi, <?= htmlspecialchars($__firstName, ENT_QUOTES, 'UTF-8') ?></span>
        <a class="nav-login" href="student.php">ACCOUNT</a>
        <a class="nav-logout" href="logout.php">LOGOUT</a>
      </div>
    <?php else: ?>
      <a class="nav-login" href="login.php">LOGIN</a>
    <?php endif; ?>
    <button class="cart-toggle-btn" id="cartToggleBtn" type="button" aria-label="Open Shopping Bag">
      <svg class="cart-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <span class="cart-label">CART</span>
      <span class="cart-badge" id="cartBadge">0</span>
    </button>
  </nav>
</header>

<!-- Shopping Cart Drawer & Backdrop Overlay -->
<div class="cart-overlay" id="cartOverlay" aria-hidden="true"></div>
<aside class="cart-drawer" id="cartDrawer" role="dialog" aria-modal="true" aria-label="Shopping Cart" aria-hidden="true">
  <div class="cart-header">
    <h3>SHOPPING BAG (<span id="cartCountHeader">0</span>)</h3>
    <button class="cart-close" id="cartCloseBtn" type="button" aria-label="Close Shopping Bag">&times;</button>
  </div>
  <div class="cart-body" id="cartBody">
    <!-- Items rendered dynamically via script.js -->
  </div>
  <div class="cart-footer" id="cartFooter">
    <div class="cart-summary-line">
      <span>SUBTOTAL</span>
      <span id="cartSubtotal">$0.00</span>
    </div>
    <div class="cart-summary-line">
      <span>SHIPPING</span>
      <span class="free-shipping">FREE</span>
    </div>
    <div class="cart-summary-line cart-total-line">
      <span>TOTAL</span>
      <span id="cartTotal">$0.00</span>
    </div>
    <button class="btn btn-gold cart-checkout-btn" id="cartCheckoutBtn" type="button">PROCEED TO CHECKOUT</button>
    <button class="cart-continue-btn" id="cartContinueBtn" type="button">CONTINUE SHOPPING</button>
  </div>
</aside>

<main>
