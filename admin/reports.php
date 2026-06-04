<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Reports';

$monthly = db()->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS orders, SUM(total) AS revenue
     FROM orders GROUP BY month ORDER BY month DESC LIMIT 12"
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Sales reports</h1>
    <table class="data-table">
        <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php foreach ($monthly as $row) : ?>
            <tr>
                <td><?= e($row['month']) ?></td>
                <td><?= (int) $row['orders'] ?></td>
                <td><?= format_money($row['revenue'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$monthly) : ?><tr><td colspan="3">No order data yet.</td></tr><?php endif; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
