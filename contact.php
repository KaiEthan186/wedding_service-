<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us';
$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $honeypot = trim((string) ($_POST['website'] ?? ''));

    if ($honeypot !== '') {
        redirect('contact.php?sent=1');
    }
    if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please fill in name, valid email, and message.';
    } else {
        $stmt = db()->prepare(
            'INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $phone ?: null, $subject ?: null, $message]);
        redirect('contact.php?sent=1');
    }
}

if (isset($_GET['sent'])) {
    $sent = true;
}

require __DIR__ . '/includes/header.php';
?>

    <div class="page-title-bar"><div class="shell"><h1>Contact Us</h1></div></div>

    <main id="main" class="section section--contact" style="padding-top:0;">
        <div class="shell contact">
            <div class="contact__intro">
                <ul class="contact__facts">
                    <li><?= e((string) $config['phone']) ?></li>
                    <li><?= e((string) $config['email']) ?></li>
                    <li><?= e((string) $config['address']) ?></li>
                </ul>
                <div class="map-placeholder" aria-label="Map placeholder">Map — add Google Maps embed in production</div>
            </div>
            <div class="contact__panel">
                <?php if ($sent) : ?>
                    <p class="banner banner--success">Thank you! We received your message.</p>
                <?php else : ?>
                    <?php if ($error) : ?><p class="banner banner--error"><?= e($error) ?></p><?php endif; ?>
                    <form class="form" method="post">
                        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
                        <div class="form__row">
                            <label><span>Name</span><input type="text" name="name" required /></label>
                            <label><span>Email</span><input type="email" name="email" required /></label>
                        </div>
                        <label><span>Phone</span><input type="tel" name="phone" /></label>
                        <label><span>Subject</span><input type="text" name="subject" /></label>
                        <label><span>Message</span><textarea name="message" rows="5" required></textarea></label>
                        <button type="submit" class="btn btn--primary btn--block">Send message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
