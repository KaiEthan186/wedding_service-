<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/** @var array<string, mixed> $config */
$config = $config ?? [];
$siteName    = e((string) ($config['site_name'] ?? 'Weddings'));
$pageTitle   = e((string) ($pageTitle ?? $config['site_name'] ?? 'Weddings'));
$currentUser = auth_user();
$cartCount   = cart_count();
$base        = $base ?? '';
$hideNav     = $hideNav ?? false;
$searchQ     = isset($_GET['q']) ? e((string) $_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Find wedding venues, vendors, dresses, photographers and more for your perfect day." />
    <title><?= $pageTitle ?> — <?= $siteName ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= $base ?>css/wedding.css?v=6" />
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header" data-header>
        <div class="shell header-shell">
            <div class="header-top">
                <div class="header-brand">
                    <a class="logo" href="<?= $base ?>index.php">
                        <span class="logo__mark" aria-hidden="true">✦</span>
                        <span class="logo__text">ZALA</span>
                    </a>
                </div>

                <?php if (!$hideNav) : ?>
                    <form class="header-search" action="<?= $base ?>services.php" method="get" role="search">
                        <span class="header-search__icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
                        </span>
                        <input
                            type="search"
                            name="q"
                            class="header-search__input"
                            placeholder="Search vendors, services &amp; products…"
                            aria-label="Search"
                            value="<?= $searchQ ?>"
                        />
                        <button type="submit" class="header-search__submit" aria-label="Search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
                        </button>
                    </form>

                    <div class="header-actions">
                        <?php if ($currentUser && $currentUser['role'] === 'customer') : ?>
                            <a href="<?= $base ?>customer/wishlist.php" class="header-icon-btn" aria-label="Wishlist" title="Wishlist">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7-4.5-9.5-9A5.5 5.5 0 0 1 12 6a5.5 5.5 0 0 1 9.5 6c-2.5 4.5-9.5 9-9.5 9z"/></svg>
                            </a>
                        <?php endif; ?>

                        <a href="<?= $base ?>cart.php" class="header-icon-btn header-cart" aria-label="Shopping cart">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M6 6h15l-1.5 9h-12L6 6z"/><path d="M6 6L5 3H2"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>
                            </svg>
                            <?php if ($cartCount > 0) : ?>
                                <span class="header-cart__badge"><?= $cartCount ?></span>
                            <?php endif; ?>
                        </a>

                        <?php if ($currentUser) : ?>
                            <div class="profile-menu">
                                <button class="profile-btn" type="button" aria-label="Account menu" aria-expanded="false" data-profile-toggle>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>
                                    </svg>
                                </button>
                                <div class="profile-dropdown" data-profile-menu>
                                    <p class="profile-dropdown__user"><?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></p>
                                    <a href="<?= $base . dashboard_url_for_role($currentUser['role']) ?>">Dashboard</a>
                                    <?php if ($currentUser['role'] === 'customer') : ?>
                                        <a href="<?= $base ?>customer/bookings.php">My Bookings</a>
                                        <a href="<?= $base ?>customer/wishlist.php">Wishlist</a>
                                        <a href="<?= $base ?>customer/profile.php">Profile</a>
                                    <?php endif; ?>
                                    <a href="<?= $base ?>logout.php" class="profile-dropdown__logout">Logout</a>
                                </div>
                            </div>
                        <?php else : ?>
                            <a href="<?= $base ?>login.php" class="header-login">Login</a>
                            <a href="<?= $base ?>register.php" class="header-register">Register</a>
                        <?php endif; ?>

                        <button type="button" class="nav-toggle" aria-label="Open menu" aria-expanded="false" data-nav-toggle>
                            <span class="nav-toggle__bar"></span>
                            <span class="nav-toggle__bar"></span>
                            <span class="nav-toggle__bar"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!$hideNav) : ?>
                <nav class="header-nav" aria-label="Primary" data-nav>
                    <a href="<?= $base ?>index.php">Home</a>
                    <a href="<?= $base ?>services.php">Services</a>
                    <a href="<?= $base ?>packages.php">Packages</a>
                    <a href="<?= $base ?>vendors.php">Vendors</a>
                    <a href="<?= $base ?>gallery.php">Gallery</a>
                    <a href="<?= $base ?>shop.php">Shop</a>
                    <a href="<?= $base ?>blog.php">Tips &amp; Advice</a>
                    <a href="<?= $base ?>about.php">About</a>
                    <a href="<?= $base ?>contact.php">Contact</a>
                </nav>
            <?php endif; ?>
        </div>
    </header>

    <?php
    $flashSuccess = flash_get('success');
    $flashError = flash_get('error');
    if ($flashSuccess !== '' || $flashError !== '') :
        ?>
    <div class="shell flash-bar">
        <?php if ($flashSuccess !== '') : ?>
            <p class="banner banner--success" role="status"><?= e($flashSuccess) ?></p>
        <?php endif; ?>
        <?php if ($flashError !== '') : ?>
            <p class="banner banner--error" role="alert"><?= e($flashError) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
