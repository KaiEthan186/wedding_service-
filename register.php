<?php
declare(strict_types=1);

$config   = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Register';
$error     = '';
$post      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName  = trim((string) ($_POST['last_name'] ?? ''));
    $email     = trim((string) ($_POST['email'] ?? ''));
    $password  = (string) ($_POST['password'] ?? '');
    $confirm   = (string) ($_POST['confirm'] ?? '');
    $role      = ($_POST['role'] ?? 'customer') === 'vendor' ? 'vendor' : 'customer';
    $business  = trim((string) ($_POST['business_name'] ?? ''));
    $honeypot  = trim((string) ($_POST['website'] ?? ''));
    $terms     = isset($_POST['terms']);
    $post = compact('firstName', 'lastName', 'email', 'role', 'business');

    if ($honeypot !== '') {
        redirect('register.php');
    }
    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms of Service.';
    } elseif ($role === 'vendor' && $business === '') {
        $error = 'Business name is required for vendor registration.';
    } else {
        $result = register_user($firstName, $lastName, $email, $password, $role, $business ?: null);
        if ($result['ok']) {
            redirect('login.php?registered=1');
        }
        $error = $result['error'];
    }
}

$hideNav = true;
require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="auth-page">
        <div class="auth-card" style="max-width:480px;">
            <a class="logo auth-logo" href="index.php"><?= e((string) $config['site_name']) ?></a>
            <h1 class="auth-card__title">Create account</h1>
            <p class="auth-card__sub">Already registered? <a href="login.php" class="auth-link">Sign in</a></p>
            <?php if ($error !== '') : ?>
                <p class="banner banner--error" role="alert"><?= e($error) ?></p>
            <?php endif; ?>
            <form class="form auth-form" method="post" action="register.php">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true" />
                <div class="form__row">
                    <label><span>First name</span><input type="text" name="first_name" required value="<?= e((string) ($post['firstName'] ?? '')) ?>" /></label>
                    <label><span>Last name</span><input type="text" name="last_name" required value="<?= e((string) ($post['lastName'] ?? '')) ?>" /></label>
                </div>
                <label><span>Email</span><input type="email" name="email" required value="<?= e((string) ($post['email'] ?? '')) ?>" /></label>
                <label><span>Account type</span>
                    <select name="role" id="reg-role" class="form-select">
                        <option value="customer" <?= ($post['role'] ?? '') === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="vendor" <?= ($post['role'] ?? '') === 'vendor' ? 'selected' : '' ?>>Vendor / Business</option>
                    </select>
                </label>
                <label id="business-wrap" class="<?= ($post['role'] ?? '') === 'vendor' ? '' : 'is-hidden' ?>">
                    <span>Business name</span>
                    <input type="text" name="business_name" value="<?= e((string) ($post['business'] ?? '')) ?>" />
                </label>
                <label><span>Password</span><input type="password" name="password" required minlength="8" /></label>
                <label><span>Confirm password</span><input type="password" name="confirm" required /></label>
                <label class="checkbox-label"><input type="checkbox" name="terms" /> I agree to the Terms of Service</label>
                <button type="submit" class="btn btn--primary btn--block btn--lg">Create account</button>
            </form>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
