<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'My Services';

$services = [];
if ($vendor) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['service_id'] ?? 0);
        $action = (string) ($_POST['action'] ?? '');
        if ($id > 0) {
            if ($action === 'toggle') {
                db()->prepare(
                    'UPDATE services SET status = CASE WHEN status = "active" THEN "inactive" ELSE "active" END WHERE id = ? AND vendor_id = ?'
                )->execute([$id, (int) $vendor['id']]);
                flash_set('success', 'Service status updated.');
            } elseif ($action === 'delete') {
                db()->prepare('DELETE FROM services WHERE id = ? AND vendor_id = ?')->execute([$id, (int) $vendor['id']]);
                flash_set('success', 'Service deleted.');
            }
            redirect('services.php');
        }
    }
    $s = db()->prepare('SELECT s.*, c.name AS category_name FROM services s JOIN service_categories c ON c.id = s.category_id WHERE s.vendor_id = ?');
    $s->execute([$vendor['id']]);
    $services = $s->fetchAll();
}

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1>My services</h1>
    <p><a href="add-service.php" class="btn btn--primary btn--sm">+ Add service</a></p>
    <table class="data-table">
        <thead><tr><th>Title</th><th>Category</th><th>Price</th><th>Status</th><th>Approved</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($services as $s) : ?>
            <tr>
                <td><?= e($s['title']) ?></td>
                <td><?= e($s['category_name']) ?></td>
                <td><?= format_money($s['price']) ?></td>
                <td><?= e($s['status']) ?></td>
                <td><?= $s['is_approved'] ? 'Yes' : 'Pending' ?></td>
                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="service_id" value="<?= (int) $s['id'] ?>" />
                        <input type="hidden" name="action" value="toggle" />
                        <button type="submit" class="btn btn--ghost btn--sm"><?= $s['status'] === 'active' ? 'Pause' : 'Activate' ?></button>
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

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
