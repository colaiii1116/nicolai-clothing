/**
 * Nicolai Clothing — Fully Functional Interactive UI, Dynamic Catalog & Shopping Cart System
 */

const CART_STORAGE_KEY = 'nicolai_cart_v1';

// Active showcase state
let activeShowcaseProduct = null;
let activeShowcaseColor   = 'Black';
let activeShowcaseSize    = 'M';

/* ============================================================
   Cart Helpers
   ============================================================ */
function getCart() {
  try {
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch (e) { return []; }
}

function saveCart(cart) {
  try {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
  } catch (e) {}
  updateCartUI();
}

async function addToCart(item) {
  if (!requireLogin('add items to your cart')) return;
  const cart = getCart();
  const idx  = cart.findIndex(c => c.key === item.key);
  if (idx > -1) {
    cart[idx].qty += item.qty;
  } else {
    cart.push(item);
  }
  saveCart(cart);
  openCart();
  try {
    await fetch('api/cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add', productId: item.id, color: item.color, size: item.size, qty: item.qty })
    });
  } catch (e) {}
}

async function updateCartQty(key, delta) {
  let cart = getCart();
  const item = cart.find(c => c.key === key);
  let newQty = 0;
  if (item) {
    item.qty += delta;
    newQty = item.qty;
    if (item.qty <= 0) cart = cart.filter(c => c.key !== key);
  }
  saveCart(cart);
  try {
    await fetch('api/cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update', key, qty: newQty })
    });
  } catch (e) {}
}

async function removeCartItem(key) {
  let cart = getCart().filter(c => c.key !== key);
  saveCart(cart);
  try {
    await fetch('api/cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'remove', key })
    });
  } catch (e) {}
}

/* ============================================================
   Cart Drawer
   ============================================================ */
function openCart() {
  const drawer  = document.querySelector('#cartDrawer');
  const overlay = document.querySelector('#cartOverlay');
  if (drawer && overlay) {
    drawer.classList.add('open');
    overlay.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }
}

