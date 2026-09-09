<?php
declare(strict_types=1);

$adminTitle = 'Product Catalog Management';
$activeTab = 'products';
require __DIR__ . '/includes/admin_header.php';

$pdo = get_db();
$errors = [];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Session token invalid. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        // Add Product
        if ($action === 'add_product') {
            $name = trim((string)($_POST['name'] ?? ''));
            $baseName = trim((string)($_POST['base_name'] ?? $name));
            $category = strtoupper(trim((string)($_POST['category'] ?? 'TOPS')));
            $price = (float)($_POST['price'] ?? 100.0);
            $stock = (int)($_POST['stock'] ?? 50);
            $image = trim((string)($_POST['image'] ?? 'classic-tee.png'));
            $color = trim((string)($_POST['color'] ?? 'Black'));
            $desc = trim((string)($_POST['description'] ?? ''));

            if ($name === '') $errors[] = 'Product name is required.';
            if ($price <= 0) $errors[] = 'Price must be greater than zero.';

            if (empty($errors)) {
                $id = 'nc-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                $stmtCheck = $pdo->prepare("SELECT `id` FROM `products` WHERE `id` = ?");
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetch()) {
                    $id .= '-' . bin2hex(random_bytes(2));
                }

                $colorsJson = json_encode([$color => $image], JSON_UNESCAPED_SLASHES);
                $sizesJson = json_encode(['S', 'M', 'L', 'XL']);

                $stmt = $pdo->prepare("INSERT INTO `products` 
                    (`id`, `name`, `base_name`, `category`, `price`, `image`, `color`, `colors_json`, `sizes_json`, `description`, `stock_quantity`, `is_active`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$id, $name, $baseName, $category, $price, $image, $color, $colorsJson, $sizesJson, $desc, $stock]);

                flash('success', "Product \"{$name}\" added successfully!");
                redirect('/admin/products.php');
            }
        }

        // Edit Product
        if ($action === 'edit_product') {
            $id = trim((string)($_POST['product_id'] ?? ''));
            $price = (float)($_POST['price'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $desc = trim((string)($_POST['description'] ?? ''));

            if ($id && $price > 0) {
                $stmt = $pdo->prepare("UPDATE `products` SET `price` = ?, `stock_quantity` = ?, `is_active` = ?, `description` = ? WHERE `id` = ?");
                $stmt->execute([$price, $stock, $isActive, $desc, $id]);
                flash('success', "Product #{$id} updated successfully!");
                redirect('/admin/products.php');
            }
        }

        // Toggle Active
        if ($action === 'toggle_active') {
            $id = trim((string)($_POST['product_id'] ?? ''));
            if ($id) {
                $pdo->prepare("UPDATE `products` SET `is_active` = NOT `is_active` WHERE `id` = ?")->execute([$id]);
                flash('success', "Product status updated.");
                redirect('/admin/products.php');
            }
        }

        // Delete Product
        if ($action === 'delete_product') {
            $id = trim((string)($_POST['product_id'] ?? ''));
            if ($id) {
                $pdo->prepare("DELETE FROM `products` WHERE `id` = ?")->execute([$id]);
                flash('success', "Product deleted.");
                redirect('/admin/products.php');
            }
        }
    }
}

// Fetch all products (including inactive)
$stmtProds = $pdo->query("SELECT * FROM `products` ORDER BY `category` ASC, `id` ASC");
$allProducts = $stmtProds->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
  <div>
    <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.2em; font-size: 11px;">CATALOG MANAGEMENT</p>
    <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 38px; margin: 6px 0 0; font-weight: 500;">Products &amp; Inventory</h1>
  </div>
  <a href="#new" class="btn btn-gold btn-sm" style="padding: 10px 22px;">+ CREATE NEW PRODUCT</a>
</div>

<?php if ($flashSuccess = flash('success')): ?>
  <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #6edb8f; padding: 12px 18px; margin-bottom: 25px; font-size: 13px;">
    <?= e($flashSuccess) ?>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div style="background: rgba(220, 60, 60, 0.15); border: 1px solid rgba(220, 60, 60, 0.4); color: #ffb4b4; padding: 12px 18px; margin-bottom: 25px; font-size: 13px;">
    <?php foreach ($errors as $err): ?>
      <p style="margin: 2px 0;">✕ <?= e($err) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Products Table -->
