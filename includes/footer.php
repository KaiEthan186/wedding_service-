<?php
declare(strict_types=1);

/** @var array<string, mixed> $config */
$config = $config ?? [];
$base = $base ?? '';
$siteName = e((string) ($config['site_name'] ?? 'Weddings'));
$year = (int) date('Y');
$email = e((string) ($config['email'] ?? ''));
$phone = e((string) ($config['phone'] ?? ''));
$ig = e((string) ($config['social']['instagram'] ?? '#'));
$fb = e((string) ($config['social']['facebook'] ?? '#'));
$pin = e((string) ($config['social']['pinterest'] ?? '#'));
?>
    <footer class="site-footer">
        <div class="shell footer__grid">
            <div>
                <p class="footer__brand"><?= $siteName ?></p>
                <p class="footer__muted">A comprehensive directory of wedding vendors — venues, photographers, florists, makeup artists, and more to help you plan your special day.</p>
            </div>
            <div>
                <p class="footer__label">Vendors</p>
                <ul class="footer__links">
                    <li><a href="<?= $base ?>services.php?category=photography">Photographers</a></li>
                    <li><a href="<?= $base ?>services.php?category=makeup">Makeup Artists</a></li>
                    <li><a href="<?= $base ?>services.php?category=decoration">Decoration</a></li>
                    <li><a href="<?= $base ?>services.php?category=catering">Catering</a></li>
                    <li><a href="<?= $base ?>services.php?category=event-hall">Event Halls</a></li>
                </ul>
            </div>
            <div>
                <p class="footer__label">Explore</p>
                <ul class="footer__links">
                    <li><a href="<?= $base ?>packages.php">Packages</a></li>
                    <li><a href="<?= $base ?>shop.php">Shop</a></li>
                    <li><a href="<?= $base ?>gallery.php">Gallery</a></li>
                    <li><a href="<?= $base ?>blog.php">Tips &amp; Advice</a></li>
                    <li><a href="<?= $base ?>booking.php">Book Now</a></li>
                </ul>
            </div>
            <div>
                <p class="footer__label">Contact</p>
                <p class="footer__muted"><a href="mailto:<?= $email ?>"><?= $email ?></a></p>
                <p class="footer__muted"><a href="tel:<?= preg_replace('/\D+/', '', $phone) ?>"><?= $phone ?></a></p>
                <p class="footer__label" style="margin-top:1rem;">Follow</p>
                <ul class="footer__social">
                    <li><a href="<?= $ig ?>" target="_blank" rel="noopener noreferrer">Instagram</a></li>
                    <li><a href="<?= $fb ?>" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                    <li><a href="<?= $pin ?>" target="_blank" rel="noopener noreferrer">Pinterest</a></li>
                </ul>
            </div>
        </div>
        <div class="shell footer__bottom">
            <p>Copyright &copy; <?= $year ?> <?= $siteName ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="<?= $base ?>js/wedding.js?v=4" defer></script>
</body>
</html>