function closeCart() {
  const drawer  = document.querySelector('#cartDrawer');
  const overlay = document.querySelector('#cartOverlay');
  if (drawer && overlay) {
    drawer.classList.remove('open');
    overlay.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function updateCartUI() {
  const cart       = getCart();
  const totalCount = cart.reduce((s, i) => s + i.qty, 0);
  const subtotal   = cart.reduce((s, i) => s + i.price * i.qty, 0);

  const badge = document.querySelector('#cartBadge');
  if (badge) {
    badge.textContent = totalCount;
    badge.classList.remove('bump');
    void badge.offsetWidth;
    badge.classList.add('bump');
  }
  const countHeader = document.querySelector('#cartCountHeader');
  if (countHeader) countHeader.textContent = totalCount;
  const subtotalEl = document.querySelector('#cartSubtotal');
  if (subtotalEl) subtotalEl.textContent = `$${subtotal.toFixed(2)}`;
  const totalEl = document.querySelector('#cartTotal');
  if (totalEl) totalEl.textContent = `$${subtotal.toFixed(2)}`;

  const body = document.querySelector('#cartBody');
  if (!body) return;

  if (cart.length === 0) {
    body.innerHTML = `
      <div class="cart-empty">
        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
          style="margin-bottom:12px;opacity:0.6;">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        <p>YOUR SHOPPING BAG IS EMPTY</p>
        <small>Discover our latest collection and timeless pieces.</small>
      </div>`;
    return;
  }

  body.innerHTML = cart.map(item => `
    <div class="cart-item" data-key="${escapeHtml(item.key)}">
      <div class="cart-item-img">
        <img src="assets/products/${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
      </div>
      <div class="cart-item-details">
        <div class="cart-item-top">
          <h4 class="cart-item-title">${escapeHtml(item.name)}</h4>
          <button class="cart-item-remove" data-remove="${escapeHtml(item.key)}" aria-label="Remove">&times;</button>
        </div>
        <div class="cart-item-variant">
          <span>Color: ${escapeHtml(item.color)}</span>
          <span>Size: ${escapeHtml(item.size)}</span>
        </div>
        <div class="cart-item-bottom">
          <div class="cart-item-qty">
            <button class="cart-qty-dec" data-key="${escapeHtml(item.key)}">−</button>
            <span>${item.qty}</span>
            <button class="cart-qty-inc" data-key="${escapeHtml(item.key)}">+</button>
          </div>
          <span class="cart-item-price">$${(item.price * item.qty).toFixed(2)}</span>
        </div>
      </div>
    </div>
  `).join('');

  body.querySelectorAll('.cart-item-remove').forEach(btn => {
    btn.addEventListener('click', () => removeCartItem(btn.dataset.remove));
  });
  body.querySelectorAll('.cart-qty-inc').forEach(btn => {
    btn.addEventListener('click', () => updateCartQty(btn.dataset.key, 1));
  });
  body.querySelectorAll('.cart-qty-dec').forEach(btn => {
    btn.addEventListener('click', () => updateCartQty(btn.dataset.key, -1));
  });
}

/* ============================================================
   Showcase — set main image helper
   ============================================================ */
function setShowcaseImage(imgFilename) {
  // The main product image uses id="mainProduct"
  const mainImg = document.querySelector('#mainProduct');
  if (mainImg && imgFilename) {
    mainImg.src = `assets/products/${imgFilename}`;
  }
}

function setThumbActive(imgFilename) {
  // Highlight the thumbnail that matches this image
  document.querySelectorAll('#productThumbs .thumb').forEach(thumb => {
    const match = thumb.dataset.img === imgFilename;
    thumb.classList.toggle('active', match);
  });
}

/* ============================================================
   Showcase Product Setup
   Called when a product card is clicked or on page load
   ============================================================ */
function setupShowcaseProduct(prod, initialColor, scroll = false) {
  activeShowcaseProduct = prod;
  activeShowcaseColor   = initialColor || Object.keys(prod.colors || {})[0] || prod.color || 'Black';
  activeShowcaseSize    = 'M';

  // ── Update text fields ────────────────────────────────────
  // #productEyebrow = product name label (top of the panel)
  const eyebrow = document.querySelector('#productEyebrow');
  if (eyebrow) eyebrow.textContent = (prod.base_name || prod.name || '').toUpperCase();

  // #productPrice = price heading
  const priceEl = document.querySelector('#productPrice');
  if (priceEl) priceEl.textContent = prod.price;

  // #productDesc = short description paragraph
  const descEl = document.querySelector('#productDesc');
  if (descEl) descEl.textContent = prod.desc || prod.description || '';

  // #accordionDesc = description in accordion
  const accEl = document.querySelector('#accordionDesc');
  if (accEl) accEl.textContent = prod.desc || prod.description || '';

  // ── Pick color image ──────────────────────────────────────
  const colorImg = (prod.colors && prod.colors[activeShowcaseColor])
    ? prod.colors[activeShowcaseColor]
    : prod.image;

  // ── Update main product image ─────────────────────────────
  setShowcaseImage(colorImg);

  // ── Update thumbnails to reflect all color variants ───────
  updateShowcaseThumbs(prod, activeShowcaseColor);

  // ── Wire color swatches ───────────────────────────────────
  document.querySelectorAll('#productSwatches .swatch').forEach(swatch => {
    const color = swatch.dataset.color;
    // Mark active swatch
    swatch.classList.toggle('active', color === activeShowcaseColor);

    // Replace old listener with a fresh one (clone trick)
    const fresh = swatch.cloneNode(true);
    swatch.parentNode.replaceChild(fresh, swatch);
    fresh.classList.toggle('active', color === activeShowcaseColor);

    fresh.addEventListener('click', () => {
      // Deactivate all swatches
      document.querySelectorAll('#productSwatches .swatch')
        .forEach(s => s.classList.remove('active'));
      fresh.classList.add('active');
      activeShowcaseColor = color;

      // Get image for this color
      const img = (prod.colors && prod.colors[color]) ? prod.colors[color] : prod.image;
      setShowcaseImage(img);
      updateShowcaseThumbs(prod, color);
      setThumbActive(img);
    });
  });

  // ── Wire thumbnail clicks ─────────────────────────────────
  wireThumbClicks();

  // ── Reset qty ─────────────────────────────────────────────
  const qtyEl = document.querySelector('#qty');
  if (qtyEl) qtyEl.textContent = '0';

  // ── Reset size buttons ────────────────────────────────────
  document.querySelectorAll('#productSizes .size-btn').forEach(btn => {
    btn.classList.toggle('active', btn.textContent.trim() === 'M');
  });
  activeShowcaseSize = 'M';

  // ── Smooth scroll to showcase (only when triggered by user click) ───
  if (scroll) {
    const section = document.querySelector('#products');
    if (section) section.scrollIntoView({ behavior: 'smooth' });
  }
}

/* ============================================================
   Update thumbnail strip to show all color variants of a product
   ============================================================ */
function updateShowcaseThumbs(prod, selectedColor) {
  const thumbsContainer = document.querySelector('#productThumbs');
  if (!thumbsContainer) return;

  const colors = prod.colors || { [prod.color || 'Black']: prod.image };

  // Build array of images: selected color first, then others, then detail
  const imgs = [];

  // 1. Put selected color image first
  if (colors[selectedColor]) imgs.push(colors[selectedColor]);

  // 2. Add other color images
  Object.entries(colors).forEach(([clr, img]) => {
    if (clr !== selectedColor && img && !imgs.includes(img)) imgs.push(img);
  });

  // 3. Add product base image if not already there
  if (prod.image && !imgs.includes(prod.image)) imgs.push(prod.image);

  // ── Rebuild thumbnails ──────────────────────────────────
  thumbsContainer.innerHTML = imgs.slice(0, 4).map((img, i) => `
    <button class="thumb${i === 0 ? ' active' : ''}" data-img="${escapeHtml(img)}" type="button">
      <img src="assets/products/${escapeHtml(img)}" alt="View ${i + 1}">
    </button>
  `).join('');

  // Set main image to the first (selected color) image
  setShowcaseImage(imgs[0] || prod.image);

  // Wire the new thumbnails
  wireThumbClicks();
}

/* ============================================================
   Wire thumbnail click events
   ============================================================ */
function wireThumbClicks() {
  document.querySelectorAll('#productThumbs .thumb').forEach(thumb => {
    const fresh = thumb.cloneNode(true);
    thumb.parentNode.replaceChild(fresh, thumb);
    fresh.addEventListener('click', () => {
      document.querySelectorAll('#productThumbs .thumb')
        .forEach(t => t.classList.remove('active'));
      fresh.classList.add('active');
      setShowcaseImage(fresh.dataset.img);
    });
  });
}

/* ============================================================
   DOM Ready
   ============================================================ */

/* ============================================================
   Auth Guard — block cart actions for guests
   ============================================================ */
function requireLogin(actionName) {
  if (window.NICOLAI_LOGGED_IN === true) return true;
  const loginUrl = window.NICOLAI_LOGIN_URL || 'login.php';

  // Show toast message
  showAuthToast('Please log in to ' + actionName + '.', loginUrl);
  return false;
}

function showAuthToast(msg, loginUrl) {
  // Remove any existing toast
  const existing = document.querySelector('#nicolaiAuthToast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'nicolaiAuthToast';
  toast.style.cssText = `
    position: fixed;
    top: 80px;
    left: 50%;
    transform: translateX(-50%);
    background: #1a1714;
    border: 1px solid rgba(193,138,53,0.5);
    color: #f0ebe4;
    padding: 16px 24px;
    border-radius: 4px;
    z-index: 99999;
    font-size: 13px;
    letter-spacing: 0.05em;
    text-align: center;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    min-width: 280px;
    max-width: 90vw;
  `;
  toast.innerHTML = `
    <p style="margin:0 0 12px;">${msg}</p>
    <a href="${loginUrl}"
       style="display:inline-block;background:var(--gold,#c18a35);color:#fff;
              padding:8px 20px;text-decoration:none;font-size:11px;
              letter-spacing:0.15em;font-weight:600;border-radius:2px;">
      LOG IN
    </a>
    <button onclick="this.closest('#nicolaiAuthToast').remove()"
            style="display:block;margin:10px auto 0;background:none;border:none;
                   color:#888;cursor:pointer;font-size:11px;letter-spacing:0.1em;">
      DISMISS
    </button>
  `;
  document.body.appendChild(toast);
  setTimeout(() => { if (toast.parentNode) toast.remove(); }, 6000);
}


/* ============================================================
   Contact Form — submitContact()
   ============================================================ */
function submitContact(e) {
  e.preventDefault();

  const form     = e.target;
  const statusEl = document.querySelector('#contactStatus');
  const btn      = form.querySelector('button[type="submit"]');

  const name    = form.querySelector('[name="name"]')?.value.trim()    || '';
  const email   = form.querySelector('[name="email"]')?.value.trim()   || '';
  const subject = form.querySelector('[name="subject"]')?.value.trim() || '';
  const message = form.querySelector('[name="message"]')?.value.trim() || '';

  if (!name || !email || !subject || !message) {
    showContactStatus(statusEl, 'Please fill in all fields.', false);
    return false;
  }

  // Loading state
  const origText = btn.textContent;
  btn.textContent = 'SENDING...';
  btn.disabled    = true;

  fetch('api/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name, email, subject, message })
  })
    .then(r => r.json())
    .then(data => {
      showContactStatus(statusEl, data.message, data.success);
      if (data.success) form.reset();
    })
    .catch(() => {
      showContactStatus(statusEl, 'Something went wrong. Please try again.', false);
    })
    .finally(() => {
      btn.textContent = origText;
      btn.disabled    = false;
    });

  return false;
}

function showContactStatus(el, msg, success) {
  if (!el) return;
  el.textContent = msg;
  el.style.cssText = `
    margin-top: 10px;
    padding: 10px 14px;
    font-size: 12px;
    letter-spacing: 0.06em;
    color: ${success ? '#a8c97f' : '#e07b6a'};
    border-left: 2px solid ${success ? '#a8c97f' : '#e07b6a'};
    display: block;
  `;
  // Auto-clear after 6s
  setTimeout(() => { if (el) el.textContent = ''; }, 6000);
}

document.addEventListener('DOMContentLoaded', () => {

  // Nav links with data-scroll — smooth scroll when on same page,
  // or navigate to href (e.g. index.php#shop) when on another page
  document.querySelectorAll('[data-scroll]').forEach(link => {
    link.addEventListener('click', e => {
      const target = document.getElementById(link.dataset.scroll);
      if (target) {
        // Section exists on this page — smooth scroll, don't navigate
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
      // Section not found — let href navigate normally (cross-page)
    });
  });

  // 1. Cart Drawer Toggle
  document.querySelector('#cartToggleBtn')?.addEventListener('click', openCart);
  document.querySelector('#cartCloseBtn')?.addEventListener('click', closeCart);
  document.querySelector('#cartOverlay')?.addEventListener('click', closeCart);
  document.querySelector('#cartCheckoutBtn')?.addEventListener('click', () => {
    closeCart(); window.location.href = 'checkout.php';
  });
  document.querySelector('#cartContinueBtn')?.addEventListener('click', closeCart);

  // 2. Mobile Menu Toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const mainNav    = document.querySelector('.main-nav');
  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', () => {
      const open = mainNav.classList.toggle('open');
      menuToggle.setAttribute('aria-expanded', String(open));
    });
  }

  // 3. Category Filter Tabs
  const tabBtns = document.querySelectorAll('.tabs button');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const category = btn.textContent.trim().toUpperCase();
      document.querySelectorAll('.arrival-card').forEach(card => {
        const cardCat = (card.dataset.category || '').toUpperCase();
        card.style.display = (category === 'ALL' || cardCat === category) ? '' : 'none';
      });
      document.querySelector('#collections')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // 4. Collection Grid Links
  document.querySelectorAll('.collection-grid a').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const title = a.querySelector('h3')?.textContent.trim().toUpperCase();
      if (title) {
        const tab = Array.from(tabBtns).find(b => b.textContent.trim().toUpperCase() === title);
        if (tab) { tab.click(); return; }
      }
      document.querySelector('#collections')?.scrollIntoView({ behavior: 'smooth' });
    });
  });

  // 5. Load Products & Init Showcase
  const products = window.NICOLAI_PRODUCTS || [];
  if (products.length > 0) {
    setupShowcaseProduct(products[0], 'Black');
  }

  // 6. Size Selection — Showcase
  document.querySelectorAll('#productSizes .size-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#productSizes .size-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeShowcaseSize = btn.textContent.trim();
    });
  });

  // 7. Quantity Stepper — Showcase (default 0, min 0)
  document.querySelectorAll('.quantity [data-qty]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      const output = document.querySelector('#qty');
      if (!output) return;
      let value = parseInt(output.textContent.trim(), 10) || 0;
      if (btn.dataset.qty === 'plus')  value = Math.min(99, value + 1);
      if (btn.dataset.qty === 'minus') value = Math.max(0,  value - 1);
      output.textContent = value;
    });
  });

  // 8. ADD TO CART — Showcase
  const mainAddBtn = document.querySelector('#mainAddToCartBtn');
  if (mainAddBtn) {
    mainAddBtn.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      if (!activeShowcaseProduct) return;
      const output = document.querySelector('#qty');
      let cur = parseInt(output?.textContent.trim() || '0', 10) || 0;
      const toAdd = cur === 0 ? 1 : cur;
      if (cur === 0 && output) output.textContent = 1;

      const color = activeShowcaseColor;
      const size  = activeShowcaseSize;
      const img   = (activeShowcaseProduct.colors && activeShowcaseProduct.colors[color])
        ? activeShowcaseProduct.colors[color]
        : activeShowcaseProduct.image;

      addToCart({
        key:   `${activeShowcaseProduct.id}__${color}__${size}`,
        id:    activeShowcaseProduct.id,
        name:  activeShowcaseProduct.base_name || activeShowcaseProduct.name,
        price: activeShowcaseProduct.price_num,
        image: img, color, size, qty: toAdd
      });

      mainAddBtn.textContent = 'ADDED ✓';
      setTimeout(() => { mainAddBtn.textContent = 'ADD TO CART'; }, 1400);
    });
  }

  // 9. BUY NOW — Showcase
  document.querySelector('#buyNowBtn')?.addEventListener('click', () => {
    if (mainAddBtn) mainAddBtn.click();
    setTimeout(() => { window.location.href = 'checkout.php'; }, 400);
  });

  // 10. Product Cards — qty steppers + ADD TO CART + click-to-inspect in showcase
  document.querySelectorAll('.arrival-card').forEach(card => {
    const prodId = card.dataset.productId;
    // Compare as strings to handle int vs string IDs from PHP
    const prod   = products.find(p => String(p.id) === String(prodId));
    if (!prod) return;

    const minusBtn = card.querySelector('.card-qty-btn.minus');
    const plusBtn  = card.querySelector('.card-qty-btn.plus');
    const qtyVal   = card.querySelector('.card-qty-val');
    const addBtn   = card.querySelector('.card-add-btn');

    // Qty steppers (default 0, min 0)
    if (minusBtn && plusBtn && qtyVal) {
      minusBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        let cur = parseInt(qtyVal.textContent.trim(), 10) || 0;
        qtyVal.textContent = Math.max(0, cur - 1);
      });
      plusBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        let cur = parseInt(qtyVal.textContent.trim(), 10) || 0;
        qtyVal.textContent = Math.min(99, cur + 1);
      });
    }

    // ADD TO CART on card
    if (addBtn) {
      addBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        let cur   = parseInt(qtyVal?.textContent.trim() || '0', 10) || 0;
        const toAdd = cur === 0 ? 1 : cur;
        if (cur === 0 && qtyVal) qtyVal.textContent = 1;

        addToCart({
          key:   `${prod.id}__${prod.color || 'Black'}__M`,
          id:    prod.id,
          name:  prod.name,
          price: prod.price_num,
          image: prod.image,
          color: prod.color || 'Black',
          size:  'M',
          qty:   toAdd
        });

        addBtn.classList.add('added');
        addBtn.textContent = 'ADDED ✓';
        setTimeout(() => {
          addBtn.classList.remove('added');
          addBtn.textContent = 'ADD TO CART';
        }, 1400);
      });
    }

    // Click image/name → load this product into the showcase
    card.querySelectorAll('[data-inspect-id]').forEach(clickable => {
      clickable.addEventListener('click', () => {
        setupShowcaseProduct(prod, prod.color || 'Black', true);
      });
    });
  });


  // ── Collections Grid: Tab Filtering (separate from New Drops) ────
  const colTabBtns = document.querySelectorAll('.tabs button');
  colTabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      // Mark active tab
      colTabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const category = btn.textContent.trim().toUpperCase();

      // Filter the Collections grid only — New Drops is NOT touched
      document.querySelectorAll('#colProductGrid .col-card').forEach(card => {
        const cardCat = (card.dataset.colCategory || '').toUpperCase();
        const show    = category === 'ALL' || cardCat === category;
        card.style.display  = show ? '' : 'none';
        card.style.opacity  = show ? '1' : '0';
      });
    });
  });

  // ── Collections Grid: Color Swatch Swap ──────────────────────────
  document.querySelectorAll('#colProductGrid .col-card').forEach(card => {
    const img      = card.querySelector('.col-card-img img');
    const swatches = card.querySelectorAll('.col-swatch');

    // Set first swatch active
    if (swatches.length > 0) swatches[0].classList.add('active');

    swatches.forEach(sw => {
      sw.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        swatches.forEach(s => s.classList.remove('active'));
        sw.classList.add('active');
        if (img && sw.dataset.img) img.src = sw.dataset.img;
      });
    });

    // ── Collections Grid: Qty Steppers ────────────────────────────
    const minusBtn = card.querySelector('.col-minus');
    const plusBtn  = card.querySelector('.col-plus');
    const qtyVal   = card.querySelector('.col-qty-val');

    if (minusBtn && plusBtn && qtyVal) {
      minusBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        let cur = parseInt(qtyVal.textContent, 10) || 0;
        qtyVal.textContent = Math.max(0, cur - 1);
      });
      plusBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        let cur = parseInt(qtyVal.textContent, 10) || 0;
        qtyVal.textContent = Math.min(99, cur + 1);
      });
    }

    // ── Collections Grid: ADD TO CART ─────────────────────────────
    const addBtn = card.querySelector('.col-add-btn');
    if (addBtn) {
      addBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        if (!requireLogin('add items to your cart')) return;

        const prodId = addBtn.dataset.colProductId;
        const prod   = (window.NICOLAI_PRODUCTS || []).find(p => String(p.id) === String(prodId));
        if (!prod) return;

        let cur    = parseInt(qtyVal?.textContent || '0', 10) || 0;
        const toAdd = cur === 0 ? 1 : cur;
        if (cur === 0 && qtyVal) qtyVal.textContent = 1;

        // Get currently selected swatch image
        const activeSwatch = card.querySelector('.col-swatch.active');
        const swColor = activeSwatch ? activeSwatch.title : (prod.color || 'Black');
        const swImg   = (prod.colors && prod.colors[swColor]) ? prod.colors[swColor] : prod.image;

        addToCart({
          key:   `${prod.id}__${swColor}__M`,
          id:    prod.id,
          name:  prod.base_name || prod.name,
          price: prod.price_num,
          image: swImg,
          color: swColor,
          size:  'M',
          qty:   toAdd
        });

        addBtn.classList.add('added');
        addBtn.textContent = 'ADDED ✓';
        setTimeout(() => {
          addBtn.classList.remove('added');
          addBtn.textContent = 'ADD TO CART';
        }, 1400);
      });
    }

    // ── Collections Grid: Click image → load in Showcase ──────────
    const cardImg = card.querySelector('[data-inspect-id]');
    if (cardImg) {
      cardImg.addEventListener('click', () => {
        const prodId = cardImg.dataset.inspectId;
        const prod   = (window.NICOLAI_PRODUCTS || []).find(p => String(p.id) === String(prodId));
        if (prod) {
          setupShowcaseProduct(prod, prod.color || 'Black', true);
        }
      });
    }
  });

  // 11. Newsletter Form
  const subscribeForm = document.querySelector('.subscribe');
  if (subscribeForm) {
    subscribeForm.addEventListener('submit', async e => {
      e.preventDefault();
      const input = subscribeForm.querySelector('input[type="email"]');
      const email = input?.value.trim() || '';
      if (!email) return;
      try {
        const res  = await fetch('api/subscribe.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email })
        });
        const json = await res.json();
        alert(json.message || 'Thank you for subscribing!');
        if (json.success && input) input.value = '';
      } catch (err) {
        alert('Subscription failed. Please try again.');
      }
    });
  }

  // 12. Sync Cart with Server on Load
  fetch('api/cart.php')
    .then(r => r.json())
    .then(data => {
      if (data.success && Array.isArray(data.items) && data.items.length > 0) {
        saveCart(data.items);
      } else {
        updateCartUI();
      }
    })
    .catch(() => updateCartUI());
});
/* ══════════════════════════════════════════════════════════════
   REVIEWS — load, render, submit
   ══════════════════════════════════════════════════════════════ */
