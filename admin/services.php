<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Manage Services';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = (int) ($_POST['service_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'approve');

    if ($sid > 0) {
        if ($action === 'delete') {
            db()->prepare('DELETE FROM services WHERE id = ?')->execute([$sid]);
            flash_set('success', 'Service deleted.');
        } elseif ($action === 'feature') {
            $val = (int) ($_POST['is_featured'] ?? 0);
            db()->prepare('UPDATE services SET is_featured = ? WHERE id = ?')->execute([$val, $sid]);
            flash_set('success', 'Featured flag updated.');
        } elseif ($action === 'status') {
            $status = (string) ($_POST['status'] ?? 'draft');
            if (in_array($status, ['draft', 'active', 'inactive'], true)) {
                db()->prepare('UPDATE services SET status = ? WHERE id = ?')->execute([$status, $sid]);
                flash_set('success', 'Status updated.');
            }
        } else {
            $approved = (int) ($_POST['is_approved'] ?? 0);
            $status = $approved ? 'active' : 'draft';
            db()->prepare('UPDATE services SET is_approved = ?, status = ? WHERE id = ?')->execute([$approved, $status, $sid]);
            flash_set('success', 'Service updated.');
        }
    }
    redirect('services.php');
}

$services = db()->query(
    'SELECT s.*, v.business_name, c.name AS category_name FROM services s
     JOIN vendors v ON v.id = s.vendor_id JOIN service_categories c ON c.id = s.category_id
     ORDER BY s.created_at DESC'
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Manage services</h1>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Vendor</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($services as $s) : ?>
            <tr>
                <td><?= e($s['title']) ?><?= $s['is_featured'] ? ' <span class="badge badge--info">Featured</span>' : '' ?></td>
                <td><?= e($s['business_name']) ?></td>
                <td><?= e($s['category_name']) ?></td>
                <td><?= format_money($s['price']) ?></td>
                <td><?= e($s['status']) ?> / <?= $s['is_approved'] ? 'approved' : 'pending' ?></td>
                <td class="table-actions">
                    <form method="post" class="inline-form">
                        <input type="hidden" name="service_id" value="<?= (int) $s['id'] ?>" />
                        <input type="hidden" name="is_approved" value="<?= $s['is_approved'] ? '0' : '1' ?>" />
                        <button type="submit" class="btn btn--primary btn--sm"><?= $s['is_approved'] ? 'Unapprove' : 'Approve' ?></button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="service_id" value="<?= (int) $s['id'] ?>" />
                        <input type="hidden" name="action" value="feature" />
                        <input type="hidden" name="is_featured" value="<?= $s['is_featured'] ? '0' : '1' ?>" />
                        <button type="submit" class="btn btn--ghost btn--sm"><?= $s['is_featured'] ? 'Unfeature' : 'Feature' ?></button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="service_id" value="<?= (int) $s['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <select name="status">
                            <?php foreach (['active', 'draft', 'inactive'] as $st) : ?>
                                <option value="<?= e($st) ?>" <?= $s['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn--ghost btn--sm">Set</button>
                    </form>
                    <form method="post" class="inline-form" onsubmit="return confirm('Delete service?');">
                        <input type="hidden" name="service_id" value="<?= (int) $s['id'] ?>" />
                        <input type="hidden" name="action" value="delete" />
                        <button type="submit" class="btn btn--ghost btn--sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
