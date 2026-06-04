<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/analytics.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Vendor Dashboard';

$stats = [
    'services' => 0,
    'bookings' => 0,
    'pending' => 0,
    'completed' => 0,
    'revenue' => 0.0,
    'reviews' => 0,
    'rating' => 0.0,
    'upcoming' => 0,
];

$chartData = ['revenue' => ['labels' => [], 'values' => []], 'status' => ['labels' => [], 'values' => []], 'services' => ['labels' => [], 'values' => []]];
$recentBookings = [];
$recentReviews = [];

if ($vendor) {
    $vid = (int) $vendor['id'];
    $pdo = db();

    $s = $pdo->prepare('SELECT COUNT(*) FROM services WHERE vendor_id = ?');
    $s->execute([$vid]);
    $stats['services'] = (int) $s->fetchColumn();

    $b = $pdo->prepare(
        'SELECT COUNT(*) AS total,
                SUM(status = "pending") AS pending,
                SUM(status = "completed") AS completed
         FROM bookings WHERE vendor_id = ?'
    );
    $b->execute([$vid]);
    $br = $b->fetch();
    $stats['bookings'] = (int) ($br['total'] ?? 0);
    $stats['pending'] = (int) ($br['pending'] ?? 0);
    $stats['completed'] = (int) ($br['completed'] ?? 0);
    $up = $pdo->prepare(
        'SELECT COUNT(*) FROM bookings WHERE vendor_id = ? AND event_date >= CURDATE() AND status IN ("pending", "confirmed", "in_progress")'
    );
    $up->execute([$vid]);
    $stats['upcoming'] = (int) $up->fetchColumn();

    $rev = $pdo->prepare(
        'SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE vendor_id = ? AND payment_status = "paid"'
    );
    $rev->execute([$vid]);
    $stats['revenue'] = (float) $rev->fetchColumn();

    $stats['reviews'] = (int) $vendor['rating_count'];
    $stats['rating'] = (float) $vendor['rating_avg'];

    $chartData['revenue'] = analytics_vendor_monthly_revenue($vid);
    $chartData['status'] = analytics_vendor_booking_status($vid);
    $chartData['services'] = analytics_vendor_top_services($vid);

    $rb = $pdo->prepare(
        'SELECT b.*, u.first_name, u.last_name, s.title AS service_title
         FROM bookings b
         JOIN users u ON u.id = b.user_id
         LEFT JOIN services s ON s.id = b.service_id
         WHERE b.vendor_id = ? ORDER BY b.created_at DESC LIMIT 5'
    );
    $rb->execute([$vid]);
    $recentBookings = $rb->fetchAll();

    $rr = $pdo->prepare(
        'SELECT r.rating, r.comment, r.created_at, u.first_name, u.last_name
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.reviewable_type = "vendor" AND r.reviewable_id = ?
         ORDER BY r.created_at DESC LIMIT 5'
    );
    $rr->execute([$vid]);
    $recentReviews = $rr->fetchAll();
}

$extraScripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../js/dashboard-charts.js"></script>';

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1><?= e($vendor['business_name'] ?? 'Vendor') ?></h1>
    <?php if (!(int) ($vendor['is_approved'] ?? 0)) : ?>
        <p class="banner banner--error">Your vendor account is pending admin approval.</p>
    <?php endif; ?>

    <div class="stat-grid stat-grid--4">
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['services'] ?></span><span class="stat-card__lbl">Total services</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['bookings'] ?></span><span class="stat-card__lbl">Total bookings</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['pending'] ?></span><span class="stat-card__lbl">Pending</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= format_money($stats['revenue']) ?></span><span class="stat-card__lbl">Total revenue</span></div>
    </div>

    <div class="stat-grid stat-grid--3">
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['completed'] ?></span><span class="stat-card__lbl">Completed</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['reviews'] ?></span><span class="stat-card__lbl">Reviews</span></div>
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= number_format($stats['rating'], 1) ?></span><span class="stat-card__lbl">Avg rating</span></div>
    </div>
    <div class="stat-grid stat-grid--3">
        <div class="stat-card stat-card--modern"><span class="stat-card__val"><?= $stats['upcoming'] ?></span><span class="stat-card__lbl">Upcoming events</span></div>
    </div>

    <?php if ($vendor) : ?>
    <div class="chart-grid">
        <div class="panel chart-panel">
            <h2 class="panel__title">Monthly revenue</h2>
            <canvas id="chart-v-revenue" data-chart="line" data-payload='<?= e(json_encode($chartData['revenue'])) ?>'></canvas>
        </div>
        <div class="panel chart-panel">
            <h2 class="panel__title">Booking status</h2>
            <canvas id="chart-v-status" data-chart="pie" data-payload='<?= e(json_encode($chartData['status'])) ?>'></canvas>
        </div>
        <div class="panel chart-panel">
            <h2 class="panel__title">Most booked services</h2>
            <canvas id="chart-v-services" data-chart="bar" data-payload='<?= e(json_encode($chartData['services'])) ?>'></canvas>
        </div>
    </div>

    <div class="dash-bottom-grid">
        <div class="panel">
            <h2 class="panel__title">Recent bookings</h2>
            <table class="data-table data-table--compact">
                <thead><tr><th>Customer</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recentBookings as $b) : ?>
                    <tr>
                        <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                        <td><?= e((string) ($b['service_title'] ?? 'Package')) ?></td>
                        <td><?= e($b['event_date']) ?></td>
                        <td><span class="<?= status_badge_class((string) $b['status']) ?>"><?= e(booking_status_label((string) $b['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="bookings.php" class="panel__link">All bookings →</a>
        </div>
        <div class="panel">
            <h2 class="panel__title">Recent reviews</h2>
            <ul class="dash-list">
                <?php if (!$recentReviews) : ?>
                    <li class="text-muted">No reviews yet</li>
                <?php endif; ?>
                <?php foreach ($recentReviews as $r) : ?>
                    <li>
                        <strong><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>
                        — <?= (int) $r['rating'] ?>★
                        <br /><small><?= e(substr((string) ($r['comment'] ?? ''), 0, 80)) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

<?php
echo $extraScripts;
require dirname(__DIR__) . '/includes/vendor_footer.php';
