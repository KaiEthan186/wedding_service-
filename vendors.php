<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Vendors';
$vendors = db()->query(
    'SELECT v.*, u.email FROM vendors v JOIN users u ON u.id = v.user_id WHERE v.is_approved = 1 ORDER BY v.rating_avg DESC'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Wedding Vendors</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">
            <div class="cards cards--3">
                <?php foreach ($vendors as $v) : ?>
                    <article class="card" id="<?= e($v['slug']) ?>">
                        <div class="card__img" style="<?= card_image_style($v['logo'] ?? null, '', 'vendor') ?>"></div>
                        <div class="card__body">
                            <h3><?= e($v['business_name']) ?></h3>
                            <?php if ($v['location']) : ?><p class="card__meta"><?= e($v['location']) ?></p><?php endif; ?>
                            <p><?= e((string) $v['description']) ?></p>
                            <p class="card__rating">★ <?= e((string) $v['rating_avg']) ?> (<?= (int) $v['rating_count'] ?> reviews)</p>
                            <a href="services.php?q=<?= urlencode($v['business_name']) ?>" class="read-more">View services &raquo;</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
