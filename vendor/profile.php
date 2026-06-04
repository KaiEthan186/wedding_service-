<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Business Profile';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business = trim((string) ($_POST['business_name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $website = trim((string) ($_POST['website'] ?? ''));
    $logo = trim((string) ($_POST['logo'] ?? ''));

    if ($business !== '') {
        db()->prepare(
            'UPDATE vendors SET business_name = ?, description = ?, location = ?, phone = ?, website = ?, logo = ? WHERE id = ?'
        )->execute([$business, $description ?: null, $location ?: null, $phone ?: null, $website ?: null, $logo ?: null, (int) $vendor['id']]);
        flash_set('success', 'Business profile updated.');
        redirect('profile.php');
    }
}

$vendor = vendor_profile_for_user((int) $user['id']);
require dirname(__DIR__) . '/includes/vendor_header.php';
?>
<h1>Business profile</h1>
<form class="form" method="post" style="max-width:600px;">
    <label><span>Business name</span><input type="text" name="business_name" required value="<?= e((string) $vendor['business_name']) ?>" /></label>
    <label><span>Description</span><textarea name="description" rows="4"><?= e((string) ($vendor['description'] ?? '')) ?></textarea></label>
    <label><span>Contact phone</span><input type="text" name="phone" value="<?= e((string) ($vendor['phone'] ?? '')) ?>" /></label>
    <label><span>Location</span><input type="text" name="location" value="<?= e((string) ($vendor['location'] ?? '')) ?>" /></label>
    <label><span>Website</span><input type="url" name="website" value="<?= e((string) ($vendor['website'] ?? '')) ?>" /></label>
    <label><span>Logo URL</span><input type="url" name="logo" value="<?= e((string) ($vendor['logo'] ?? '')) ?>" /></label>
    <button class="btn btn--primary" type="submit">Save profile</button>
</form>
<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
