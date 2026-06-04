<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Cart';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = (int) ($_POST['product_id'] ?? 0);
    if ($action === 'update') {
        cart_update($pid, (int) ($_POST['qty'] ?? 1));
    } elseif ($action === 'remove') {
        cart_remove($pid);
    }
    redirect('cart.php');
}

$cart = cart_get();
$items = [];
$subtotal = 0.0;

if ($cart) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $qty = (int) ($cart[$p['id']] ?? 0);
        $line = (float) $p['price'] * $qty;
        $subtotal += $line;
        $items[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
    }
}

require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="section">
        <div class="shell">
            <h1>Shopping cart</h1>
            <?php if (!$items) : ?>
                <p class="empty-state">Your cart is empty. <a href="shop.php">Browse the shop</a></p>
            <?php else : ?>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $row) : ?>
                        <tr>
                            <td><?= e($row['product']['name']) ?></td>
                            <td><?= format_money($row['product']['price']) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update" />
                                    <input type="hidden" name="product_id" value="<?= (int) $row['product']['id'] ?>" />
                                    <input type="number" name="qty" value="<?= $row['qty'] ?>" min="1" class="qty-input" />
                                    <button type="submit" class="btn btn--ghost btn--sm">Update</button>
                                </form>
                            </td>
                            <td><?= format_money($row['line']) ?></td>
                            <td>
                                <form method="post"><input type="hidden" name="action" value="remove" /><input type="hidden" name="product_id" value="<?= (int) $row['product']['id'] ?>" /><button type="submit" class="btn btn--ghost btn--sm">Remove</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="price-lg">Subtotal: <?= format_money($subtotal) ?></p>
                <a href="checkout.php" class="btn btn--primary">Checkout</a>
            <?php endif; ?>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