(function initReviews() {
  const track    = document.getElementById('reviewsTrack');
  const modal    = document.getElementById('reviewModal');
  const openBtn  = document.getElementById('openReviewModalBtn');
  const closeBtn = document.getElementById('closeReviewModal');
  const form     = document.getElementById('reviewForm');
  const msgEl    = document.getElementById('reviewFormMsg');
  const ratingIn = document.getElementById('reviewRating');
  const stars    = document.querySelectorAll('#starPicker .star');

  if (!track) return; // not on index page

  // ── Helpers ───────────────────────────────────────────────
  function starsHtml(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
  }
  function initials(name) {
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
  }
  function timeAgo(dateStr) {
    const diff = (Date.now() - new Date(dateStr)) / 1000;
    if (diff < 86400)   return 'Today';
    if (diff < 604800)  return Math.floor(diff / 86400) + 'd ago';
    if (diff < 2592000) return Math.floor(diff / 604800) + 'w ago';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
  }

  function buildCard(r) {
    const card = document.createElement('div');
    card.className = 'review-card';
    card.innerHTML = `
      <div class="review-stars">${starsHtml(Number(r.rating))}</div>
      <p class="review-text">"${r.review_text}"</p>
      <div class="review-author">
        <div class="review-avatar">${initials(r.name)}</div>
        <div>
          <div class="review-name">${r.name}</div>
          <div class="review-date">${timeAgo(r.created_at)}</div>
        </div>
      </div>`;
    return card;
  }

  // ── Load reviews ──────────────────────────────────────────
  async function loadReviews() {
    try {
      const res = await fetch('api/reviews.php');
      const data = await res.json();
      if (!data.success || !data.reviews.length) return;

      track.innerHTML = '';
      // Duplicate for seamless infinite scroll
      const all = [...data.reviews, ...data.reviews];
      all.forEach(r => track.appendChild(buildCard(r)));
    } catch (e) {
      console.warn('Reviews load failed', e);
    }
  }
  loadReviews();

  // ── Modal open/close ──────────────────────────────────────
  function openModal()  { modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); }
  function closeModal() { modal.classList.remove('open'); modal.setAttribute('aria-hidden','true'); }

  if (openBtn)  openBtn.addEventListener('click', openModal);
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (modal)    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // ── Star picker ───────────────────────────────────────────
  let selectedRating = 5;
  stars.forEach(star => {
    star.classList.toggle('active', Number(star.dataset.val) <= 5);
    star.addEventListener('mouseenter', () => {
      stars.forEach(s => s.classList.toggle('hover', Number(s.dataset.val) <= Number(star.dataset.val)));
    });
    star.addEventListener('mouseleave', () => {
      stars.forEach(s => s.classList.remove('hover'));
    });
    star.addEventListener('click', () => {
      selectedRating = Number(star.dataset.val);
      ratingIn.value = selectedRating;
      stars.forEach(s => s.classList.toggle('active', Number(s.dataset.val) <= selectedRating));
    });
  });

  // ── Submit review ─────────────────────────────────────────
  if (form) {
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const name   = document.getElementById('reviewName').value.trim();
      const review = document.getElementById('reviewText').value.trim();
      const rating = Number(ratingIn.value) || 5;
      const btn    = document.getElementById('submitReviewBtn');

      msgEl.className = 'review-form-msg';
      msgEl.textContent = '';

      if (!name || !review) {
        msgEl.className = 'review-form-msg error';
        msgEl.textContent = 'Please fill in your name and review.';
        return;
      }

      btn.disabled = true;
      btn.textContent = 'SUBMITTING…';

      try {
        const res = await fetch('api/reviews.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, rating, review })
        });
        const data = await res.json();

        if (data.success) {
          msgEl.className = 'review-form-msg success';
          msgEl.textContent = '✓ Thank you! Your review has been posted.';
          form.reset();
          stars.forEach(s => s.classList.toggle('active', Number(s.dataset.val) <= 5));
          selectedRating = 5;
          ratingIn.value = 5;
          // Prepend the new card to track
          if (data.review) {
            const newCard = buildCard(data.review);
            track.prepend(newCard);
          }
          setTimeout(closeModal, 1800);
        } else {
          msgEl.className = 'review-form-msg error';
          msgEl.textContent = (data.errors || ['Something went wrong.']).join(' ');
        }
      } catch (err) {
        msgEl.className = 'review-form-msg error';
        msgEl.textContent = 'Network error. Please try again.';
      } finally {
        btn.disabled = false;
        btn.textContent = 'SUBMIT REVIEW';
      }
    });
  }
})();