<?php
declare(strict_types=1);

$base = '../';
require __DIR__ . '/header.php';
$user = require_role('vendor');
?>
<div class="dashboard-layout">
    <aside class="dash-sidebar dash-sidebar--modern">
        <p class="dash-sidebar__title">Vendor</p>
        <nav class="dash-nav">
            <a href="<?= $base ?>vendor/dashboard.php">Dashboard</a>
            <a href="<?= $base ?>vendor/profile.php">Business Profile</a>
            <a href="<?= $base ?>vendor/services.php">My Services</a>
            <a href="<?= $base ?>vendor/add-service.php">Add Service</a>
            <a href="<?= $base ?>vendor/packages.php">My Packages</a>
            <a href="<?= $base ?>vendor/products.php">My Products</a>
            <a href="<?= $base ?>vendor/bookings.php">Bookings</a>
            <a href="<?= $base ?>vendor/calendar.php">Availability</a>
            <a href="<?= $base ?>vendor/portfolio.php">Portfolio</a>
            <a href="<?= $base ?>vendor/quotations.php">Quotations</a>
            <a href="<?= $base ?>vendor/messages.php">Messages</a>
            <a href="<?= $base ?>index.php">← Site</a>
        </nav>
    </aside>
    <div class="dash-main">
