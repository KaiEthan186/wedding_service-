<?php
declare(strict_types=1);

/** Monthly revenue from paid bookings + paid orders (last 12 months). */
function analytics_monthly_revenue(): array
{
    $labels = [];
    $bookingRev = [];
    $orderRev = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-{$i} months"));
        $labels[] = date('M Y', strtotime($month . '-01'));
        $b = db()->prepare(
            'SELECT COALESCE(SUM(total_amount), 0) FROM bookings
             WHERE payment_status = "paid" AND DATE_FORMAT(created_at, "%Y-%m") = ?'
        );
        $b->execute([$month]);
        $bookingRev[] = (float) $b->fetchColumn();
        $o = db()->prepare(
            'SELECT COALESCE(SUM(total), 0) FROM orders
             WHERE payment_status = "paid" AND DATE_FORMAT(created_at, "%Y-%m") = ?'
        );
        $o->execute([$month]);
        $orderRev[] = (float) $o->fetchColumn();
    }
    $combined = [];
    foreach ($bookingRev as $idx => $v) {
        $combined[] = round($v + ($orderRev[$idx] ?? 0), 2);
    }
    return ['labels' => $labels, 'values' => $combined, 'bookings' => $bookingRev, 'orders' => $orderRev];
}

/** Simple 24-month revenue forecast from average growth. */
function analytics_revenue_forecast(): array
{
    $monthly = analytics_monthly_revenue();
    $values = $monthly['values'];
    $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
    $last = $values ? (float) end($values) : $avg;
    $growth = 1.03;

    $labels = [];
    $forecast = [];
    $start = new DateTime('first day of next month');
    for ($i = 0; $i < 24; $i++) {
        $labels[] = $start->format('M Y');
        $last = round($last * $growth, 2);
        $forecast[] = $last;
        $start->modify('+1 month');
    }
    return ['labels' => $labels, 'values' => $forecast];
}

function analytics_bookings_by_category(): array
{
    $rows = db()->query(
        'SELECT c.name, COUNT(b.id) AS cnt
         FROM service_categories c
         LEFT JOIN services s ON s.category_id = c.id
         LEFT JOIN bookings b ON b.service_id = s.id
         GROUP BY c.id, c.name
         ORDER BY cnt DESC'
    )->fetchAll();
    return [
        'labels' => array_column($rows, 'name'),
        'values' => array_map('intval', array_column($rows, 'cnt')),
    ];
}

function analytics_user_roles(): array
{
    $rows = db()->query(
        'SELECT role, COUNT(*) AS cnt FROM users GROUP BY role'
    )->fetchAll();
    return [
        'labels' => array_map(fn ($r) => ucfirst((string) $r['role']), $rows),
        'values' => array_map('intval', array_column($rows, 'cnt')),
    ];
}

function analytics_vendor_booking_status(int $vendorId): array
{
    $rows = db()->prepare(
        'SELECT status, COUNT(*) AS cnt FROM bookings WHERE vendor_id = ? GROUP BY status'
    );
    $rows->execute([$vendorId]);
    $data = $rows->fetchAll();
    return [
        'labels' => array_map(fn ($r) => booking_status_label((string) $r['status']), $data),
        'values' => array_map('intval', array_column($data, 'cnt')),
    ];
}

function analytics_vendor_monthly_revenue(int $vendorId): array
{
    $labels = [];
    $values = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-{$i} months"));
        $labels[] = date('M Y', strtotime($month . '-01'));
        $stmt = db()->prepare(
            'SELECT COALESCE(SUM(total_amount), 0) FROM bookings
             WHERE vendor_id = ? AND payment_status = "paid" AND DATE_FORMAT(created_at, "%Y-%m") = ?'
        );
        $stmt->execute([$vendorId, $month]);
        $values[] = (float) $stmt->fetchColumn();
    }
    return ['labels' => $labels, 'values' => $values];
}

function analytics_vendor_top_services(int $vendorId): array
{
    $rows = db()->prepare(
        'SELECT s.title, COUNT(b.id) AS cnt
         FROM services s
         LEFT JOIN bookings b ON b.service_id = s.id
         WHERE s.vendor_id = ?
         GROUP BY s.id, s.title
         ORDER BY cnt DESC LIMIT 8'
    );
    $rows->execute([$vendorId]);
    $data = $rows->fetchAll();
    return [
        'labels' => array_column($data, 'title'),
        'values' => array_map('intval', array_column($data, 'cnt')),
    ];
}
