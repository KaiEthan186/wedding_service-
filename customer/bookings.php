<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');
$pageTitle = 'My Bookings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    if ($bookingId > 0) {
        db()->prepare(
            'UPDATE bookings SET status = "cancelled", updated_at = NOW() WHERE id = ? AND user_id = ? AND status IN ("pending", "confirmed")'
        )->execute([$bookingId, (int) $user['id']]);
        db()->prepare(
            'INSERT INTO booking_timeline (booking_id, actor_user_id, event_type, event_message) VALUES (?, ?, "cancelled", "Booking cancelled by customer.")'
        )->execute([$bookingId, (int) $user['id']]);
        flash_set('success', 'Booking cancelled.');
    }
    redirect('bookings.php');
}

$stmt = db()->prepare(
    'SELECT b.*, s.title AS service_title, p.name AS package_name, v.business_name
     FROM bookings b
     LEFT JOIN services s ON s.id = b.service_id
     LEFT JOIN packages p ON p.id = b.package_id
     LEFT JOIN vendors v ON v.id = b.vendor_id
     WHERE b.user_id = ? ORDER BY b.event_date DESC'
);
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <h1>My bookings</h1>
    <table class="data-table">
        <thead><tr><th>Date</th><th>Service / Package</th><th>Vendor</th><th>Total</th><th>Deposit</th><th>Remaining</th><th>Status</th><th>Payment</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b) : ?>
            <tr>
                <td><?= e($b['event_date']) ?></td>
                <td><?= e($b['service_title'] ?? $b['package_name'] ?? '—') ?></td>
                <td><?= e((string) ($b['business_name'] ?? '—')) ?></td>
                <td><?= format_money($b['total_amount']) ?></td>
                <td><?= format_money($b['deposit_amount']) ?></td>
                <td><?= format_money($b['remaining_amount']) ?></td>
                <td><span class="badge"><?= e($b['status']) ?></span></td>
                <td><?= e($b['payment_status']) ?></td>
                <td>
                    <?php if (in_array($b['status'], ['pending', 'confirmed'], true)) : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>" />
                        <button type="submit" class="btn btn--ghost btn--sm">Cancel</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bookings) : ?><tr><td colspan="9">No bookings yet. <a href="../booking.php">Book now</a></td></tr><?php endif; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
