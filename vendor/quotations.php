<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$user = require_role('vendor');
$vendor = vendor_profile_for_user((int) $user['id']);
$pageTitle = 'Quotations';

if (!$vendor) {
    flash_set('error', 'Vendor profile not found.');
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qid = (int) ($_POST['quotation_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    if ($qid > 0 && in_array($status, ['sent', 'accepted', 'rejected', 'expired'], true)) {
        db()->prepare('UPDATE quotations SET status = ? WHERE id = ? AND vendor_id = ?')
            ->execute([$status, $qid, (int) $vendor['id']]);
        flash_set('success', 'Quotation updated.');
    }
    redirect('quotations.php');
}

$quotes = db()->prepare(
    'SELECT q.*, u.first_name, u.last_name
     FROM quotations q
     JOIN users u ON u.id = q.customer_id
     WHERE q.vendor_id = ?
     ORDER BY q.created_at DESC'
);
$quotes->execute([(int) $vendor['id']]);
$rows = $quotes->fetchAll();

require dirname(__DIR__) . '/includes/vendor_header.php';
?>
<h1>Quotations</h1>
<p class="text-muted">Use this to respond to customer quotation requests.</p>
<table class="data-table">
    <thead><tr><th>Customer</th><th>Title</th><th>Amount</th><th>Status</th><th>Details</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $q) : ?>
        <tr>
            <td><?= e($q['first_name'] . ' ' . $q['last_name']) ?></td>
            <td><?= e($q['title']) ?></td>
            <td><?= format_money($q['amount']) ?></td>
            <td><?= e($q['status']) ?></td>
            <td><?= e((string) $q['details']) ?></td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="quotation_id" value="<?= (int) $q['id'] ?>" />
                    <select name="status" class="select--sm">
                        <?php foreach (['requested', 'sent', 'accepted', 'rejected', 'expired'] as $st) : ?>
                            <option value="<?= e($st) ?>" <?= $q['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn--ghost btn--sm">Set</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$rows) : ?><tr><td colspan="6">No quotations yet.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php require dirname(__DIR__) . '/includes/vendor_footer.php'; ?>
