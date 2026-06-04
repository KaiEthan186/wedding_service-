<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$pageTitle = 'Blog';

if ($slug !== '') {
    $stmt = db()->prepare('SELECT * FROM blogs WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    if (!$post) {
        flash_set('error', 'Article not found.');
        redirect('blog.php');
    }
    $pageTitle = $post['title'];
    require __DIR__ . '/includes/header.php';
    ?>
    <main id="main" class="section">
        <div class="shell prose">
            <p class="eyebrow">Blog</p>
            <h1><?= e($post['title']) ?></h1>
            <div class="blog-content"><?= $post['content'] ?></div>
            <p><a href="blog.php">← All articles</a></p>
        </div>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$posts = db()->query('SELECT id, title, slug, excerpt, published_at FROM blogs WHERE is_published = 1 ORDER BY published_at DESC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Wedding Tips &amp; Advice</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell">
            <div class="cards cards--3">
                <?php foreach ($posts as $p) : ?>
                    <article class="card">
                        <div class="card__img card__thumb--placeholder"></div>
                        <div class="card__body">
                            <h3><a href="blog.php?slug=<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
                            <p><?= e((string) $p['excerpt']) ?></p>
                            <p class="text-muted"><?= e(date('M j, Y', strtotime($p['published_at']))) ?></p>
                            <a class="read-more" href="blog.php?slug=<?= e($p['slug']) ?>">Read More &raquo;</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
