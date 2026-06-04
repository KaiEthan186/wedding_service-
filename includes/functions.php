<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash_set(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): string
{
    $msg = (string) ($_SESSION['_flash'][$key] ?? '');
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function format_money(float|string $amount): string
{
    $config = require dirname(__DIR__) . '/config.php';
    $symbol = $config['currency_symbol'] ?? '$';
    return $symbol . number_format((float) $amount, 2);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function order_number(): string
{
    return 'WS-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('ymd');
}

function cart_get(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $total = 0;
    foreach (cart_get() as $qty) {
        $total += (int) $qty;
    }
    return $total;
}

function cart_add(int $productId, int $qty = 1): void
{
    $_SESSION['cart'] ??= [];
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + max(1, $qty);
}

function cart_update(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function cart_remove(int $productId): void
{
    unset($_SESSION['cart'][$productId]);
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function notify_user(int $userId, string $title, string $message, string $type = 'info'): void
{
    $stmt = db()->prepare(
        'INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $title, $message, $type]);
}

function dashboard_url_for_role(string $role): string
{
    return match ($role) {
        'admin'    => 'admin/dashboard.php',
        'vendor'   => 'vendor/dashboard.php',
        default    => 'customer/dashboard.php',
    };
}

/** Safe internal return URL (relative paths only). */
function safe_return_url(string $url, string $fallback = 'index.php'): string
{
    $url = trim($url);
    if ($url === '' || str_contains($url, '://') || str_starts_with($url, '//')) {
        return $fallback;
    }
    if ($url[0] === '/') {
        return $url;
    }
    if (preg_match('#^[a-z0-9_./-]+\.php#i', $url) === 1) {
        return $url;
    }
    return $fallback;
}

/** Prefer same-site referer, else explicit/fallback URL. */
function back_url(string $fallback = 'index.php'): string
{
    $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    if ($ref !== '') {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '' && str_contains($ref, $host)) {
            $path = parse_url($ref, PHP_URL_PATH) ?: '';
            $query = parse_url($ref, PHP_URL_QUERY);
            $relative = $path . ($query ? '?' . $query : '');
            return safe_return_url(ltrim($relative, '/'), $fallback);
        }
    }
    return safe_return_url($fallback, $fallback);
}

/** Image path from DB — supports local assets or full URLs (e.g. Unsplash). */
function media_url(?string $path, string $base = ''): string
{
    if ($path === null || trim($path) === '') {
        return '';
    }
    $path = trim($path);
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function placeholder_image(string $type = 'wedding'): string
{
    return match ($type) {
        'product'  => 'https://images.unsplash.com/photo-1594552072238-4b08548b0c69?w=600&q=80',
        'vendor'   => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80',
        'service'  => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&q=80',
        'gallery'  => 'https://images.unsplash.com/photo-1465492120980-0b1273bca3bb?w=600&q=80',
        default    => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&q=80',
    };
}

function card_image_style(?string $image, string $base = '', string $fallbackType = 'service'): string
{
    $url = media_url($image, $base);
    if ($url === '') {
        $url = placeholder_image($fallbackType);
    }
    return "background-image:url('" . e($url) . "')";
}

function wishlist_add_url(string $type, int $id, string $returnTo = ''): string
{
    $url = 'wishlist-add.php?type=' . rawurlencode($type) . '&id=' . $id;
    if ($returnTo !== '') {
        $url .= '&return=' . rawurlencode(safe_return_url($returnTo));
    }
    return $url;
}

function render_back_link(string $fallback, string $label = '← Back'): void
{
    $href = back_url($fallback);
    echo '<a href="' . e($href) . '" class="btn btn--ghost btn-back">' . e($label) . '</a>';
}
