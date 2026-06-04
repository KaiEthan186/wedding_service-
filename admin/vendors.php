<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Manage Vendors';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid = (int) ($_POST['vendor_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'approve');
    if ($action === 'approval') {
        $approved = (int) ($_POST['is_approved'] ?? 0);
        db()->prepare('UPDATE vendors SET is_approved = ? WHERE id = ?')->execute([$approved, $vid]);
    } elseif ($action === 'suspend') {
        db()->prepare('UPDATE users SET is_active = 0 WHERE id = (SELECT user_id FROM vendors WHERE id = ?)')->execute([$vid]);
    } elseif ($action === 'activate') {
        db()->prepare('UPDATE users SET is_active = 1 WHERE id = (SELECT user_id FROM vendors WHERE id = ?)')->execute([$vid]);
    }
    flash_set('success', 'Vendor updated.');
    redirect('vendors.php');
}

$vendors = db()->query(
    'SELECT v.*, u.email, u.is_active FROM vendors v JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC'
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Manage vendors</h1>
    <table class="data-table">
        <thead><tr><th>Business</th><th>Email</th><th>Location</th><th>Approved</th><th>Account</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($vendors as $v) : ?>
            <tr>
                <td><?= e($v['business_name']) ?></td>
                <td><?= e($v['email']) ?></td>
                <td><?= e((string) ($v['location'] ?? '')) ?></td>
                <td><?= $v['is_approved'] ? 'Yes' : 'Pending' ?></td>
                <td><?= (int) $v['is_active'] === 1 ? 'Active' : 'Suspended' ?></td>
                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="approval" />
                        <input type="hidden" name="vendor_id" value="<?= (int) $v['id'] ?>" />
                        <input type="hidden" name="is_approved" value="<?= $v['is_approved'] ? '0' : '1' ?>" />
                        <button type="submit" class="btn btn--primary btn--sm"><?= $v['is_approved'] ? 'Revoke' : 'Approve' ?></button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="<?= (int) $v['is_active'] === 1 ? 'suspend' : 'activate' ?>" />
                        <input type="hidden" name="vendor_id" value="<?= (int) $v['id'] ?>" />
                        <button type="submit" class="btn btn--ghost btn--sm"><?= (int) $v['is_active'] === 1 ? 'Suspend' : 'Activate' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
