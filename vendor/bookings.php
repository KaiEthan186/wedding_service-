<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Booking Requests';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

handle_booking_post_actions(['role' => 'vendor', 'vendor_id' => (int) $vendor['id'], 'user_id' => (int) $user['id']]);

$b = db()->prepare(
    'SELECT b.*, u.first_name, u.last_name, u.email, u.phone, s.title AS service_title
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     LEFT JOIN services s ON s.id = b.service_id
     WHERE b.vendor_id = ? ORDER BY b.event_date ASC'
);
$b->execute([$vendor['id']]);
$bookings = $b->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1>Booking requests</h1>
    <p class="text-muted">Flow: Pending → Confirmed → In progress → Completed. You can reject pending or cancel active bookings.</p>

    <table class="data-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Date</th>
                <th>Service</th>
                <th>Total</th>
                <th>Deposit</th>
                <th>Remaining</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $row) :
            $transitions = vendor_booking_status_transitions((string) $row['status']);
            ?>
            <tr>
                <td>
                    <?= e($row['first_name'] . ' ' . $row['last_name']) ?>
                    <br /><small><?= e((string) $row['email']) ?></small>
                </td>
                <td><?= e($row['event_date']) ?></td>
                <td><?= e((string) ($row['service_title'] ?? 'Package')) ?></td>
                <td><?= format_money($row['total_amount']) ?></td>
                <td><?= format_money($row['deposit_amount']) ?></td>
                <td><?= format_money($row['remaining_amount']) ?></td>
                <td><span class="<?= status_badge_class((string) $row['status']) ?>"><?= e(booking_status_label((string) $row['status'])) ?></span></td>
                <td><span class="<?= status_badge_class((string) $row['payment_status']) ?>"><?= e(ucfirst((string) $row['payment_status'])) ?></span></td>
                <td class="table-actions">
                    <?php foreach ($transitions as $next) : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="booking_id" value="<?= (int) $row['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <button type="submit" name="status" value="<?= e($next) ?>" class="btn btn--<?= $next === 'rejected' || $next === 'cancelled' ? 'ghost' : 'primary' ?> btn--sm">
                            <?= e(match ($next) {
                                'confirmed'   => 'Accept',
                                'rejected'    => 'Reject',
                                'in_progress' => 'Start',
                                'completed'   => 'Complete',
                                'cancelled'   => 'Cancel',
                                default       => booking_status_label($next),
                            }) ?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                    <a href="../admin/booking-invoice.php?id=<?= (int) $row['id'] ?>" class="btn btn--ghost btn--sm" target="_blank">Invoice</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
