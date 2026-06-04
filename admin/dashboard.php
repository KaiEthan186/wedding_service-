<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Admin Dashboard';

$pdo = db();
$stats = [
    'customers'=> (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn(),
    'vendors'  => (int) $pdo->query('SELECT COUNT(*) FROM vendors')->fetchColumn(),
    'bookings' => (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
    'pending_approvals' => (int) $pdo->query('SELECT COUNT(*) FROM vendors WHERE is_approved = 0')->fetchColumn()
        + (int) $pdo->query('SELECT COUNT(*) FROM services WHERE is_approved = 0')->fetchColumn()
        + (int) $pdo->query('SELECT COUNT(*) FROM products WHERE is_approved = 0')->fetchColumn(),
    'revenue'  => (float) $pdo->query(
        'SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE payment_status = "paid"'
    )->fetchColumn() + (float) $pdo->query(
        'SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status = "paid"'
    )->fetchColumn(),
];

$monthlyRevenue = analytics_monthly_revenue();
$forecast = analytics_revenue_forecast();
$byCategory = analytics_bookings_by_category();
$byRole = analytics_user_roles();

$latestBookings = $pdo->query(
    'SELECT b.id, b.event_date, b.status, b.total_amount, u.first_name, u.last_name
     FROM bookings b JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT 5'
)->fetchAll();
$latestUsers = $pdo->query(
    'SELECT id, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5'
)->fetchAll();
$pendingVendors = $pdo->query(
    'SELECT v.id, v.business_name, v.created_at, u.email
     FROM vendors v JOIN users u ON u.id = v.user_id WHERE v.is_approved = 0 ORDER BY v.created_at DESC LIMIT 5'
)->fetchAll();

$chartData = [
    'revenue' => [
        'labels' => array_merge($monthlyRevenue['labels'], $forecast['labels']),
        'actual' => array_merge($monthlyRevenue['values'], array_fill(0, count($forecast['labels']), null)),
        'forecast' => array_merge(array_fill(0, count($monthlyRevenue['labels']), null), $forecast['values']),
    ],
    'category' => $byCategory,
    'roles' => $byRole,
];

$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../js/dashboard-charts.js"></script>';

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Dashboard</h1>

    <div class="stat-grid stat-grid--4">
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['customers'] ?></span><span class="stat-card__lbl">Total customers</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['vendors'] ?></span><span class="stat-card__lbl">Total vendors</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['bookings'] ?></span><span class="stat-card__lbl">Total bookings</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= format_money($stats['revenue']) ?></span><span class="stat-card__lbl">Total sales</span></div>
    </div>
    <div class="stat-grid stat-grid--4">
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['pending_approvals'] ?></span><span class="stat-card__lbl">Pending approvals</span></div>
    </div>

    <div class="chart-grid">
        <div class="panel chart-panel">
            <h2 class="panel__title">Monthly revenue &amp; forecast</h2>
            <canvas id="chart-revenue" data-chart="revenue" data-payload='<?= e(json_encode($chartData['revenue'])) ?>'></canvas>
        </div>
        <div class="panel chart-panel">
            <h2 class="panel__title">Bookings by category</h2>
            <canvas id="chart-category" data-chart="bar" data-payload='<?= e(json_encode($chartData['category'])) ?>'></canvas>
        </div>
        <div class="panel chart-panel">
            <h2 class="panel__title">User roles</h2>
            <canvas id="chart-roles" data-chart="pie" data-payload='<?= e(json_encode($chartData['roles'])) ?>'></canvas>
        </div>
    </div>

    <div class="dash-bottom-grid">
        <div class="panel">
            <h2 class="panel__title">Latest bookings</h2>
            <table class="data-table data-table--compact">
                <thead><tr><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($latestBookings as $b) : ?>
                    <tr>
                        <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                        <td><?= e($b['event_date']) ?></td>
                        <td><?= format_money($b['total_amount']) ?></td>
                        <td><span class="<?= status_badge_class((string) $b['status']) ?>"><?= e(booking_status_label((string) $b['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="bookings.php" class="panel__link">View all bookings →</a>
        </div>
        <div class="panel">
            <h2 class="panel__title">Latest users</h2>
            <ul class="dash-list">
                <?php foreach ($latestUsers as $u) : ?>
                    <li><?= e($u['first_name'] . ' ' . $u['last_name']) ?> <span class="badge"><?= e($u['role']) ?></span></li>
                <?php endforeach; ?>
            </ul>
            <a href="users.php" class="panel__link">Manage users →</a>
        </div>
        <div class="panel">
            <h2 class="panel__title">Vendor approval requests</h2>
            <ul class="dash-list">
                <?php if (!$pendingVendors) : ?>
                    <li class="text-muted">No pending vendors</li>
                <?php endif; ?>
                <?php foreach ($pendingVendors as $v) : ?>
                    <li><?= e($v['business_name']) ?> <small><?= e($v['email']) ?></small></li>
                <?php endforeach; ?>
            </ul>
            <a href="vendors.php" class="panel__link">Manage vendors →</a>
        </div>
    </div>

<?php
echo $extraScripts;
require dirname(__DIR__) . '/includes/admin_footer.php';
