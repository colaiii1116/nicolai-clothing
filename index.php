<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/function.php';
$page_title = 'Home';
require __DIR__ . '/includes/header.php';

// Fetch real products dynamically from MySQL database
$products = fetch_products();
// Product catalog JSON for JavaScript hydration
$productsJson = json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<script>
window.NICOLAI_PRODUCTS  = <?= $productsJson ?>;
window.NICOLAI_LOGGED_IN = <?= is_logged_in() ? 'true' : 'false' ?>;
window.NICOLAI_LOGIN_URL = '<?= e(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) ?>/login.php';
</script>

<section class="hero section-dark" id="home">
  <div class="hero-copy">
    <p class="eyebrow">CLOTHING</p>
    <h1>WEAR<br><span>CONFIDENCE.</span><br>LIVE NICOLAI.</h1>
    <p>Timeless style. Premium quality.<br>Made for those who lead.</p>
    <a class="btn btn-gold" href="#shop">SHOP NOW</a>
  </div>
  <div class="hero-model"><img src="assets/products/hero-model.png" alt="Nicolai Clothing model wearing a black shirt"></div>
  <div class="hero-logo">N<span>NC</span><small>NICOLAI<br>CLOTHING</small></div>
</section>

<section class="benefits section-dark">
  <article><b>✦</b><h3>PREMIUM QUALITY</h3><p>Carefully selected materials for comfort and durability.</p></article>
  <article><b>✦</b><h3>TIMELESS DESIGN</h3><p>Minimal, modern, and made to stand out.</p></article>
  <article><b>✦</b><h3>FAST SHIPPING</h3><p>Quick and reliable delivery right to your door.</p></article>
  <article><b>✦</b><h3>CUSTOMER SUPPORT</h3><p>We're here to help you anytime.</p></article>
</section>

<section class="new-arrivals section-light" id="shop">
  <div class="section-heading left">
    <p class="eyebrow">01 03</p>
    <h2>New Drops.<br>Elevated Style.</h2>
    <p>Discover our latest pieces.<br>Designed for your everyday.</p>
    <a class="text-link" href="#products">VIEW ALL &rarr;</a>
  </div>
  <div class="arrival-grid">
    <?php foreach (array_slice($products, 0, 6) as $p): ?>
      <article class="arrival-card" data-product-id="<?= e($p['id']) ?>" data-category="<?= e($p['category']) ?>">
        <div class="product-stage" data-inspect-id="<?= e($p['id']) ?>">
          <img src="assets/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        </div>
        <h3 class="product-card-title" data-inspect-id="<?= e($p['id']) ?>"><?= e($p['name']) ?></h3>
        <strong class="product-card-price"><?= e($p['price']) ?></strong>

      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="about section-dark" id="about">
  <div class="about-copy">
    <p class="eyebrow">ABOUT US</p>
    <h2>More than clothing.<br>It's a lifestyle.</h2>
    <p>Nicolai Clothing is built on the belief that what you wear reflects who you are. We create timeless, high-quality pieces that combine comfort, style, and purpose. Designed for those who lead with confidence and live with intention.</p>
    <a class="btn btn-gold" href="info.php">OUR STORY</a>
  </div>
  <div class="about-images">
    <img src="assets/products/brand-label.png" alt="Nicolai clothing label">
    <img src="assets/products/clothing-rack.png" alt="Nicolai clothing rack">
  </div>
</section>

<section class="values section-light">
  <article><p class="eyebrow">OUR MISSION</p><p>To deliver high-quality clothing that inspires confidence and empowers individuality.</p></article>
  <article><p class="eyebrow">OUR VISION</p><p>To be a trusted lifestyle brand known for timeless design and authenticity.</p></article>
  <article><p class="eyebrow">OUR VALUES</p><p>Quality, Integrity, Purpose, and Continuous Improvement in everything we do.</p></article>
</section>

