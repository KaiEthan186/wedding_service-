<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');
$pageTitle = 'My Orders';

$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <h1>My orders</h1>
    <table class="data-table">
        <thead><tr><th>Order #</th><th>Total</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o) : ?>
            <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= format_money($o['total']) ?></td>
                <td><span class="badge"><?= e($o['status']) ?></span></td>
                <td><?= e($o['payment_status']) ?></td>
                <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$orders) : ?><tr><td colspan="5">No orders yet. <a href="../shop.php">Shop now</a></td></tr><?php endif; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
