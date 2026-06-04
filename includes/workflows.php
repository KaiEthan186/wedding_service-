<?php
declare(strict_types=1);

/** Booking status values (requires migration 001 for in_progress). */
const BOOKING_STATUSES = ['pending', 'confirmed', 'in_progress', 'rejected', 'completed', 'cancelled'];
const BOOKING_PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];
const ORDER_STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
const ORDER_PAYMENT_STATUSES = ['unpaid', 'paid'];

function booking_timeline_add(int $bookingId, ?int $actorUserId, string $eventType, string $message): void
{
    db()->prepare(
        'INSERT INTO booking_timeline (booking_id, actor_user_id, event_type, event_message) VALUES (?, ?, ?, ?)'
    )->execute([$bookingId, $actorUserId, $eventType, $message]);
}

function booking_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM bookings WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function order_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function booking_status_label(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'pending'     => 'badge badge--warning',
        'confirmed', 'processing', 'in_progress' => 'badge badge--info',
        'completed', 'delivered', 'paid' => 'badge badge--success',
        'rejected', 'cancelled', 'failed', 'refunded' => 'badge badge--danger',
        default       => 'badge',
    };
}

/**
 * @param array{role: string, vendor_id?: int} $actor
 */
function booking_update_status(int $bookingId, string $status, array $actor): array
{
    if (!in_array($status, BOOKING_STATUSES, true)) {
        return ['ok' => false, 'error' => 'Invalid status.'];
    }

    $booking = booking_by_id($bookingId);
    if (!$booking) {
        return ['ok' => false, 'error' => 'Booking not found.'];
    }

    $current = (string) $booking['status'];
    if ($current === $status) {
        return ['ok' => true, 'message' => 'No change.'];
    }

    if ($actor['role'] === 'vendor') {
        $vendorId = (int) ($actor['vendor_id'] ?? 0);
        if ($vendorId <= 0 || (int) $booking['vendor_id'] !== $vendorId) {
            return ['ok' => false, 'error' => 'Not allowed.'];
        }
        $allowed = vendor_booking_status_transitions($current);
        if (!in_array($status, $allowed, true)) {
            return ['ok' => false, 'error' => 'Cannot change from ' . $current . ' to ' . $status . '.'];
        }
    }

    $pdo = db();
    $pdo->prepare('UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$status, $bookingId]);

    sync_payment_on_booking_status($bookingId, $status, (string) $booking['payment_status']);

    $customerId = (int) $booking['user_id'];
    $vendorUserId = null;
    if ($booking['vendor_id']) {
        $vu = $pdo->prepare('SELECT user_id FROM vendors WHERE id = ?');
        $vu->execute([(int) $booking['vendor_id']]);
        $vendorUserId = (int) ($vu->fetchColumn() ?: 0) ?: null;
    }

    $label = booking_status_label($status);
    notify_user($customerId, 'Booking updated', "Your booking #{$bookingId} is now: {$label}.", 'booking');
    if ($vendorUserId && $actor['role'] !== 'vendor') {
        notify_user($vendorUserId, 'Booking updated', "Booking #{$bookingId} status set to {$label}.", 'booking');
    }
    booking_timeline_add(
        $bookingId,
        isset($actor['user_id']) ? (int) $actor['user_id'] : null,
        'status_' . $status,
        "Booking status changed to {$label}."
    );

    return ['ok' => true, 'message' => 'Booking status updated.'];
}

/** @return list<string> */
function vendor_booking_status_transitions(string $current): array
{
    return match ($current) {
        'pending'   => ['confirmed', 'rejected'],
        'confirmed' => ['in_progress', 'completed', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        default     => [],
    };
}

/**
 * @param array{role: string, vendor_id?: int} $actor
 */
function booking_update_payment(int $bookingId, string $paymentStatus, array $actor): array
{
    if (!in_array($paymentStatus, BOOKING_PAYMENT_STATUSES, true)) {
        return ['ok' => false, 'error' => 'Invalid payment status.'];
    }

    $booking = booking_by_id($bookingId);
    if (!$booking) {
        return ['ok' => false, 'error' => 'Booking not found.'];
    }

    if ($actor['role'] === 'vendor') {
        return ['ok' => false, 'error' => 'Vendors cannot change payment status.'];
    }

    $pdo = db();
    $pdo->prepare('UPDATE bookings SET payment_status = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$paymentStatus, $bookingId]);

    $payStatus = match ($paymentStatus) {
        'paid'      => 'completed',
        'refunded'  => 'failed',
        default     => 'pending',
    };
    $pdo->prepare(
        'UPDATE payments SET status = ? WHERE payable_type = ? AND payable_id = ?'
    )->execute([$payStatus, 'booking', $bookingId]);

    notify_user(
        (int) $booking['user_id'],
        'Payment updated',
        "Booking #{$bookingId} payment is now {$paymentStatus}.",
        'payment'
    );
    booking_timeline_add(
        $bookingId,
        isset($actor['user_id']) ? (int) $actor['user_id'] : null,
        'payment_' . $paymentStatus,
        "Booking payment marked {$paymentStatus}."
    );

    return ['ok' => true, 'message' => 'Payment status updated.'];
}

function sync_payment_on_booking_status(int $bookingId, string $status, string $currentPayment): void
{
    if ($status === 'cancelled' && $currentPayment === 'paid') {
        booking_update_payment($bookingId, 'refunded', ['role' => 'admin']);
        return;
    }
    if ($status === 'rejected' && $currentPayment === 'unpaid') {
        db()->prepare(
            'UPDATE payments SET status = ? WHERE payable_type = ? AND payable_id = ?'
        )->execute(['failed', 'booking', $bookingId]);
    }
}

function order_update_status(int $orderId, string $status): array
{
    if (!in_array($status, ORDER_STATUSES, true)) {
        return ['ok' => false, 'error' => 'Invalid status.'];
    }

    $order = order_by_id($orderId);
    if (!$order) {
        return ['ok' => false, 'error' => 'Order not found.'];
    }

    db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);

    notify_user(
        (int) $order['user_id'],
        'Order updated',
        "Order {$order['order_number']} is now " . booking_status_label($status) . '.',
        'order'
    );

    return ['ok' => true, 'message' => 'Order status updated.'];
}

function order_update_payment(int $orderId, string $paymentStatus): array
{
    if (!in_array($paymentStatus, ORDER_PAYMENT_STATUSES, true)) {
        return ['ok' => false, 'error' => 'Invalid payment status.'];
    }

    $order = order_by_id($orderId);
    if (!$order) {
        return ['ok' => false, 'error' => 'Order not found.'];
    }

    db()->prepare('UPDATE orders SET payment_status = ? WHERE id = ?')
        ->execute([$paymentStatus, $orderId]);

    $payStatus = $paymentStatus === 'paid' ? 'completed' : 'pending';
    db()->prepare(
        'UPDATE payments SET status = ? WHERE payable_type = ? AND payable_id = ?'
    )->execute([$payStatus, 'order', $orderId]);

    notify_user(
        (int) $order['user_id'],
        'Payment updated',
        "Order {$order['order_number']} payment is now {$paymentStatus}.",
        'payment'
    );

    return ['ok' => true, 'message' => 'Order payment updated.'];
}

/** Admin can confirm/reject/cancel orders via status field. */
function order_confirm(int $orderId): array
{
    return order_update_status($orderId, 'processing');
}

function order_reject(int $orderId): array
{
    return order_update_status($orderId, 'cancelled');
}

function order_cancel(int $orderId): array
{
    return order_update_status($orderId, 'cancelled');
}

function handle_booking_post_actions(array $actor): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $action = (string) ($_POST['action'] ?? '');
    $bookingId = (int) ($_POST['booking_id'] ?? 0);
    if ($bookingId <= 0) {
        return;
    }

    if ($action === 'status') {
        $status = (string) ($_POST['status'] ?? '');
        $result = booking_update_status($bookingId, $status, $actor);
    } elseif ($action === 'payment' && $actor['role'] === 'admin') {
        $payment = (string) ($_POST['payment_status'] ?? '');
        $result = booking_update_payment($bookingId, $payment, $actor);
    } else {
        return;
    }

    if ($result['ok']) {
        flash_set('success', $result['message'] ?? 'Updated.');
    } else {
        flash_set('error', $result['error'] ?? 'Update failed.');
    }
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'bookings.php'));
    redirect($script);
}

function handle_order_post_actions(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    if ($orderId <= 0) {
        return;
    }

    $result = match ($action) {
        'confirm'  => order_confirm($orderId),
        'reject'   => order_reject($orderId),
        'cancel'   => order_cancel($orderId),
        'status'   => order_update_status($orderId, (string) ($_POST['status'] ?? '')),
        'payment'  => order_update_payment($orderId, (string) ($_POST['payment_status'] ?? '')),
        default    => ['ok' => false, 'error' => 'Unknown action.'],
    };

    if ($result['ok']) {
        flash_set('success', $result['message'] ?? 'Updated.');
    } else {
        flash_set('error', $result['error'] ?? 'Update failed.');
    }
    redirect('orders.php');
}
