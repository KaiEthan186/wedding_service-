<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Shop';
$search = trim((string) ($_GET['q'] ?? ''));
$cat = trim((string) ($_GET['category'] ?? ''));
$returnTo = 'shop.php' . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');

$sql = 'SELECT p.*, c.name AS category_name, v.business_name
        FROM products p
        JOIN product_categories c ON c.id = p.category_id
        JOIN vendors v ON v.id = p.vendor_id
        WHERE p.is_active = 1 AND p.is_approved = 1 AND v.is_approved = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($cat !== '') {
    $sql .= ' AND c.slug = ?';
    $params[] = $cat;
}
$sql .= ' ORDER BY p.name';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$categories = db()->query('SELECT * FROM product_categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    cart_add((int) $_POST['product_id'], (int) ($_POST['qty'] ?? 1));
    flash_set('success', 'Added to cart.');
    redirect('shop.php');
}

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Wedding Shop</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">
            <form class="filter-bar" method="get">
                <input type="search" name="q" placeholder="Search products…" value="<?= e($search) ?>" />
                <select name="category">
                    <option value="">All</option>
                    <?php foreach ($categories as $c) : ?>
                        <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn--secondary">Search</button>
            </form>
            <div class="cards cards--3">
                <?php foreach ($products as $p) : ?>
                    <article class="card card--listing">
                        <div class="card__img" style="<?= card_image_style($p['image'] ?? null, '', 'product') ?>"></div>
                        <div class="card__body">
                            <h3><?= e($p['name']) ?></h3>
                            <p class="card__meta"><?= e($p['category_name']) ?> · <?= e((string) $p['business_name']) ?></p>
                            <p class="card__price"><?= format_money($p['price']) ?></p>
                            <form method="post" class="inline-form card__actions">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>" />
                                <input type="number" name="qty" value="1" min="1" max="<?= (int) $p['stock'] ?>" class="qty-input" />
                                <button type="submit" name="add_to_cart" value="1" class="btn btn--primary btn--sm">Add to cart</button>
                                <a class="btn btn--ghost btn--sm btn-wishlist" href="<?= e(wishlist_add_url('product', (int) $p['id'], $returnTo)) ?>" title="Save to wishlist">♥ Wishlist</a>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
