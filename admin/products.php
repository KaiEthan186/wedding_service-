<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Manage Products';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['product_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($id > 0) {
        if ($action === 'approve') {
            db()->prepare('UPDATE products SET is_approved = 1 WHERE id = ?')->execute([$id]);
            flash_set('success', 'Product approved.');
        } elseif ($action === 'unapprove') {
            db()->prepare('UPDATE products SET is_approved = 0 WHERE id = ?')->execute([$id]);
            flash_set('success', 'Product moved back to pending.');
        } elseif ($action === 'toggle') {
            db()->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
            flash_set('success', 'Product visibility updated.');
        } elseif ($action === 'delete') {
            db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            flash_set('success', 'Product removed.');
        }
    }
    redirect('products.php');
}

$products = db()->query(
    'SELECT p.*, c.name AS category_name, v.business_name
     FROM products p
     JOIN product_categories c ON c.id = p.category_id
     JOIN vendors v ON v.id = p.vendor_id
     ORDER BY p.created_at DESC'
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>
<h1>Manage products</h1>
<table class="data-table">
    <thead>
    <tr><th>Name</th><th>Vendor</th><th>Category</th><th>Price</th><th>Stock</th><th>Approval</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p) : ?>
        <tr>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['business_name']) ?></td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= format_money($p['price']) ?></td>
            <td><?= (int) $p['stock'] ?></td>
            <td><?= (int) $p['is_approved'] === 1 ? 'Approved' : 'Pending' ?></td>
            <td><?= (int) $p['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
            <td class="table-actions">
                <form method="post" class="inline-form">
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>" />
                    <input type="hidden" name="action" value="<?= (int) $p['is_approved'] === 1 ? 'unapprove' : 'approve' ?>" />
                    <button type="submit" class="btn btn--primary btn--sm"><?= (int) $p['is_approved'] === 1 ? 'Unapprove' : 'Approve' ?></button>
                </form>
                <form method="post" class="inline-form">
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>" />
                    <input type="hidden" name="action" value="toggle" />
                    <button type="submit" class="btn btn--ghost btn--sm"><?= (int) $p['is_active'] === 1 ? 'Hide' : 'Activate' ?></button>
                </form>
                <form method="post" class="inline-form" onsubmit="return confirm('Delete product?');">
                    <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <button type="submit" class="btn btn--ghost btn--sm">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
