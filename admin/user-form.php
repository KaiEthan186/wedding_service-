<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Edit user' : 'Add user';

$userRow = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'role' => 'customer',
    'is_active' => 1,
];

if ($isEdit) {
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash_set('error', 'User not found.');
        redirect('users.php');
    }
    $userRow = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim((string) ($_POST['first_name'] ?? ''));
    $last = trim((string) ($_POST['last_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $role = (string) ($_POST['role'] ?? 'customer');
    $active = (int) ($_POST['is_active'] ?? 1);
    $password = (string) ($_POST['password'] ?? '');
    $resetPassword = isset($_POST['reset_password']);

    if (!in_array($role, ['customer', 'vendor', 'admin'], true)) {
        flash_set('error', 'Invalid role.');
        redirect('user-form.php' . ($isEdit ? '?id=' . $id : ''));
    }

    if ($first === '' || $last === '' || $email === '') {
        flash_set('error', 'Name and email are required.');
        redirect('user-form.php' . ($isEdit ? '?id=' . $id : ''));
    }

    $pdo = db();

    if ($isEdit) {
        if ($userRow['role'] === 'admin' && $role !== 'admin') {
            flash_set('error', 'Cannot change admin role.');
            redirect('user-form.php?id=' . $id);
        }
        $pdo->prepare(
            'UPDATE users SET first_name=?, last_name=?, email=?, phone=?, role=?, is_active=? WHERE id=?'
        )->execute([$first, $last, $email, $phone ?: null, $role, $active, $id]);

        if ($resetPassword && $password !== '') {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        flash_set('success', 'User updated.');
        redirect('users.php');
    }

    if ($password === '') {
        flash_set('error', 'Password required for new users.');
        redirect('user-form.php');
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        flash_set('error', 'Email already in use.');
        redirect('user-form.php');
    }

    $pdo->prepare(
        'INSERT INTO users (role, first_name, last_name, email, password_hash, phone, is_active, email_verified_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    )->execute([
        $role,
        $first,
        $last,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $phone ?: null,
        $active,
    ]);
    $newId = (int) $pdo->lastInsertId();

    if ($role === 'vendor') {
        $business = trim((string) ($_POST['business_name'] ?? $first . ' ' . $last));
        $pdo->prepare(
            'INSERT INTO vendors (user_id, business_name, slug, is_approved) VALUES (?, ?, ?, 0)'
        )->execute([$newId, $business, slugify($business)]);
    }

    flash_set('success', 'User created.');
    redirect('users.php');
}

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1><?= $isEdit ? 'Edit user' : 'Add user' ?></h1>
    <form class="form panel" method="post" style="max-width:520px;">
        <label>First name <input type="text" name="first_name" value="<?= e($userRow['first_name']) ?>" required /></label>
        <label>Last name <input type="text" name="last_name" value="<?= e($userRow['last_name']) ?>" required /></label>
        <label>Email <input type="email" name="email" value="<?= e($userRow['email']) ?>" required /></label>
        <label>Phone <input type="text" name="phone" value="<?= e((string) ($userRow['phone'] ?? '')) ?>" /></label>
        <label>Role
            <select name="role" <?= ($userRow['role'] ?? '') === 'admin' ? 'disabled' : '' ?>>
                <?php foreach (['customer', 'vendor', 'admin'] as $r) : ?>
                    <option value="<?= e($r) ?>" <?= ($userRow['role'] ?? '') === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (($userRow['role'] ?? '') === 'admin') : ?>
                <input type="hidden" name="role" value="admin" />
            <?php endif; ?>
        </label>
        <label>Active
            <select name="is_active">
                <option value="1" <?= (int) ($userRow['is_active'] ?? 1) ? 'selected' : '' ?>>Yes</option>
                <option value="0" <?= !(int) ($userRow['is_active'] ?? 1) ? 'selected' : '' ?>>No</option>
            </select>
        </label>

        <?php if (!$isEdit) : ?>
            <label>Business name (vendors) <input type="text" name="business_name" placeholder="Only if role is vendor" /></label>
            <label>Password <input type="password" name="password" required minlength="6" /></label>
        <?php else : ?>
            <label><input type="checkbox" name="reset_password" value="1" /> Reset password</label>
            <label>New password <input type="password" name="password" minlength="6" /></label>
        <?php endif; ?>

        <div class="action-row">
            <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Save' : 'Create user' ?></button>
            <a href="users.php" class="btn btn--ghost">Cancel</a>
        </div>
    </form>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
