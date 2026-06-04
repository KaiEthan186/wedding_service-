<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Packages';
$packages = db()->query(
    'SELECT p.*, v.business_name
     FROM packages p
     JOIN vendors v ON v.id = p.vendor_id
     WHERE p.status = "active" AND v.is_approved = 1
     ORDER BY p.price ASC'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Wedding Packages</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">
            <div class="cards cards--3 cards--pricing">
                <?php foreach ($packages as $pkg) : ?>
                    <article class="price-card<?= $pkg['is_featured'] ? ' price-card--featured' : '' ?>">
                        <?php if ($pkg['is_featured']) : ?><p class="price-card__ribbon">Popular</p><?php endif; ?>
                        <?php if (!empty($pkg['image'])) : ?>
                            <div class="card__img price-card__img" style="<?= card_image_style($pkg['image'], '', 'service') ?>"></div>
                        <?php endif; ?>
                        <h3><?= e($pkg['name']) ?></h3>
                        <p class="card__meta">By <?= e((string) $pkg['business_name']) ?></p>
                        <p class="price-card__price"><?= format_money($pkg['price']) ?></p>
                        <?php if ($pkg['duration_days']) : ?>
                            <p class="text-muted"><?= (int) $pkg['duration_days'] ?> day(s)</p>
                        <?php endif; ?>
                        <p><?= e((string) $pkg['description']) ?></p>
                        <ul>
                            <?php foreach (explode(',', (string) $pkg['included_services']) as $item) : ?>
                                <li><?= e(trim($item)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a class="btn btn--secondary btn--block" href="booking.php?package=<?= (int) $pkg['id'] ?>&amp;back=<?= rawurlencode('packages.php') ?>">Book package</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
