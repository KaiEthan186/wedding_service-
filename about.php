<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
$pageTitle = 'About Us';
require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>About Us</h1></div></div>

    <main id="main" class="section" style="padding-top:0;">
        <div class="shell prose">
            <h2 style="font-family:var(--font-display);margin-top:0;"><?= e((string) $config['site_name']) ?></h2>
            <p>We connect couples with trusted wedding vendors — photography, styling, catering, venues, and more — in one beautiful marketplace.</p>
            <p>Whether you need a single service or a complete package, our platform helps you compare, book, and manage your wedding planning online.</p>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
