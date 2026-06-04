<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';

$featuredVendors = [];
$blogPosts = [];
$packages = [];

try {
    $featuredVendors = db()->query(
        'SELECT business_name, slug, rating_avg, description, logo FROM vendors WHERE is_approved = 1 ORDER BY rating_avg DESC LIMIT 6'
    )->fetchAll();
    $blogPosts = db()->query(
        'SELECT title, slug, excerpt, published_at FROM blogs WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3'
    )->fetchAll();
    $packages = db()->query(
        'SELECT id, name, slug, price, included_services, is_featured FROM packages WHERE status = "active" ORDER BY price ASC LIMIT 4'
    )->fetchAll();
} catch (Throwable $e) {
    // DB not ready
}

$categories = [
    ['label' => 'Photography', 'sub' => 'Photographers', 'slug' => 'photography', 'img' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80'],
    ['label' => 'Bridal Makeup', 'sub' => 'Makeup Artists', 'slug' => 'makeup', 'img' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=600&q=80'],
    ['label' => 'Decoration', 'sub' => 'Floral & Decor', 'slug' => 'decoration', 'img' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600&q=80'],
    ['label' => 'Wedding Dress', 'sub' => 'Dresses & Accessories', 'slug' => 'dress', 'img' => 'https://i.pinimg.com/736x/cf/a8/24/cfa82434b5aceda2ac197a5809a14656.jpg'],
    ['label' => 'Invitations', 'sub' => 'Cards & Stationery', 'slug' => 'invitation', 'img' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=600&q=80'],
    ['label' => 'Catering', 'sub' => 'Food & Banquets', 'slug' => 'catering', 'img' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=600&q=80'],
];

$quickLinks = [
    ['icon' => '🏛', 'label' => 'Venues', 'url' => 'services.php?category=event-hall'],
    ['icon' => '👗', 'label' => 'Dresses', 'url' => 'shop.php?category=dresses'],
    ['icon' => '💄', 'label' => 'Makeup', 'url' => 'services.php?category=makeup'],
    ['icon' => '💐', 'label' => 'Florists', 'url' => 'services.php?category=decoration'],
    ['icon' => '✉️', 'label' => 'Invitations', 'url' => 'services.php?category=invitation'],
    ['icon' => '📷', 'label' => 'Photographers', 'url' => 'services.php?category=photography'],
];

require __DIR__ . '/includes/header.php';
?>

    <main id="main">
        <section class="hero-banner" aria-labelledby="hero-title">
            <div>
                <h1 id="hero-title"><?= e((string) $config['hero_title']) ?></h1>
                <p><?= e((string) $config['hero_subtitle']) ?></p>
                <a class="btn btn--light btn--lg" href="services.php">Find wedding vendors</a>
            </div>
        </section>

        <div class="shell">
            <div class="cat-grid">
                <?php foreach ($categories as $cat) : ?>
                    <a class="cat-tile" href="services.php?category=<?= e($cat['slug']) ?>">
                       <div class="cat-tile__img" style="background-image:url('<?= e($cat['img']) ?>')"></div>
                        <div class="cat-tile__overlay">
                            <p class="cat-tile__sub"><?= e($cat['sub']) ?></p>
                            <h2 class="cat-tile__label"><?= e($cat['label']) ?></h2>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <section class="section">
            <div class="shell">
                <header class="section__head">
                    <h2>What are you looking for?</h2>
                </header>
                <div class="quick-links">
                    <?php foreach ($quickLinks as $link) : ?>
                        <a class="quick-link" href="<?= e($link['url']) ?>">
                            <div class="quick-link__icon" aria-hidden="true"><?= $link['icon'] ?></div>
                            <span><?= e($link['label']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if ($blogPosts) : ?>
        <section class="section section--gray">
            <div class="shell">
                <header class="section__head">
                    <h2>Wedding Tips &amp; Advice</h2>
                    <p class="section__lead">Planning ideas, trends, and inspiration for your celebration.</p>
                </header>
                <div class="cards cards--3">
                    <?php foreach ($blogPosts as $post) : ?>
                        <article class="card">
                        <div class="card__img" style="background-image:url('https://i.pinimg.com/736x/b4/16/23/b416237b2f9d178dfd12465c1bb88e4e.jpg')"></div>
                            <div class="card__body">
                                <h3><a href="blog.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                                <p><?= e((string) $post['excerpt']) ?></p>
                                <a class="read-more" href="blog.php?slug=<?= e($post['slug']) ?>">Read More &raquo;</a>
                            </div>
                            
                        </article>

                        <article class="card">
                        <div class="card__img" style="background-image:url('https://i.pinimg.com/736x/b4/16/23/b416237b2f9d178dfd12465c1bb88e4e.jpg')"></div>
                            <div class="card__body">
                                <h3><a href="blog.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
                                <p><?= e((string) $post['excerpt']) ?></p>
                                <a class="read-more" href="blog.php?slug=<?= e($post['slug']) ?>">Read More &raquo;</a>
                            </div>
                            
                        </article>
                    <?php endforeach; ?>
                </div>

                
                <p style="text-align:center;margin-top:1.5rem;">
                    <a class="btn btn--outline" href="blog.php">View all articles</a>
                </p>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($featuredVendors) : ?>
        <section class="section">
            <div class="shell">
                <header class="section__head">
                    <h2>Featured wedding vendors</h2>
                </header>
                <div class="cards cards--3">
                    <?php foreach ($featuredVendors as $v) : ?>
                        <article class="card">
                            <div class="card__img" style="<?= card_image_style($v['logo'] ?? null, '', 'vendor') ?>"></div>
                            <div class="card__body">
                                <h3><a href="vendors.php#<?= e($v['slug']) ?>"><?= e($v['business_name']) ?></a></h3>
                                <p><?= e(substr((string) ($v['description'] ?? ''), 0, 100)) ?>…</p>
                                <p class="card__rating">★ <?= e((string) $v['rating_avg']) ?></p>
                                <a class="read-more" href="services.php?q=<?= urlencode($v['business_name']) ?>">View services &raquo;</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <p style="text-align:center;margin-top:1.5rem;">
                    <a class="btn btn--outline" href="vendors.php">Explore more vendors</a>
                </p>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($packages) : ?>
        <section class="section section--gray">
            <div class="shell">
                <header class="section__head">
                    <h2>Wedding packages</h2>
                    <p class="section__lead">Complete packages with bundled services for every budget.</p>
                </header>
                <div class="cards cards--3 cards--pricing">
                    <?php foreach ($packages as $pkg) : ?>
                        <article class="price-card<?= $pkg['is_featured'] ? ' price-card--featured' : '' ?>">
                            <?php if ($pkg['is_featured']) : ?><p class="price-card__ribbon">Popular</p><?php endif; ?>
                            <h3><?= e($pkg['name']) ?></h3>
                            <p class="price-card__price"><?= format_money($pkg['price']) ?></p>
                            <ul>
                                <?php foreach (array_slice(explode(',', (string) $pkg['included_services']), 0, 4) as $item) : ?>
                                    <li><?= e(trim($item)) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a class="btn btn--primary btn--block" href="booking.php?package=<?= (int) $pkg['id'] ?>&amp;back=<?= rawurlencode('index.php') ?>">Enquire</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="section">
            <div class="shell">
                <div class="cta-banner">
                    <h3>List your business for free!</h3>
                    <p>We offer free listings to wedding vendors — photography, makeup, catering, venues, and more.</p>
                    <a class="btn btn--primary" href="register.php">Join now</a>
                </div>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
