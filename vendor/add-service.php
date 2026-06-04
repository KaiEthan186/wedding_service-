<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Add Service';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

$categories = db()->query('SELECT * FROM service_categories ORDER BY sort_order')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) ($_POST['title'] ?? ''));
    $catId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $desc = trim((string) ($_POST['description'] ?? ''));
    $availableDates = trim((string) ($_POST['available_dates'] ?? ''));
    $packageDetails = trim((string) ($_POST['package_details'] ?? ''));
    $portfolioImages = trim((string) ($_POST['portfolio_images'] ?? ''));
    if ($title && $catId && $price > 0) {
        $slug = slugify($title);
        db()->prepare(
            'INSERT INTO services (vendor_id, category_id, title, slug, description, price, available_dates, package_details, portfolio_images, status, is_approved)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "draft", 0)'
        )->execute([$vendor['id'], $catId, $title, $slug . '-' . time(), $desc, $price, $availableDates ?: null, $packageDetails ?: null, $portfolioImages ?: null]);
        flash_set('success', 'Service submitted for admin approval.');
        redirect('services.php');
    }
}

require dirname(__DIR__) . '/includes/vendor_header.php';
?>

    <h1>Add service</h1>
    <form class="form" method="post" style="max-width:480px;">
        <label><span>Title</span><input type="text" name="title" required /></label>
        <label><span>Category</span>
            <select name="category_id" class="form-select" required>
                <?php foreach ($categories as $c) : ?>
                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label><span>Price ($)</span><input type="number" name="price" step="0.01" min="1" required /></label>
        <label><span>Description</span><textarea name="description" rows="4"></textarea></label>
        <label><span>Available dates</span><textarea name="available_dates" rows="2" placeholder="YYYY-MM-DD, YYYY-MM-DD"></textarea></label>
        <label><span>Package details</span><textarea name="package_details" rows="2" placeholder="What is included"></textarea></label>
        <label><span>Portfolio image URLs</span><textarea name="portfolio_images" rows="2" placeholder="Comma-separated image URLs"></textarea></label>
        <button type="submit" class="btn btn--primary">Submit</button>
    </form>

<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