<section class="collections section-dark" id="collections">
  <div class="section-heading">
    <p class="eyebrow">01</p>
    <h2>COLLECTIONS</h2>
    <p>Elevated essentials. Timeless pieces.<br>Made to move with you.</p>
  </div>
  <div class="tabs">
    <button class="active">ALL</button>
    <button>TOPS</button>
    <button>HOODIES</button>
    <button>OUTERWEAR</button>
    <button>ACCESSORIES</button>
  </div>
  <div class="collection-grid">
    <a href="#products"><div><h3>TOPS</h3><span>Shop now &rarr;</span></div><img src="assets/products/classic-tee-model.png" alt="Tops collection"></a>
    <a href="#products"><div><h3>HOODIES</h3><span>Shop now &rarr;</span></div><img src="assets/products/hoodie-model.png" alt="Hoodies collection"></a>
    <a href="#products"><div><h3>OUTERWEAR</h3><span>Shop now &rarr;</span></div><img src="assets/products/jacket-model.png" alt="Outerwear collection"></a>
    <a href="#products"><div><h3>ACCESSORIES</h3><span>Shop now &rarr;</span></div><img src="assets/products/premium-cap.png" alt="Accessories collection"></a>
  </div>

  <!-- ── Collections Product Grid (All Products by Category) ── -->
  <div class="col-product-grid" id="colProductGrid">
    <?php foreach ($products as $p): ?>
      <article class="col-card" data-col-category="<?= e($p['category']) ?>">
        <div class="col-card-img" data-inspect-id="<?= e($p['id']) ?>">
          <img src="assets/products/<?= e($p['image']) ?>"
               alt="<?= e($p['name']) ?>" loading="lazy">
        </div>
        <?php if (!empty($p['colors']) && count($p['colors']) > 1): ?>
          <div class="col-card-swatches">
            <?php foreach ($p['colors'] as $colorName => $colorImg): ?>
              <button type="button"
                      class="col-swatch"
                      data-img="assets/products/<?= e($colorImg) ?>"
                      title="<?= e($colorName) ?>"
                      aria-label="<?= e($colorName) ?>">
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <h4 class="col-card-name"><?= e($p['name']) ?></h4>
        <p class="col-card-price"><?= e($p['price']) ?></p>
        <div class="col-card-action">
          <div class="col-qty-control">
            <button type="button" class="col-qty-btn col-minus">-</button>
            <span class="col-qty-val">0</span>
            <button type="button" class="col-qty-btn col-plus">+</button>
          </div>
          <button type="button"
                  class="col-add-btn"
                  data-col-product-id="<?= e($p['id']) ?>">ADD TO CART</button>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="products section-light" id="products">
  <div class="product-intro">
    <div class="brand-mark">NC</div>
    <p>We believe in quality over everything.<br>Each piece is designed to be timeless,<br>so you can wear confidence every single day.</p>
  </div>
  <div class="product-detail">
    <div class="gallery">
      <div class="thumbs" id="productThumbs">
        <button class="thumb active" data-img="essential-hoodie-black.png" type="button"><img src="assets/products/essential-hoodie-black.png" alt="Black view"></button>
        <button class="thumb" data-img="essential-hoodie-olive.png" type="button"><img src="assets/products/essential-hoodie-olive.png" alt="Olive view"></button>
        <button class="thumb" data-img="essential-hoodie-beige.png" type="button"><img src="assets/products/essential-hoodie-beige.png" alt="Beige view"></button>
        <button class="thumb" data-img="hoodie-detail.png" type="button"><img src="assets/products/hoodie-detail.png" alt="Detail view"></button>
      </div>
      <div class="main-product">
        <img id="mainProduct" src="assets/products/essential-hoodie-black.png" alt="NC Essential Hoodie">
      </div>
    </div>
    <div class="product-info" id="productInfo">
      <p class="eyebrow" id="productEyebrow">NC ESSENTIAL HOODIE</p>
      <h2 id="productPrice">$120.00</h2>
      <p id="productDesc">Premium comfort with a clean, elevated silhouette in signature black.</p>
      
      <label>COLOR 
        <div class="swatches" id="productSwatches">
          <button class="swatch black active" data-color="Black" type="button" aria-label="Black" title="Black"></button>
          <button class="swatch olive" data-color="Olive" type="button" aria-label="Olive" title="Olive"></button>
          <button class="swatch beige" data-color="Beige" type="button" aria-label="Beige" title="Beige"></button>
        </div>
      </label>
      
      <label>SIZE
        <div class="sizes" id="productSizes">
          <button type="button" class="size-btn">S</button>
          <button type="button" class="size-btn active">M</button>
          <button type="button" class="size-btn">L</button>
          <button type="button" class="size-btn">XL</button>
        </div>
      </label>
      
      <div class="quantity">
        <button type="button" data-qty="minus" aria-label="Decrease">-</button>
        <span id="qty">0</span>
        <button type="button" data-qty="plus" aria-label="Increase">+</button>
        <button type="button" class="btn btn-dark add-cart" id="mainAddToCartBtn">ADD TO CART</button>
      </div>
      <button type="button" class="buy-now" id="buyNowBtn">BUY NOW</button>
      
      <div class="accordions">
        <details open><summary>DESCRIPTION</summary><p id="accordionDesc">Premium heavyweight fabric construction with signature NC monogram detailing on the chest.</p></details>
        <details><summary>MATERIAL &amp; CARE</summary><p>Premium cotton fleece. Machine wash cold with like colors, inside out. Tumble dry low or line dry in shade.</p></details>
        <details><summary>SHIPPING &amp; RETURNS</summary><p>Complimentary express delivery on all orders. Free 30-day returns and exchanges.</p></details>
      </div>
    </div>
  </div>

  <div class="recommendations">
    <h3>YOU MAY ALSO LIKE</h3>
    <div class="recommend-grid">
      <?php foreach (array_slice($products, 6) as $p): ?>
        <article class="arrival-card" data-product-id="<?= e($p['id']) ?>" data-category="<?= e($p['category']) ?>">
          <div class="recommend-img" data-inspect-id="<?= e($p['id']) ?>">
            <img src="assets/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
          </div>
          <h4 class="product-card-title" data-inspect-id="<?= e($p['id']) ?>"><?= e($p['name']) ?></h4>

        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="lookbook section-light" id="lookbook">
  <div class="lookbook-image"><img src="assets/products/hero-model.png" alt="Nicolai lookbook"></div>
  <div>
    <p class="eyebrow">LOOKBOOK</p>
    <h2>Confidence<br>in every detail.</h2>
    <p>Explore a visual language built around minimal silhouettes, premium materials, and the Nicolai monogram.</p>
    <a class="btn btn-dark" href="#shop">EXPLORE SHOP</a>
  </div>
</section>

<section class="contact section-dark" id="contact">
  <div class="contact-copy">
    <p class="eyebrow">CONTACT US</p>
    <h2>We'd love to hear from you.</h2>
    <p>Send us a message and we'll get back to you soon.</p>
    <div class="contact-lines">
      <p><b>Email</b><?= e(CONTACT_EMAIL) ?></p>
      <p><b>Phone</b><?= e(CONTACT_PHONE) ?></p>
      <p><b>Location</b><?= e(CONTACT_LOCATION) ?></p>
    </div>
  </div>
  <form class="contact-form" onsubmit="return submitContact(event)">
    <label>Name<input required name="name"></label>
    <label>Email<input required type="email" name="email"></label>
    <label>Subject<input required name="subject"></label>
    <label>Message<textarea required name="message" rows="6"></textarea></label>
    <button class="btn btn-gold" type="submit">SEND MESSAGE</button>
    <p id="contactStatus" class="form-status" aria-live="polite"></p>
  </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>