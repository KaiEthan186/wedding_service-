<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$type = ($_GET['type'] ?? $_GET['add'] ?? '') === 'product' ? 'product' : 'service';
$id = (int) ($_GET['id'] ?? 0);
$return = safe_return_url((string) ($_GET['return'] ?? ''), 'index.php');

$user = auth_user();
if (!$user) {
    redirect('login.php?redirect=' . rawurlencode('wishlist-add.php?type=' . $type . '&id=' . $id . '&return=' . rawurlencode($return)));
}

if ($user['role'] !== 'customer') {
    flash_set('error', 'Wishlist is available for customer accounts.');
    redirect($return);
}

if ($id <= 0) {
    flash_set('error', 'Invalid item.');
    redirect($return);
}

db()->prepare('INSERT IGNORE INTO wishlist (user_id, item_type, item_id) VALUES (?, ?, ?)')
    ->execute([(int) $user['id'], $type, $id]);

flash_set('success', 'Added to wishlist.');
redirect($return);
