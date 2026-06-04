<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Checkout';
$user = require_login();
$cart = cart_get();

if (!$cart) {
    flash_set('error', 'Your cart is empty.');
    redirect('shop.php');
}

$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$subtotal = 0.0;
$lines = [];
foreach ($products as $p) {
    $qty = (int) ($cart[$p['id']] ?? 0);
    $line = (float) $p['price'] * $qty;
    $subtotal += $line;
    $lines[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
}

$discount = 0.0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim((string) ($_POST['shipping_address'] ?? ''));
    $method = $_POST['payment_method'] ?? 'cod';
    $coupon = trim((string) ($_POST['coupon'] ?? ''));

    if ($coupon === 'WEDDING10') {
        $discount = round($subtotal * 0.1, 2);
    }

    if ($address === '') {
        flash_set('error', 'Please enter a shipping address.');
    } else {
        $total = $subtotal - $discount;
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $num = order_number();
            $o = $pdo->prepare(
                'INSERT INTO orders (user_id, order_number, subtotal, discount, total, payment_method, shipping_address, coupon_code)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $o->execute([$user['id'], $num, $subtotal, $discount, $total, $method, $address, $coupon ?: null]);
            $orderId = (int) $pdo->lastInsertId();

            $oi = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            foreach ($lines as $row) {
                $oi->execute([$orderId, $row['product']['id'], $row['qty'], $row['product']['price']]);
            }

            $pay = $pdo->prepare(
                'INSERT INTO payments (user_id, payable_type, payable_id, amount, method, status)
                 VALUES (?, "order", ?, ?, ?, "pending")'
            );
            $pay->execute([$user['id'], $orderId, $total, $method]);

            notify_user((int) $user['id'], 'Order placed', "Order $num has been received.", 'success');
            $pdo->commit();
            cart_clear();
            flash_set('success', "Order $num placed successfully!");
            redirect('customer/orders.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash_set('error', 'Checkout failed. Please try again.');
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

    <main id="main" class="section">
        <div class="shell" style="max-width:640px;">
            <h1>Checkout</h1>
            <p>Subtotal: <?= format_money($subtotal) ?> — Coupon <code>WEDDING10</code> for 10% off</p>
            <form class="form" method="post">
                <label><span>Shipping address</span><textarea name="shipping_address" rows="3" required></textarea></label>
                <label><span>Coupon code</span><input type="text" name="coupon" placeholder="WEDDING10" /></label>
                <label><span>Payment method</span>
                    <select name="payment_method" class="form-select">
                        <option value="cod">Cash on delivery</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="manual">Manual payment (upload proof later)</option>
                    </select>
                </label>
                <button type="submit" class="btn btn--primary btn--block">Place order</button>
            </form>
        </div>
    </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
