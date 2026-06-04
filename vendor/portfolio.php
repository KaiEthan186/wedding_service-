<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Portfolio';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $vendor) {
    $title = trim((string) ($_POST['title'] ?? ''));
    $cat = $_POST['category'] ?? 'wedding';
    if ($title) {
        db()->prepare('INSERT INTO gallery (vendor_id, category, title, image_path) VALUES (?, ?, ?, ?)')
            ->execute([$vendor['id'], $cat, $title, 'assets/images/placeholder.jpg']);
        flash_set('success', 'Gallery item added.');
    }
    redirect('portfolio.php');
}

$items = [];
if ($vendor) {
    $g = db()->prepare('SELECT * FROM gallery WHERE vendor_id = ? ORDER BY sort_order');
    $g->execute([$vendor['id']]);
    $items = $g->fetchAll();
}

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1>Portfolio</h1>
    <form class="form" method="post" style="max-width:400px;">
        <label><span>Title</span><input type="text" name="title" required /></label>
        <label><span>Category</span>
            <select name="category" class="form-select">
                <option value="wedding">Wedding</option>
                <option value="decoration">Decoration</option>
                <option value="makeup">Makeup</option>
            </select>
        </label>
        <button type="submit" class="btn btn--primary">Add (placeholder image)</button>
    </form>
    <ul>
        <?php foreach ($items as $i) : ?>
            <li><?= e((string) $i['title']) ?> — <?= e($i['category']) ?></li>
        <?php endforeach; ?>
    </ul>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
