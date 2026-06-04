<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'My Products';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

$categories = db()->query('SELECT * FROM product_categories ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'create');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        $toggle = db()->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ? AND vendor_id = ?');
        $toggle->execute([$id, (int) $vendor['id']]);
        flash_set('success', 'Product status updated.');
        redirect('products.php');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($name !== '' && $categoryId > 0 && $price > 0 && $stock >= 0) {
        $slug = slugify($name) . '-' . time();
        db()->prepare(
            'INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock, is_approved, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1)'
        )->execute([(int) $vendor['id'], $categoryId, $name, $slug, $description ?: null, $price, $stock]);
        flash_set('success', 'Product created and submitted for admin approval.');
        redirect('products.php');
    }
    flash_set('error', 'Please fill required fields.');
}

$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN product_categories c ON c.id = p.category_id
     WHERE p.vendor_id = ?
     ORDER BY p.created_at DESC'
);
$stmt->execute([(int) $vendor['id']]);
$products = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

<h1>My products</h1>
<form method="post" class="form" style="max-width:520px;">
    <label><span>Name</span><input type="text" name="name" required /></label>
    <label><span>Category</span>
        <select name="category_id" class="form-select" required>
            <?php foreach ($categories as $c) : ?>
                <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><span>Price</span><input type="number" min="0.01" step="0.01" name="price" required /></label>
    <label><span>Stock</span><input type="number" min="0" step="1" name="stock" required value="0" /></label>
    <label><span>Description</span><textarea name="description" rows="3"></textarea></label>
    <button class="btn btn--primary" type="submit">Create product</button>
</form>

<table class="data-table">
    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Approval</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($products as $p) : ?>
        <tr>
            <td><?= e($p['name']) ?></td>
            <td><?= e($p['category_name']) ?></td>
            <td><?= format_money($p['price']) ?></td>
            <td><?= (int) $p['stock'] ?></td>
            <td><?= (int) $p['is_approved'] === 1 ? 'Approved' : 'Pending' ?></td>
            <td><?= (int) $p['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="toggle" />
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                    <button class="btn btn--ghost btn--sm" type="submit"><?= (int) $p['is_active'] === 1 ? 'Hide' : 'Activate' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
