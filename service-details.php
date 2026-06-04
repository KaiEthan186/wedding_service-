<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
if ($slug === '') {
    redirect('services.php');
}

$stmt = db()->prepare(
    'SELECT s.*, c.name AS category_name, v.business_name, v.slug AS vendor_slug, v.id AS vendor_id
     FROM services s
     JOIN service_categories c ON c.id = s.category_id
     JOIN vendors v ON v.id = s.vendor_id
     WHERE s.slug = ? AND s.status = "active" LIMIT 1'
);
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    flash_set('error', 'Service not found.');
    redirect('services.php');
}

$pageTitle = $service['title'];
$returnTo = 'service-details.php?slug=' . rawurlencode($slug);
$bookUrl = 'booking.php?service=' . (int) $service['id'] . '&back=' . rawurlencode($returnTo);

$reviews = db()->prepare(
    'SELECT r.*, u.first_name, u.last_name FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.reviewable_type = "service" AND r.reviewable_id = ? AND r.is_approved = 1
     ORDER BY r.created_at DESC LIMIT 10'
);
$reviews->execute([(int) $service['id']]);
$reviewList = $reviews->fetchAll();

require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="section">
        <div class="shell">
            <p class="form-page__back">
                <a href="<?= e(back_url('services.php')) ?>" class="btn btn--ghost btn-back">← Back to services</a>
            </p>
            <div class="split">
            <div>
                <div class="detail-hero" style="<?= card_image_style($service['image'] ?? null, '', 'service') ?>"></div>
                <p class="eyebrow"><?= e($service['category_name']) ?></p>
                <h1><?= e($service['title']) ?></h1>
                <p class="price-lg"><?= format_money($service['price']) ?></p>
                <p>By <a href="vendors.php#<?= e($service['vendor_slug']) ?>"><?= e($service['business_name']) ?></a></p>
                <?php if ($service['location']) : ?><p>📍 <?= e($service['location']) ?></p><?php endif; ?>
                <p><?= nl2br(e((string) $service['description'])) ?></p>
                <div class="hero__actions card__actions">
                    <a class="btn btn--primary" href="<?= e($bookUrl) ?>">Book this service</a>
                    <a class="btn btn--ghost btn-wishlist" href="<?= e(wishlist_add_url('service', (int) $service['id'], $returnTo)) ?>">♥ Save to wishlist</a>
                </div>
            </div>
            <div class="detail-panel">
                <h2>Reviews</h2>
                <?php if (!$reviewList) : ?>
                    <p class="text-muted">No reviews yet.</p>
                <?php endif; ?>
                <?php foreach ($reviewList as $r) : ?>
                    <blockquote class="quote">
                        <p>★ <?= (int) $r['rating'] ?> — <?= e($r['comment'] ?? '') ?></p>
                        <footer>— <?= e($r['first_name'] . ' ' . $r['last_name']) ?></footer>
                    </blockquote>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
