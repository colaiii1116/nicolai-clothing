<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../function.php';
if (!is_admin()) { redirect('/admin/index.php'); }

$pdo = get_db();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($action === 'delete') {
            $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
            flash('msg', 'Review deleted.');
        } elseif ($action === 'approve') {
            $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")->execute([$id]);
            flash('msg', 'Review approved.');
        } elseif ($action === 'hide') {
            $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE id = ?")->execute([$id]);
            flash('msg', 'Review hidden.');
        }
    }
    redirect('/admin/reviews.php');
}

$reviews = $pdo->query(
    "SELECT * FROM reviews ORDER BY created_at DESC"
)->fetchAll();

$flashMsg = flash('msg');
$activeTab = 'reviews';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-header">
  <h1>Reviews</h1>
  <p><?= count($reviews) ?> total review(s)</p>
</div>

<?php if ($flashMsg): ?>
  <div class="admin-flash"><?= e($flashMsg) ?></div>
<?php endif; ?>

<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Rating</th>
        <th>Review</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($reviews as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><strong><?= e($r['name']) ?></strong></td>
        <td style="color:var(--gold);letter-spacing:2px;"><?= str_repeat('★', (int)$r['rating']) ?></td>
        <td style="max-width:280px;font-size:0.82rem;color:#b0a89e;"><?= e(mb_strimwidth($r['review_text'], 0, 100, '…')) ?></td>
        <td style="font-size:0.75rem;color:#666;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
        <td>
          <?php if ($r['is_approved']): ?>
            <span style="color:#7ec89a;font-size:0.72rem;letter-spacing:.1em;">VISIBLE</span>
          <?php else: ?>
            <span style="color:#e07070;font-size:0.72rem;letter-spacing:.1em;">HIDDEN</span>
          <?php endif; ?>
        </td>
        <td>
          <div style="display:flex;gap:8px;align-items:center;">
            <?php if ($r['is_approved']): ?>
              <form method="post">
                <input type="hidden" name="action" value="hide">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-sm" style="background:#333;">HIDE</button>
              </form>
            <?php else: ?>
              <form method="post">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-sm admin-btn-gold">APPROVE</button>
              </form>
            <?php endif; ?>
            <form method="post" onsubmit="return confirm('Delete this review?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">DELETE</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reviews): ?>
      <tr><td colspan="7" style="text-align:center;color:#555;padding:40px 0;">No reviews yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php ?>
</main>
</body>
</html> ?>