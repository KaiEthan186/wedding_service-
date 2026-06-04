<?php
declare(strict_types=1);

$base = '../';
require __DIR__ . '/header.php';
$user = require_role('customer');
?>
<div class="dashboard-layout">
    <aside class="dash-sidebar dash-sidebar--modern">
        <p class="dash-sidebar__title">My account</p>
        <nav class="dash-nav">
            <a href="<?= $base ?>customer/dashboard.php">Dashboard</a>
            <a href="<?= $base ?>customer/bookings.php">My Bookings</a>
            <a href="<?= $base ?>customer/orders.php">Orders</a>
            <a href="<?= $base ?>customer/wishlist.php">Wishlist</a>
            <a href="<?= $base ?>customer/messages.php">Messages</a>
            <a href="<?= $base ?>customer/profile.php">Profile</a>
            <a href="<?= $base ?>services.php">Browse services</a>
            <a href="<?= $base ?>shop.php">Browse shop</a>
            <a href="<?= $base ?>index.php">← Site</a>
        </nav>
    </aside>
    <div class="dash-main">
