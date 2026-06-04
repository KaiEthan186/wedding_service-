<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'My Packages';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'create');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle' && $id > 0) {
        db()->prepare(
            'UPDATE packages SET status = CASE WHEN status = "active" THEN "inactive" ELSE "active" END WHERE id = ? AND vendor_id = ?'
        )->execute([$id, (int) $vendor['id']]);
        flash_set('success', 'Package status updated.');
        redirect('packages.php');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $days = (int) ($_POST['duration_days'] ?? 0);
    $included = trim((string) ($_POST['included_services'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($name !== '' && $price > 0) {
        $slug = slugify($name) . '-' . time();
        db()->prepare(
            'INSERT INTO packages (vendor_id, name, slug, description, price, duration_days, included_services, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "active")'
        )->execute([(int) $vendor['id'], $name, $slug, $description ?: null, $price, $days ?: null, $included ?: null]);
        flash_set('success', 'Package created.');
        redirect('packages.php');
    }
    flash_set('error', 'Please fill required fields.');
}

$stmt = db()->prepare('SELECT * FROM packages WHERE vendor_id = ? ORDER BY created_at DESC');
$stmt->execute([(int) $vendor['id']]);
$packages = $stmt->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

<h1>My packages</h1>
<form method="post" class="form" style="max-width:520px;">
    <label><span>Package name</span><input type="text" name="name" required /></label>
    <label><span>Price</span><input type="number" min="0.01" step="0.01" name="price" required /></label>
    <label><span>Duration days</span><input type="number" min="1" step="1" name="duration_days" /></label>
    <label><span>Included services (comma separated)</span><textarea name="included_services" rows="2"></textarea></label>
    <label><span>Description</span><textarea name="description" rows="3"></textarea></label>
    <button class="btn btn--primary" type="submit">Create package</button>
</form>

<table class="data-table">
    <thead><tr><th>Name</th><th>Price</th><th>Duration</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($packages as $p) : ?>
        <tr>
            <td><?= e($p['name']) ?></td>
            <td><?= format_money($p['price']) ?></td>
            <td><?= e((string) ($p['duration_days'] ?? '—')) ?></td>
            <td><?= e($p['status']) ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="toggle" />
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                    <button class="btn btn--ghost btn--sm" type="submit"><?= $p['status'] === 'active' ? 'Pause' : 'Activate' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
