<?php
declare(strict_types=1);

$config   = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Sign In';
$error     = '';
$loginRedirect = safe_return_url(trim((string) ($_GET['redirect'] ?? '')), '');

if (isset($_GET['registered'])) {
    flash_set('success', 'Account created! Sign in with your email and password.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $honeypot = trim((string) ($_POST['website'] ?? ''));

    if ($honeypot !== '') {
        redirect('login.php');
    }

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = attempt_login($email, $password);
        if ($result['ok']) {
            $redirect = safe_return_url(
                trim((string) ($_POST['redirect'] ?? $_GET['redirect'] ?? '')),
                dashboard_url_for_role($result['role'])
            );
            redirect($redirect);
        }
        $error = $result['error'];
    }
}

$hideNav = true;
require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="auth-page">
        <div class="auth-card">
            <a class="logo auth-logo" href="index.php"><?= e((string) $config['site_name']) ?></a>
            <h1 class="auth-card__title">Sign in</h1>
            <p class="auth-card__sub">Don't have an account? <a href="register.php" class="auth-link">Register free</a></p>
            <?php if ($error !== '') : ?>
                <p class="banner banner--error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>
            <form class="form auth-form" method="post" action="login.php">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
                <?php if ($loginRedirect !== '') : ?>
                    <input type="hidden" name="redirect" value="<?= e($loginRedirect) ?>" />
                <?php endif; ?>
                <label><span>Email</span><input type="email" name="email" required value="<?= e((string) ($_POST['email'] ?? '')) ?>" /></label>
                <label><span>Password</span><input type="password" name="password" required /></label>
                <p class="auth-form__meta"><a href="forgot-password.php" class="auth-link auth-link--small">Forgot password?</a></p>
                <button type="submit" class="btn btn--primary btn--block btn--lg">Sign in</button>
            </form>
            <p class="auth-hint--block">Demo: admin@wedding.com / customer@wedding.com / vendor@wedding.com — password: <strong>password</strong></p>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
