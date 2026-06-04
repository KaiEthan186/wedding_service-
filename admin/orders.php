<?php
declare(strict_types=1);

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/workflows.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_role('admin');
$pageTitle = 'Orders';

handle_order_post_actions();

$orders = db()->query(
    'SELECT o.*, u.first_name, u.last_name, u.email
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 100'
)->fetchAll();

require dirname(__DIR__) . '/includes/admin_header.php';
?>

    <h1>Shop orders</h1>
    <p class="text-muted">Confirm moves order to processing. Reject/Cancel sets status to cancelled. Use Mark paid when payment is received.</p>

    <table class="data-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o) : ?>
            <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e($o['first_name'] . ' ' . $o['last_name']) ?></td>
                <td><?= format_money($o['total']) ?></td>
                <td><span class="<?= status_badge_class((string) $o['status']) ?>"><?= e(booking_status_label((string) $o['status'])) ?></span></td>
                <td><span class="<?= status_badge_class((string) $o['payment_status']) ?>"><?= e(ucfirst((string) $o['payment_status'])) ?></span></td>
                <td><?= e(substr((string) $o['created_at'], 0, 10)) ?></td>
                <td class="table-actions">
                    <?php if ($o['status'] === 'pending') : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>" />
                        <button type="submit" name="action" value="confirm" class="btn btn--primary btn--sm">Confirm</button>
                        <button type="submit" name="action" value="reject" class="btn btn--ghost btn--sm">Reject</button>
                    </form>
                    <?php endif; ?>
                    <?php if (!in_array($o['status'], ['cancelled', 'delivered'], true)) : ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>" />
                        <button type="submit" name="action" value="cancel" class="btn btn--ghost btn--sm">Cancel</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>" />
                        <input type="hidden" name="action" value="payment" />
                        <input type="hidden" name="payment_status" value="paid" />
                        <button type="submit" class="btn btn--ghost btn--sm">Mark paid</button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>" />
                        <input type="hidden" name="action" value="payment" />
                        <input type="hidden" name="payment_status" value="unpaid" />
                        <button type="submit" class="btn btn--ghost btn--sm">Unpaid</button>
                    </form>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>" />
                        <input type="hidden" name="action" value="status" />
                        <select name="status" class="select--sm">
                            <?php foreach (ORDER_STATUSES as $st) : ?>
                                <option value="<?= e($st) ?>" <?= $o['status'] === $st ? 'selected' : '' ?>><?= e(booking_status_label($st)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn--ghost btn--sm">Set</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php require dirname(__DIR__) . '/includes/admin_footer.php'; ?>
