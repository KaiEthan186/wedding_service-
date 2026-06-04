<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Gallery';
$filter = trim((string) ($_GET['category'] ?? ''));

$sql = 'SELECT g.*, v.business_name FROM gallery g LEFT JOIN vendors v ON v.id = g.vendor_id WHERE 1=1';
$params = [];
if ($filter !== '') {
    $sql .= ' AND g.category = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY g.sort_order ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = ['wedding', 'decoration', 'dress', 'makeup', 'venue', 'other'];

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Wedding Gallery</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">
            <div class="filter-pills">
                <a class="pill<?= $filter === '' ? ' is-active' : '' ?>" href="gallery.php">All</a>
                <?php foreach ($categories as $cat) : ?>
                    <a class="pill<?= $filter === $cat ? ' is-active' : '' ?>" href="gallery.php?category=<?= e($cat) ?>"><?= e(ucfirst($cat)) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="gallery-grid" data-gallery>
                <?php foreach ($items as $item) : ?>
                    <figure class="gallery-grid__item">
                        <div class="gallery__tile" style="<?= card_image_style($item['image_path'] ?? null, '', 'gallery') ?>" role="img" aria-label="<?= e((string) $item['title']) ?>"></div>
                        <figcaption>
                            <strong><?= e((string) ($item['title'] ?? 'Untitled')) ?></strong>
                            <?php if ($item['business_name']) : ?> · <?= e($item['business_name']) ?><?php endif; ?>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
