<?php
declare(strict_types=1);

$adminTitle = 'Customer Inquiries & Subscribers';
$activeTab = 'messages';
require __DIR__ . '/includes/admin_header.php';

$pdo = get_db();

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_msg_status') {
    if (verify_csrf($_POST['csrf_token'] ?? null)) {
        $msgId = (int)($_POST['msg_id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? 'Read'));
        $pdo->prepare("UPDATE `contact_messages` SET `status` = ? WHERE `id` = ?")->execute([$status, $msgId]);
        flash('success', "Message status updated to {$status}.");
        redirect('/admin/messages.php');
    }
}

$messages = $pdo->query("SELECT * FROM `contact_messages` ORDER BY `created_at` DESC")->fetchAll();
$subscribers = $pdo->query("SELECT * FROM `newsletter_subscribers` ORDER BY `created_at` DESC")->fetchAll();
?>

<div style="margin-bottom: 30px;">
  <p class="eyebrow" style="color: var(--gold); letter-spacing: 0.2em; font-size: 11px;">COMMUNICATIONS</p>
  <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 38px; margin: 6px 0 0; font-weight: 500;">Inquiries &amp; Newsletter</h1>
</div>

<?php if ($flashSuccess = flash('success')): ?>
  <div style="background: rgba(110, 219, 143, 0.15); border: 1px solid rgba(110, 219, 143, 0.4); color: #6edb8f; padding: 12px 18px; margin-bottom: 25px; font-size: 13px;">
    <?= e($flashSuccess) ?>
  </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 35px; align-items: start;">
  
  <!-- Contact Form Messages -->
  <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 25px;">
    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-top: 0; margin-bottom: 20px; color: #f6f4ef; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 10px;">
      Customer Inquiries (<?= count($messages) ?>)
    </h3>

    <?php if (empty($messages)): ?>
      <p style="color: #9c9489; text-align: center; padding: 30px;">No messages received yet.</p>
    <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 15px;">
        <?php foreach ($messages as $msg): ?>
          <div style="background: rgba(15,13,12,0.6); border: 1px solid <?= $msg['status'] === 'New' ? 'rgba(193,138,53,0.5)' : 'rgba(255,255,255,0.06)' ?>; padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
              <div>
                <strong style="color: #fff; font-size: 14px;"><?= e($msg['name']) ?></strong>
                <span style="color: #9c9489; font-size: 12px; margin-left: 10px;">&lt;<?= e($msg['email']) ?>&gt;</span>
              </div>
              <span class="badge <?= $msg['status'] === 'New' ? 'badge-pending' : 'badge-delivered' ?>">
                <?= strtoupper(e($msg['status'])) ?>
              </span>
            </div>

            <div style="font-size: 12px; color: var(--gold); font-weight: 500; margin-bottom: 8px;">
              Subject: <?= e($msg['subject']) ?>
            </div>

            <p style="font-size: 12px; color: #cfc6b7; line-height: 1.6; margin: 0 0 12px; white-space: pre-wrap;"><?= e($msg['message']) ?></p>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.04); padding-top: 8px; font-size: 11px; color: #8a8277;">
              <span>Received: <?= date('M j, Y, g:i a', strtotime($msg['created_at'])) ?></span>
              <form method="post" style="margin: 0;">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update_msg_status">
                <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                <input type="hidden" name="status" value="<?= $msg['status'] === 'New' ? 'Read' : 'New' ?>">
                <button type="submit" style="background: none; border: none; color: var(--gold); cursor: pointer; text-decoration: underline; font-size: 11px;">
                  Mark as <?= $msg['status'] === 'New' ? 'Read' : 'New' ?>
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Newsletter Subscribers -->
  <div style="background: rgba(30, 27, 24, 0.7); border: 1px solid rgba(193, 138, 53, 0.22); padding: 25px;">
    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-top: 0; margin-bottom: 20px; color: #f6f4ef; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 10px;">
      Subscribers (<?= count($subscribers) ?>)
    </h3>

    <?php if (empty($subscribers)): ?>
      <p style="color: #9c9489; text-align: center; padding: 20px;">No subscribers yet.</p>
    <?php else: ?>
      <div style="max-height: 500px; overflow-y: auto;">
        <table class="admin-table" style="font-size: 11px;">
          <thead>
            <tr>
              <th>EMAIL</th>
              <th>DATE</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subscribers as $sub): ?>
              <tr>
                <td style="color: #fff;"><?= e($sub['email']) ?></td>
                <td style="color: #8a8277;"><?= date('M j, Y', strtotime($sub['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

</main>
</body>
</html>
