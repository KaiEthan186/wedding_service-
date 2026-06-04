<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Manage Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'toggle');
    $uid = (int) ($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $uid > 0) {
        $u = db()->prepare('SELECT role FROM users WHERE id = ?');
        $u->execute([$uid]);
        $row = $u->fetch();
        if ($row && $row['role'] !== 'admin') {
            db()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            flash_set('success', 'User deleted.');
        } else {
            flash_set('error', 'Cannot delete this user.');
        }
    } elseif ($action === 'toggle') {
        $active = (int) ($_POST['is_active'] ?? 0);
        db()->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role != "admin"')->execute([$active, $uid]);
        flash_set('success', 'User updated.');
    }
    redirect('users.php');
}

$roleFilter = trim((string) ($_GET['role'] ?? ''));
$search = trim((string) ($_GET['q'] ?? ''));

$sql = 'SELECT id, role, first_name, last_name, email, phone, is_active, created_at FROM users WHERE 1=1';
$params = [];
if ($roleFilter !== '' && in_array($roleFilter, ['customer', 'vendor', 'admin'], true)) {
    $sql .= ' AND role = ?';
    $params[] = $roleFilter;
}
if ($search !== '') {
    $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <div class="page-head">
        <h1>Manage users</h1>
        <a href="user-form.php" class="btn btn--primary btn--sm">+ Add user</a>
    </div>

    <form method="get" class="inline-form filter-bar">
        <input type="search" name="q" placeholder="Search name or email" value="<?= e($search) ?>" />
        <select name="role">
            <option value="">All roles</option>
            <?php foreach (['customer', 'vendor', 'admin'] as $r) : ?>
                <option value="<?= e($r) ?>" <?= $roleFilter === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn--ghost btn--sm">Filter</button>
    </form>

    <table class="data-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u) : ?>
            <tr>
                <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge"><?= e($u['role']) ?></span></td>
                <td><?= $u['is_active'] ? 'Yes' : 'No' ?></td>
                <td class="table-actions">
                    <a href="user-form.php?id=<?= (int) $u['id'] ?>" class="btn btn--ghost btn--sm">Edit</a>
                    <?php if ($u['role'] !== 'admin') : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>" />
                        <input type="hidden" name="action" value="toggle" />
                        <input type="hidden" name="is_active" value="<?= $u['is_active'] ? '0' : '1' ?>" />
                        <button type="submit" class="btn btn--ghost btn--sm"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>" />
                        <input type="hidden" name="action" value="delete" />
                        <button type="submit" class="btn btn--ghost btn--sm">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
