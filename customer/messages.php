<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');
$pageTitle = 'Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = (int) ($_POST['receiver_id'] ?? 0);
    $body = trim((string) ($_POST['body'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    if ($to && $body !== '') {
        db()->prepare('INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES (?, ?, ?, ?)')
            ->execute([$user['id'], $to, $subject ?: null, $body]);
        notify_user($to, 'New message', 'You have a new message.', 'message');
        flash_set('success', 'Message sent.');
    }
    redirect('messages.php');
}

$inbox = db()->prepare(
    'SELECT m.*, u.first_name, u.last_name FROM messages m
     JOIN users u ON u.id = m.sender_id WHERE m.receiver_id = ? ORDER BY m.created_at DESC LIMIT 20'
);
$inbox->execute([$user['id']]);
$messages = $inbox->fetchAll();
$vendors = db()->query(
    'SELECT u.id, v.business_name FROM vendors v JOIN users u ON u.id = v.user_id WHERE v.is_approved = 1'
)->fetchAll();

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <h1>Messages</h1>
    <form class="form" method="post" style="max-width:480px;margin-bottom:2rem;">
        <label><span>To vendor</span>
            <select name="receiver_id" class="form-select" required>
                <option value="">Select…</option>
                <?php foreach ($vendors as $v) : ?>
                    <option value="<?= (int) $v['id'] ?>"><?= e($v['business_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><span>Subject</span><input type="text" name="subject" /></label>
        <label><span>Message</span><textarea name="body" rows="4" required></textarea></label>
        <button type="submit" class="btn btn--primary">Send</button>
    </form>
    <?php foreach ($messages as $m) : ?>
        <article class="msg-card">
            <p><strong><?= e($m['first_name'] . ' ' . $m['last_name']) ?></strong></p>
            <p><?= e($m['body']) ?></p>
        </article>
    <?php endforeach; ?>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
