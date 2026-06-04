<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_login();
if (!in_array($user['role'], ['admin', 'vendor'], true)) {
    redirect('../login.php');
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT b.*, u.first_name, u.last_name, u.email, v.business_name, s.title AS service_title, p.name AS package_name
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     LEFT JOIN vendors v ON v.id = b.vendor_id
     LEFT JOIN services s ON s.id = b.service_id
     LEFT JOIN packages p ON p.id = b.package_id
     WHERE b.id = ?'
);
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) {
    flash_set('error', 'Booking not found.');
    redirect($user['role'] === 'vendor' ? '../vendor/bookings.php' : 'bookings.php');
}

if ($user['role'] === 'vendor') {
    $vendor = vendor_profile_for_user((int) $user['id']);
    if (!$vendor || (int) $b['vendor_id'] !== (int) $vendor['id']) {
        flash_set('error', 'Not allowed.');
        redirect('../vendor/bookings.php');
    }
}

$siteName = (string) ($config['site_name'] ?? 'Wedding Service');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Invoice #<?= (int) $b['id'] ?> — <?= e($siteName) ?></title>
    <link rel="stylesheet" href="../css/wedding.css?v=5" />
    <style>
        body { padding: 2rem; max-width: 720px; margin: 0 auto; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button type="button" class="btn btn--primary no-print" onclick="window.print()">Print</button>
    <h1><?= e($siteName) ?></h1>
    <h2>Booking Invoice #<?= (int) $b['id'] ?></h2>
    <p><strong>Date issued:</strong> <?= e(date('Y-m-d')) ?></p>
    <hr />
    <p><strong>Bill to:</strong><br /><?= e($b['first_name'] . ' ' . $b['last_name']) ?><br /><?= e($b['email']) ?></p>
    <p><strong>Vendor:</strong> <?= e((string) ($b['business_name'] ?? '—')) ?></p>
    <p><strong>Service:</strong> <?= e((string) ($b['service_title'] ?? $b['package_name'] ?? '—')) ?></p>
    <p><strong>Event date:</strong> <?= e($b['event_date']) ?></p>
    <p><strong>Status:</strong> <?= e(booking_status_label((string) $b['status'])) ?></p>
    <p><strong>Payment:</strong> <?= e(ucfirst((string) $b['payment_status'])) ?></p>
    <p class="invoice-total"><strong>Total:</strong> <?= format_money($b['total_amount']) ?></p>
</body>
</html>
