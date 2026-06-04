<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');
$pageTitle = 'My Dashboard';

$bookings = db()->prepare('SELECT COUNT(*) AS c FROM bookings WHERE user_id = ?');
$bookings->execute([$user['id']]);
$bookingCount = (int) $bookings->fetch()['c'];

$orders = db()->prepare('SELECT COUNT(*) AS c FROM orders WHERE user_id = ?');
$orders->execute([$user['id']]);
$orderCount = (int) $orders->fetch()['c'];

$wish = db()->prepare('SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?');
$wish->execute([$user['id']]);
$wishCount = (int) $wish->fetch()['c'];

$notifs = db()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5');
$notifs->execute([$user['id']]);
$notifications = $notifs->fetchAll();

$upcoming = db()->prepare(
    'SELECT COUNT(*) AS c FROM bookings WHERE user_id = ? AND event_date >= CURDATE() AND status IN ("pending", "confirmed", "in_progress")'
);
$upcoming->execute([$user['id']]);
$upcomingCount = (int) ($upcoming->fetch()['c'] ?? 0);

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <h1>Hello, <?= e($user['first_name']) ?></h1>
    <div class="stat-grid">
        <div class="stat-card"><span class="stat-card__val"><?= $upcomingCount ?></span><span class="stat-card__lbl">Upcoming weddings</span></div>
        <div class="stat-card"><span class="stat-card__val"><?= $bookingCount ?></span><span class="stat-card__lbl">Bookings</span></div>
        <div class="stat-card"><span class="stat-card__val"><?= $orderCount ?></span><span class="stat-card__lbl">Orders</span></div>
        <div class="stat-card"><span class="stat-card__val"><?= $wishCount ?></span><span class="stat-card__lbl">Wishlist</span></div>
    </div>
    <h2>Recent notifications</h2>
    <ul class="notif-list">
        <?php foreach ($notifications as $n) : ?>
            <li class="<?= $n['is_read'] ? '' : 'is-unread' ?>"><strong><?= e($n['title']) ?></strong> — <?= e($n['message']) ?></li>
        <?php endforeach; ?>
        <?php if (!$notifications) : ?><li>No notifications yet.</li><?php endif; ?>
    </ul>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