<div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 25px; margin-bottom: 45px;">
  <div style="overflow-x: auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>IMAGE</th>
          <th>PRODUCT NAME &amp; ID</th>
          <th>CATEGORY</th>
          <th>PRICE</th>
          <th>INVENTORY STOCK</th>
          <th>STATUS</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allProducts as $prod): ?>
          <tr>
            <td style="width: 65px;">
              <img src="../assets/products/<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>" style="width: 55px; height: 60px; object-fit: contain; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); padding: 4px;">
            </td>
            <td>
              <strong style="color: #fff; font-size: 13px;"><?= e($prod['name']) ?></strong>
              <small style="display:block; color: #8a8277; font-family: monospace;"><?= e($prod['id']) ?></small>
            </td>
            <td>
              <span style="font-size: 11px; letter-spacing: 0.1em; color: var(--gold);"><?= e($prod['category']) ?></span>
            </td>
            <td style="font-weight: 600; color: #fff;">
              <?= format_price($prod['price']) ?>
            </td>
            <td>
              <strong style="color: <?= $prod['stock_quantity'] < 10 ? '#ff8c8c' : '#6edb8f' ?>; font-size: 13px;">
                <?= $prod['stock_quantity'] ?> units
              </strong>
            </td>
            <td>
              <?php if ($prod['is_active']): ?>
                <span class="badge badge-delivered">ACTIVE</span>
              <?php else: ?>
                <span class="badge badge-cancelled">HIDDEN</span>
              <?php endif; ?>
            </td>
            <td>
              <details style="position: relative;">
                <summary style="cursor: pointer; color: var(--gold); font-size: 11px; font-weight: 600;">EDIT &bull;&bull;&bull;</summary>
                <div style="position: absolute; right: 0; z-index: 10; background: #1f1b18; border: 1px solid rgba(193,138,53,0.3); padding: 18px; width: 280px; box-shadow: 0 10px 30px rgba(0,0,0,0.7); margin-top: 5px;">
                  <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="product_id" value="<?= e($prod['id']) ?>">

                    <label style="display:block; font-size: 10px; color:#aaa; margin-bottom: 8px;">PRICE ($)
                      <input type="number" step="0.01" name="price" value="<?= $prod['price'] ?>" style="width: 100%; box-sizing: border-box; background: #12100e; border: 1px solid #444; color: #fff; padding: 6px; margin-top: 4px;">
                    </label>

                    <label style="display:block; font-size: 10px; color:#aaa; margin-bottom: 8px;">STOCK QUANTITY
                      <input type="number" name="stock" value="<?= $prod['stock_quantity'] ?>" style="width: 100%; box-sizing: border-box; background: #12100e; border: 1px solid #444; color: #fff; padding: 6px; margin-top: 4px;">
                    </label>

                    <label style="display:block; font-size: 10px; color:#aaa; margin-bottom: 8px;">DESCRIPTION
                      <textarea name="description" rows="2" style="width: 100%; box-sizing: border-box; background: #12100e; border: 1px solid #444; color: #fff; padding: 6px; margin-top: 4px;"><?= e($prod['description']) ?></textarea>
                    </label>

                    <label style="display:flex; align-items:center; gap: 8px; font-size: 11px; color:#aaa; margin-bottom: 12px; cursor: pointer;">
                      <input type="checkbox" name="is_active" value="1" <?= $prod['is_active'] ? 'checked' : '' ?>> Visible on Website
                    </label>

                    <button type="submit" class="btn btn-gold btn-sm" style="width: 100%; text-align: center;">Save Changes</button>
                  </form>

                  <form method="post" onsubmit="return confirm('Are you sure you want to delete this product?');" style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="<?= e($prod['id']) ?>">
                    <button type="submit" style="background: none; border: none; color: #ff8c8c; font-size: 10px; cursor: pointer; text-decoration: underline;">Delete Product</button>
                  </form>
                </div>
              </details>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add New Product Section -->
<div id="new" style="background: rgba(30, 27, 24, 0.75); border: 1px solid rgba(193, 138, 53, 0.3); padding: 30px; margin-bottom: 50px;">
  <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; margin-top: 0; margin-bottom: 18px; color: #f6f4ef; border-bottom: 1px solid rgba(193,138,53,0.2); padding-bottom: 10px;">
    Add New Product to Catalog
  </h3>

  <form method="post" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="action" value="add_product">

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">PRODUCT NAME *
      <input type="text" name="name" required placeholder="e.g. NC HEAVYWEIGHT HOODIE - CHARCOAL" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">BASE NAME
      <input type="text" name="base_name" placeholder="e.g. NC HEAVYWEIGHT HOODIE" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">CATEGORY *
      <select name="category" required style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
        <option value="HOODIES">HOODIES</option>
        <option value="TOPS">TOPS</option>
        <option value="OUTERWEAR">OUTERWEAR</option>
        <option value="ACCESSORIES">ACCESSORIES</option>
      </select>
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">PRICE ($ USD) *
      <input type="number" step="0.01" name="price" required value="120.00" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">INITIAL STOCK UNITS *
      <input type="number" name="stock" required value="50" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">IMAGE FILENAME *
      <input type="text" name="image" required value="essential-hoodie-black.png" placeholder="e.g. signature-jacket-black.png" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em;">PRIMARY COLOR *
      <input type="text" name="color" required value="Black" placeholder="Black, Olive, or Beige" style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;">
    </label>

    <label style="font-size: 11px; color: #b8afa3; letter-spacing: 0.05em; grid-column: span 2;">DESCRIPTION
      <textarea name="description" rows="3" placeholder="Crafted with premium materials..." style="width: 100%; box-sizing: border-box; background: rgba(15,13,12,0.8); border: 1px solid #4a433a; color: #fff; padding: 10px; margin-top: 6px;"></textarea>
    </label>

    <div style="grid-column: span 2; margin-top: 10px;">
      <button type="submit" class="btn btn-gold" style="padding: 14px 30px; font-size: 12px; letter-spacing: 0.15em;">ADD PRODUCT TO DATABASE</button>
    </div>
  </form>
</div>

</main>
</body>
</html>
