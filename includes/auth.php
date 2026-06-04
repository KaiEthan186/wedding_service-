<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }

    $stmt = db()->prepare(
        'SELECT id, role, first_name, last_name, email, phone, avatar, is_active
         FROM users WHERE id = ? LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['user_id']]);
    $row = $stmt->fetch();

    if (!$row || !(int) $row['is_active']) {
        auth_logout();
        return null;
    }

    $user = $row;
    $cached = true;
    return $user;
}

function auth_login(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function require_login(): array
{
    $user = auth_user();
    if (!$user) {
        flash_set('error', 'Please sign in to continue.');
        redirect('login.php');
    }
    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        flash_set('error', 'You do not have permission to access that page.');
        redirect(dashboard_url_for_role($user['role']));
    }
    return $user;
}

function register_user(
    string $firstName,
    string $lastName,
    string $email,
    string $password,
    string $role = 'customer',
    ?string $businessName = null
): array {
    $pdo = db();

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        return ['ok' => false, 'error' => 'This email is already registered.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (role, first_name, last_name, email, password_hash, email_verified_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([$role, $firstName, $lastName, $email, $hash]);
        $userId = (int) $pdo->lastInsertId();

        if ($role === 'vendor' && $businessName) {
            $slug = slugify($businessName);
            $base = $slug;
            $n = 1;
            while (true) {
                $s = $pdo->prepare('SELECT id FROM vendors WHERE slug = ? LIMIT 1');
                $s->execute([$slug]);
                if (!$s->fetch()) {
                    break;
                }
                $slug = $base . '-' . (++$n);
            }
            $v = $pdo->prepare(
                'INSERT INTO vendors (user_id, business_name, slug, is_approved) VALUES (?, ?, ?, 0)'
            );
            $v->execute([$userId, $businessName, $slug]);
        }

        notify_user($userId, 'Welcome!', 'Your account was created successfully.', 'success');
        $pdo->commit();
        return ['ok' => true, 'user_id' => $userId];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

function attempt_login(string $email, string $password): array
{
    $stmt = db()->prepare(
        'SELECT id, password_hash, is_active, role FROM users WHERE email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || !(int) $row['is_active']) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    if (!password_verify($password, $row['password_hash'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }

    auth_login((int) $row['id']);
    return ['ok' => true, 'role' => $row['role']];
}

function vendor_profile_for_user(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM vendors WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
