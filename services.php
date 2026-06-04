<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Services';
$category = trim((string) ($_GET['category'] ?? ''));
$search = trim((string) ($_GET['q'] ?? ''));
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$currentUser = auth_user();
$returnTo = 'services.php' . (($_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');

$sql = 'SELECT s.*, c.name AS category_name, v.business_name, v.rating_avg
        FROM services s
        JOIN service_categories c ON c.id = s.category_id
        JOIN vendors v ON v.id = s.vendor_id
        WHERE s.status = "active" AND s.is_approved = 1 AND v.is_approved = 1';
$params = [];

if ($category !== '') {
    $sql .= ' AND c.slug = ?';
    $params[] = $category;
}
if ($search !== '') {
    $sql .= ' AND (s.title LIKE ? OR s.description LIKE ? OR v.business_name LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $sql .= ' AND s.price >= ?';
    $params[] = (float) $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= ' AND s.price <= ?';
    $params[] = (float) $maxPrice;
}
$sql .= ' ORDER BY s.is_featured DESC, s.price ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$categories = db()->query('SELECT * FROM service_categories ORDER BY sort_order')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar">
        <div class="shell"><h1>Wedding Vendors &amp; Services</h1></div>
    </div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">

            <form class="filter-bar" method="get" action="services.php">
                <input type="search" name="q" placeholder="Search services…" value="<?= e($search) ?>" />
                <select name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="min_price" placeholder="Min $" value="<?= e((string) $minPrice) ?>" min="0" step="50" />
                <input type="number" name="max_price" placeholder="Max $" value="<?= e((string) $maxPrice) ?>" min="0" step="50" />
                <button type="submit" class="btn btn--secondary">Filter</button>
            </form>

            <div class="cards cards--3">
                <?php if (!$services) : ?>
                    <p class="empty-state">No services match your filters.</p>
                <?php endif; ?>
                <?php foreach ($services as $svc) :
                    $bookUrl = 'booking.php?service=' . (int) $svc['id'] . '&back=' . rawurlencode('service-details.php?slug=' . $svc['slug']);
                    ?>
                    <article class="card card--listing">
                        <a href="service-details.php?slug=<?= e($svc['slug']) ?>" class="card__img-link">
                            <div class="card__img" style="<?= card_image_style($svc['image'] ?? null, '', 'service') ?>" aria-hidden="true"></div>
                        </a>
                        <div class="card__body">
                            <p class="card__meta"><?= e($svc['category_name']) ?> · <?= e($svc['business_name']) ?></p>
                            <h3><a href="service-details.php?slug=<?= e($svc['slug']) ?>"><?= e($svc['title']) ?></a></h3>
                            <p class="card__price"><?= format_money($svc['price']) ?></p>
                            <?php if ((float) $svc['rating_avg'] > 0) : ?>
                                <p class="card__rating">★ <?= e((string) $svc['rating_avg']) ?></p>
                            <?php endif; ?>
                            <div class="card__actions">
                                <a class="btn btn--primary btn--sm" href="<?= e($bookUrl) ?>">Book now</a>
                                <a class="btn btn--ghost btn--sm btn-wishlist" href="<?= e(wishlist_add_url('service', (int) $svc['id'], $returnTo)) ?>" title="Save to wishlist">♥ Wishlist</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
