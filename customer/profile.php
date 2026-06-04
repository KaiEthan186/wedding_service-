<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('customer');
$pageTitle = 'My Profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $fn = trim((string) ($_POST['first_name'] ?? ''));
    $ln = trim((string) ($_POST['last_name'] ?? ''));
    if ($fn && $ln) {
        db()->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?')
            ->execute([$fn, $ln, $phone ?: null, $user['id']]);
        flash_set('success', 'Profile updated.');
        redirect('profile.php');
    }
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

require dirname(__DIR__) . '/includes/customer_header.php';
?>

    <h1>My profile</h1>
    <form class="form" method="post" style="max-width:400px;">
        <label><span>First name</span><input type="text" name="first_name" value="<?= e($profile['first_name']) ?>" required /></label>
        <label><span>Last name</span><input type="text" name="last_name" value="<?= e($profile['last_name']) ?>" required /></label>
        <label><span>Email</span><input type="email" value="<?= e($profile['email']) ?>" disabled /></label>
        <label><span>Phone</span><input type="tel" name="phone" value="<?= e((string) ($profile['phone'] ?? '')) ?>" /></label>
        <button type="submit" class="btn btn--primary">Save</button>
    </form>

<?php require dirname(__DIR__) . '/includes/customer_footer.php'; ?>
