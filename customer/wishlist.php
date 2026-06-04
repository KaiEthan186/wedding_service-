<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');

if (isset($_GET['add'], $_GET['id'])) {
    redirect(wishlist_add_url(
        $_GET['add'] === 'product' ? 'product' : 'service',
        (int) $_GET['id'],
        'wishlist.php'
    ));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    db()->prepare('DELETE FROM wishlist WHERE id = ? AND user_id = ?')
        ->execute([(int) $_POST['remove_id'], $user['id']]);
    flash_set('success', 'Removed from wishlist.');
    redirect('wishlist.php');
}

$pageTitle = 'Wishlist';
$stmt = db()->prepare('SELECT * FROM wishlist WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$items = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <div class="page-head">
        <h1>Wishlist</h1>
        <a href="../services.php" class="btn btn--ghost btn--sm">Browse services</a>
    </div>

    <?php if (!$items) : ?>
        <p class="empty-state">Your wishlist is empty. Save services or shop products with the ♥ Wishlist button.</p>
        <div class="card__actions">
            <a href="../services.php" class="btn btn--primary">Explore services</a>
            <a href="../shop.php" class="btn btn--ghost">Visit shop</a>
        </div>
    <?php else : ?>
    <ul class="wishlist-list">
        <?php foreach ($items as $w) :
            $title = 'Item #' . $w['item_id'];
            $price = '';
            $image = '';
            $link = '../index.php';
            if ($w['item_type'] === 'service') {
                $q = db()->prepare('SELECT title, price, image, slug FROM services WHERE id = ?');
                $q->execute([$w['item_id']]);
                if ($row = $q->fetch()) {
                    $title = $row['title'];
                    $price = format_money($row['price']);
                    $image = $row['image'] ?? '';
                    $link = '../service-details.php?slug=' . rawurlencode((string) $row['slug']);
                }
            } else {
                $q = db()->prepare('SELECT name, price, image FROM products WHERE id = ?');
                $q->execute([$w['item_id']]);
                if ($row = $q->fetch()) {
                    $title = $row['name'];
                    $price = format_money($row['price']);
                    $image = $row['image'] ?? '';
                    $link = '../shop.php';
                }
            }
            ?>
            <li class="wishlist-card">
                <div class="wishlist-card__img" style="<?= card_image_style($image ?: null, '../', $w['item_type'] === 'product' ? 'product' : 'service') ?>"></div>
                <div class="wishlist-card__body">
                    <strong><a href="<?= e($link) ?>"><?= e($title) ?></a></strong>
                    <p class="text-muted"><?= e(ucfirst((string) $w['item_type'])) ?><?= $price !== '' ? ' · ' . e($price) : '' ?></p>
                </div>
                <form method="post" class="inline-form">
                    <input type="hidden" name="remove_id" value="<?= (int) $w['id'] ?>" />
                    <button type="submit" class="btn btn--ghost btn--sm">Remove</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
