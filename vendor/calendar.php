<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Availability Calendar';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = (string) ($_POST['available_date'] ?? '');
    $isAvailable = (int) ($_POST['is_available'] ?? 1) === 1 ? 1 : 0;
    $note = trim((string) ($_POST['note'] ?? ''));
    if ($date !== '') {
        db()->prepare(
            'INSERT INTO vendor_availability (vendor_id, available_date, is_available, note)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE is_available = VALUES(is_available), note = VALUES(note)'
        )->execute([(int) $vendor['id'], $date, $isAvailable, $note ?: null]);
        flash_set('success', 'Availability updated.');
    }
    redirect('calendar.php');
}

$bookedStmt = db()->prepare(
    'SELECT id, event_date, status FROM bookings
     WHERE vendor_id = ? AND status IN ("pending", "confirmed", "in_progress")
     ORDER BY event_date ASC'
);
$bookedStmt->execute([(int) $vendor['id']]);
$booked = $bookedStmt->fetchAll();

$availabilityStmt = db()->prepare(
    'SELECT * FROM vendor_availability WHERE vendor_id = ? ORDER BY available_date ASC LIMIT 120'
);
$availabilityStmt->execute([(int) $vendor['id']]);
$availability = $availabilityStmt->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>
<h1>Availability calendar</h1>
<p class="text-muted">Set unavailable dates to prevent double booking.</p>
<form method="post" class="form" style="max-width:520px;">
    <label><span>Date</span><input type="date" name="available_date" required min="<?= date('Y-m-d') ?>" /></label>
    <label><span>Status</span>
        <select name="is_available" class="form-select">
            <option value="1">Available</option>
            <option value="0">Unavailable</option>
        </select>
    </label>
    <label><span>Note</span><input type="text" name="note" placeholder="Optional note" /></label>
    <button type="submit" class="btn btn--primary">Save</button>
</form>

<h2>Upcoming weddings</h2>
<ul class="dash-list">
    <?php foreach ($booked as $b) : ?>
        <li><?= e((string) $b['event_date']) ?> — Booking #<?= (int) $b['id'] ?> (<?= e((string) $b['status']) ?>)</li>
    <?php endforeach; ?>
    <?php if (!$booked) : ?><li class="text-muted">No upcoming bookings.</li><?php endif; ?>
</ul>

<h2>Configured availability</h2>
<ul class="dash-list">
    <?php foreach ($availability as $a) : ?>
        <li><?= e((string) $a['available_date']) ?> — <?= (int) $a['is_available'] === 1 ? 'Available' : 'Unavailable' ?><?= $a['note'] ? ' · ' . e((string) $a['note']) : '' ?></li>
    <?php endforeach; ?>
    <?php if (!$availability) : ?><li class="text-muted">No custom availability yet.</li><?php endif; ?>
</ul>
<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
