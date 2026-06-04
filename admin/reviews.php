<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Reviews';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = (int) ($_POST['review_id'] ?? 0);
    $approved = (int) ($_POST['is_approved'] ?? 0);
    db()->prepare('UPDATE reviews SET is_approved = ? WHERE id = ?')->execute([$approved, $rid]);
    redirect('reviews.php');
}

$reviews = db()->query(
    'SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC'
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Manage reviews</h1>
    <?php foreach ($reviews as $r) : ?>
        <article class="msg-card">
            <p>★ <?= (int) $r['rating'] ?> — <?= e((string) $r['comment']) ?> by <?= e($r['first_name']) ?></p>
            <form method="post" class="inline-form">
                <input type="hidden" name="review_id" value="<?= (int) $r['id'] ?>" />
                <input type="hidden" name="is_approved" value="<?= $r['is_approved'] ? '0' : '1' ?>" />
                <button type="submit" class="btn btn--ghost btn--sm"><?= $r['is_approved'] ? 'Hide' : 'Approve' ?></button>
            </form>
        </article>
    <?php endforeach; ?>
    <?php if (!$reviews) : ?><p>No reviews yet.</p><?php endif; ?>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
