<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$admin = require_role('admin');
$pageTitle = 'Bookings';

handle_booking_post_actions(['role' => 'admin', 'user_id' => (int) $admin['id']]);

$filterStatus = trim((string) ($_GET['status'] ?? ''));
$sql = 'SELECT b.*, u.first_name, u.last_name, u.email, v.business_name, s.title AS service_title
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        LEFT JOIN vendors v ON v.id = b.vendor_id
        LEFT JOIN services s ON s.id = b.service_id
        WHERE 1=1';
$params = [];
if ($filterStatus !== '' && in_array($filterStatus, BOOKING_STATUSES, true)) {
    $sql .= ' AND b.status = ?';
    $params[] = $filterStatus;
}
$sql .= ' ORDER BY b.created_at DESC LIMIT 100';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$viewId = (int) ($_GET['view'] ?? 0);
$viewBooking = null;
$timeline = [];
if ($viewId > 0) {
    $vb = db()->prepare(
        'SELECT b.*, u.first_name, u.last_name, u.email, u.phone, v.business_name, s.title AS service_title, p.name AS package_name
         FROM bookings b
         JOIN users u ON u.id = b.user_id
         LEFT JOIN vendors v ON v.id = b.vendor_id
         LEFT JOIN services s ON s.id = b.service_id
         LEFT JOIN packages p ON p.id = b.package_id
         WHERE b.id = ?'
    );
    $vb->execute([$viewId]);
    $viewBooking = $vb->fetch() ?: null;
    if ($viewBooking) {
        $tl = db()->prepare(
            'SELECT bt.*, u.first_name, u.last_name
             FROM booking_timeline bt
             LEFT JOIN users u ON u.id = bt.actor_user_id
             WHERE bt.booking_id = ?
             ORDER BY bt.created_at ASC'
        );
        $tl->execute([$viewId]);
        $timeline = $tl->fetchAll();
    }
}

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <div class="page-head">
        <h1>Bookings</h1>
        <form method="get" class="inline-form filter-bar">
            <label for="status">Filter</label>
            <select name="status" id="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach (BOOKING_STATUSES as $st) : ?>
                    <option value="<?= e($st) ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= e(booking_status_label($st)) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($viewBooking) : ?>
    <div class="panel panel--highlight" id="booking-detail">
        <h2>Booking #<?= (int) $viewBooking['id'] ?></h2>
        <div class="detail-grid">
            <p><strong>Customer:</strong> <?= e($viewBooking['first_name'] . ' ' . $viewBooking['last_name']) ?> (<?= e($viewBooking['email']) ?>)</p>
            <p><strong>Phone:</strong> <?= e((string) ($viewBooking['phone'] ?? '—')) ?></p>
            <p><strong>Vendor:</strong> <?= e((string) ($viewBooking['business_name'] ?? '—')) ?></p>
            <p><strong>Service:</strong> <?= e((string) ($viewBooking['service_title'] ?? $viewBooking['package_name'] ?? '—')) ?></p>
            <p><strong>Event date:</strong> <?= e($viewBooking['event_date']) ?></p>
            <p><strong>Total:</strong> <?= format_money($viewBooking['total_amount']) ?></p>
            <p><strong>Deposit:</strong> <?= format_money($viewBooking['deposit_amount']) ?></p>
            <p><strong>Remaining:</strong> <?= format_money($viewBooking['remaining_amount']) ?></p>
            <p><strong>Status:</strong> <span class="<?= status_badge_class((string) $viewBooking['status']) ?>"><?= e(booking_status_label((string) $viewBooking['status'])) ?></span></p>
            <p><strong>Payment:</strong> <span class="<?= status_badge_class((string) $viewBooking['payment_status']) ?>"><?= e(ucfirst((string) $viewBooking['payment_status'])) ?></span></p>
        </div>
        <?php if (!empty($viewBooking['special_requests'])) : ?>
            <p><strong>Requests:</strong> <?= e($viewBooking['special_requests']) ?></p>
        <?php endif; ?>
        <?php if ($timeline) : ?>
            <h3>Event timeline</h3>
            <ul class="dash-list">
                <?php foreach ($timeline as $ev) : ?>
                    <li>
                        <strong><?= e((string) $ev['event_message']) ?></strong>
                        <small>
                            <?= e((string) $ev['created_at']) ?>
                            <?php if (!empty($ev['first_name'])) : ?> · <?= e($ev['first_name'] . ' ' . $ev['last_name']) ?><?php endif; ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="action-row">
            <form method="post" class="inline-form">
                <input type="hidden" name="booking_id" value="<?= (int) $viewBooking['id'] ?>" />
                <input type="hidden" name="action" value="status" />
                <select name="status" required>
                    <?php foreach (BOOKING_STATUSES as $st) : ?>
                        <option value="<?= e($st) ?>" <?= $viewBooking['status'] === $st ? 'selected' : '' ?>><?= e(booking_status_label($st)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn--primary btn--sm">Update status</button>
            </form>
            <form method="post" class="inline-form">
                <input type="hidden" name="booking_id" value="<?= (int) $viewBooking['id'] ?>" />
                <input type="hidden" name="action" value="payment" />
                <select name="payment_status" required>
                    <?php foreach (BOOKING_PAYMENT_STATUSES as $ps) : ?>
                        <option value="<?= e($ps) ?>" <?= $viewBooking['payment_status'] === $ps ? 'selected' : '' ?>><?= e(ucfirst($ps)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn--ghost btn--sm">Update payment</button>
            </form>
            <a href="booking-invoice.php?id=<?= (int) $viewBooking['id'] ?>" class="btn btn--ghost btn--sm" target="_blank">Print invoice</a>
            <a href="bookings.php" class="btn btn--ghost btn--sm">Close</a>
        </div>
    </div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Vendor</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Deposit</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b) : ?>
            <tr>
                <td><?= (int) $b['id'] ?></td>
                <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                <td><?= e((string) ($b['business_name'] ?? '—')) ?></td>
                <td><?= e($b['event_date']) ?></td>
                <td><?= format_money($b['total_amount']) ?></td>
                <td><?= format_money($b['deposit_amount']) ?></td>
                <td><span class="<?= status_badge_class((string) $b['status']) ?>"><?= e(booking_status_label((string) $b['status'])) ?></span></td>
                <td><span class="<?= status_badge_class((string) $b['payment_status']) ?>"><?= e(ucfirst((string) $b['payment_status'])) ?></span></td>
                <td class="table-actions">
                    <a href="bookings.php?view=<?= (int) $b['id'] ?>" class="btn btn--ghost btn--sm">View</a>
                    <?php if ($b['status'] === 'pending') : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <button type="submit" name="status" value="confirmed" class="btn btn--primary btn--sm">Confirm</button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <button type="submit" name="status" value="rejected" class="btn btn--ghost btn--sm">Reject</button>
                    </form>
                    <?php endif; ?>
                    <?php if (!in_array($b['status'], ['cancelled', 'rejected', 'completed'], true)) : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <button type="submit" name="status" value="cancelled" class="btn btn--ghost btn--sm">Cancel</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <input type="hidden" name="action" value="payment" />
                        <input type="hidden" name="payment_status" value="paid" />
                        <button type="submit" class="btn btn--ghost btn--sm">Mark paid</button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <input type="hidden" name="action" value="payment" />
                        <input type="hidden" name="payment_status" value="unpaid" />
                        <button type="submit" class="btn btn--ghost btn--sm">Unpaid</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
