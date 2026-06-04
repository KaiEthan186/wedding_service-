<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Book a Service';
$user = require_login();
$serviceId = (int) ($_GET['service'] ?? 0);
$packageId = (int) ($_GET['package'] ?? 0);

$service = null;
$package = null;
$amount = 0.0;
$depositAmount = 0.0;
$vendorId = null;
$backUrl = safe_return_url((string) ($_GET['back'] ?? ''), 'services.php');

if ($serviceId) {
    $s = db()->prepare('SELECT s.*, v.id AS vendor_id, v.is_approved AS vendor_approved FROM services s JOIN vendors v ON v.id = s.vendor_id WHERE s.id = ?');
    $s->execute([$serviceId]);
    $service = $s->fetch();
    if ($service && (int) $service['is_approved'] === 1 && (string) $service['status'] === 'active' && (int) $service['vendor_approved'] === 1) {
        $amount = (float) $service['price'];
        $depositAmount = round($amount * 0.2, 2);
        $vendorId = (int) $service['vendor_id'];
        $backUrl = 'service-details.php?slug=' . rawurlencode((string) $service['slug']);
    } else {
        $service = null;
    }
}
if ($packageId) {
    $p = db()->prepare('SELECT p.*, v.id AS vendor_id, v.is_approved AS vendor_approved FROM packages p JOIN vendors v ON v.id = p.vendor_id WHERE p.id = ? AND p.status = "active"');
    $p->execute([$packageId]);
    $package = $p->fetch();
    if ($package && (int) $package['vendor_approved'] === 1) {
        $amount = (float) $package['price'];
        $depositAmount = round($amount * 0.2, 2);
        $vendorId = (int) $package['vendor_id'];
        $backUrl = 'packages.php';
    } else {
        $package = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventDate = trim((string) ($_POST['event_date'] ?? ''));
    $guests = (int) ($_POST['guest_count'] ?? 0);
    $requests = trim((string) ($_POST['special_requests'] ?? ''));
    $payment = $_POST['payment_method'] ?? 'cod';
    $sid = (int) ($_POST['service_id'] ?? 0);
    $pid = (int) ($_POST['package_id'] ?? 0);

    if ($eventDate === '') {
        flash_set('error', 'Please select an event date.');
        redirect('booking.php?' . http_build_query(array_filter([
            'service' => $serviceId ?: null,
            'package' => $packageId ?: null,
            'back' => $_POST['back'] ?? null,
        ])));
    }

    $total = $amount;
    $deposit = 0.0;
    $remaining = 0.0;
    if ($sid) {
        $s = db()->prepare('SELECT price, vendor_id FROM services WHERE id = ?');
        $s->execute([$sid]);
        $row = $s->fetch();
        $total = (float) ($row['price'] ?? 0);
        $vendorId = (int) ($row['vendor_id'] ?? 0);
    } elseif ($pid) {
        $p = db()->prepare('SELECT price, vendor_id FROM packages WHERE id = ?');
        $p->execute([$pid]);
        $row = $p->fetch();
        $total = (float) ($row['price'] ?? 0);
        $vendorId = (int) ($row['vendor_id'] ?? 0);
    }

    if ($vendorId > 0) {
        $check = db()->prepare(
            'SELECT 1 FROM vendor_availability WHERE vendor_id = ? AND available_date = ? AND is_available = 0 LIMIT 1'
        );
        $check->execute([$vendorId, $eventDate]);
        if ($check->fetchColumn()) {
            flash_set('error', 'Vendor is not available on this date. Please choose another date.');
            redirect('booking.php?' . http_build_query(array_filter([
                'service' => $serviceId ?: null,
                'package' => $packageId ?: null,
                'back' => $_POST['back'] ?? null,
            ])));
        }
    }

    $deposit = round($total * 0.2, 2);
    $remaining = max(0, $total - $deposit);

    $stmt = db()->prepare(
        'INSERT INTO bookings (user_id, vendor_id, service_id, package_id, event_date, guest_count, special_requests, total_amount, deposit_amount, remaining_amount, status, payment_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending", "unpaid")'
    );
    $stmt->execute([
        $user['id'],
        $vendorId ?: null,
        $sid ?: null,
        $pid ?: null,
        $eventDate,
        $guests ?: null,
        $requests ?: null,
        $total,
        $deposit,
        $remaining,
    ]);
    $bookingId = (int) db()->lastInsertId();

    $pay = db()->prepare(
        'INSERT INTO payments (user_id, payable_type, payable_id, amount, method) VALUES (?, "booking", ?, ?, ?)'
    );
    $pay->execute([$user['id'], $bookingId, $deposit, $payment]);

    db()->prepare(
        'INSERT INTO booking_timeline (booking_id, actor_user_id, event_type, event_message) VALUES (?, ?, ?, ?)'
    )->execute([$bookingId, (int) $user['id'], 'submitted', 'Booking submitted by customer.']);
    db()->prepare(
        'INSERT INTO booking_timeline (booking_id, actor_user_id, event_type, event_message) VALUES (?, ?, ?, ?)'
    )->execute([$bookingId, (int) $user['id'], 'deposit_pending', 'Deposit payment is pending verification.']);

    notify_user((int) $user['id'], 'Booking submitted', 'Your booking request is pending vendor confirmation.', 'booking');
    if ($vendorId) {
        $vu = db()->prepare('SELECT user_id FROM vendors WHERE id = ?');
        $vu->execute([$vendorId]);
        $vid = $vu->fetch();
        if ($vid) {
            notify_user((int) $vid['user_id'], 'New booking request', 'A customer requested a booking.', 'booking');
        }
    }

    flash_set('success', 'Booking submitted! You will receive confirmation soon.');
    redirect('customer/bookings.php');
}

require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="section">
        <div class="shell form-page">
            <p class="form-page__back">
                <a href="<?= e($backUrl) ?>" class="btn btn--ghost btn-back">← Back</a>
            </p>
            <h1>Book your wedding</h1>
            <?php if ($service) : ?>
                <p class="banner banner--success">Service: <?= e($service['title']) ?> — <?= format_money($service['price']) ?></p>
            <?php elseif ($package) : ?>
                <p class="banner banner--success">Package: <?= e($package['name']) ?> — <?= format_money($package['price']) ?></p>
            <?php else : ?>
                <p>Select a <a href="services.php">service</a> or <a href="packages.php">package</a> first, or fill the form below.</p>
            <?php endif; ?>
            <form class="form" method="post">
                <input type="hidden" name="service_id" value="<?= $serviceId ?>" />
                <input type="hidden" name="package_id" value="<?= $packageId ?>" />
                <input type="hidden" name="back" value="<?= e($backUrl) ?>" />
                <label><span>Event date</span><input type="date" name="event_date" required min="<?= date('Y-m-d') ?>" /></label>
                <label><span>Guest count</span><input type="number" name="guest_count" min="1" /></label>
                <label><span>Special requests</span><textarea name="special_requests" rows="4"></textarea></label>
                <label><span>Payment method</span>
                    <select name="payment_method" class="form-select">
                        <option value="cod">Cash on delivery</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="manual">Manual payment upload</option>
                    </select>
                </label>
                <p class="text-muted">Deposit due now: 20% (<?= format_money($depositAmount) ?>). Remaining amount is collected before event completion.</p>
                <div class="form-actions">
                    <button type="submit" class="btn btn--primary btn--block">Submit booking</button>
                    <a href="<?= e($backUrl) ?>" class="btn btn--ghost btn--block">Cancel</a>
                </div>
            </form>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
