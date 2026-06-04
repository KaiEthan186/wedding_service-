<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Forgot Password';
$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Local project: show success without sending mail
        $sent = true;
    }
}

require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="section">
        <div class="shell" style="max-width:480px;">
            <h1>Forgot password</h1>
            <?php if ($sent) : ?>
                <p class="banner banner--success">If that email exists, reset instructions would be sent. (Email not configured in local demo.)</p>
            <?php else : ?>
                <?php if ($error) : ?><p class="banner banner--error"><?= e($error) ?></p><?php endif; ?>
                <form class="form" method="post">
                    <label><span>Email</span><input type="email" name="email" required /></label>
                    <button type="submit" class="btn btn--primary">Send reset link</button>
                </form>
            <?php endif; ?>
            <p><a href="login.php">← Back to sign in</a></p>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
