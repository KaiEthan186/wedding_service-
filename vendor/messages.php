<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$pageTitle = 'Messages';

$inbox = db()->prepare(
    'SELECT m.*, u.first_name, u.last_name FROM messages m
     JOIN users u ON u.id = m.sender_id WHERE m.receiver_id = ? ORDER BY m.created_at DESC'
);
$inbox->execute([$user['id']]);
$messages = $inbox->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1>Messages</h1>
    <?php foreach ($messages as $m) : ?>
        <article class="msg-card">
            <p><strong><?= e($m['first_name'] . ' ' . $m['last_name']) ?></strong> — <?= e((string) ($m['subject'] ?? '')) ?></p>
            <p><?= e($m['body']) ?></p>
        </article>
    <?php endforeach; ?>
    <?php if (!$messages) : ?><p>No messages.</p><?php endif; ?>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
