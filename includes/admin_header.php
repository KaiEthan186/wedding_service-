<?php
declare(strict_types=1);

$base = '../';
require __DIR__ . '/header.php';
$user = require_role('admin');
?>
<div class="dashboard-layout">
    <aside class="dash-sidebar dash-sidebar--modern">
        <p class="dash-sidebar__title">Admin</p>
        <nav class="dash-nav">
            <a href="<?= $base ?>admin/dashboard.php">Dashboard</a>
            <a href="<?= $base ?>admin/users.php">Users</a>
            <a href="<?= $base ?>admin/vendors.php">Vendors</a>
            <a href="<?= $base ?>admin/services.php">Services</a>
            <a href="<?= $base ?>admin/products.php">Products</a>
            <a href="<?= $base ?>admin/categories.php">Categories</a>
            <a href="<?= $base ?>admin/bookings.php">Bookings</a>
            <a href="<?= $base ?>admin/orders.php">Orders</a>
            <a href="<?= $base ?>admin/reports.php">Reports</a>
            <a href="<?= $base ?>admin/reviews.php">Reviews</a>
            <a href="<?= $base ?>index.php">← Site</a>
        </nav>
    </aside>
    <div class="dash-main">
