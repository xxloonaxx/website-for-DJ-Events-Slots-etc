<?php


function ensure_admin_user_schema(): void
{
    try {
        db()->query('SELECT id FROM admin_users LIMIT 1');
    } catch (Throwable $e) {
        if (function_exists('render_system_error')) {
            render_system_error(
                'Admin-Benutzer Tabelle fehlt',
                "Die Tabelle admin_users fehlt. Bitte das aktuelle sql/schema.sql importieren.\n\nFehler: " . $e->getMessage()
            );
        }
        throw $e;
    }
}

function ensure_default_admin_user(): void
{
    global $config;
    ensure_admin_user_schema();

    try {
        $count = (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    } catch (Throwable $e) {
        return;
    }

    if ($count > 0) {
        return;
    }

    $username = trim($config['app']['default_admin_username'] ?? 'admin');
    $password = (string) ($config['app']['default_admin_password'] ?? 'admin123');

    if ($username === '' || $password === '') {
        return;
    }

    $stmt = db()->prepare(
        'INSERT INTO admin_users (username, password_hash, display_name, is_active)
         VALUES (:username, :password_hash, :display_name, 1)'
    );
    $stmt->execute([
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'display_name' => 'System Admin',
    ]);
}

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_user_id']);
}

function current_admin_user(): ?array
{
    ensure_admin_user_schema();

    if (!is_admin_logged_in()) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, display_name, is_active FROM admin_users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['admin_user_id']]);
    $user = $stmt->fetch();

    if (!$user || !(int) $user['is_active']) {
        logout_admin();
        return null;
    }

    return $user;
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        add_flash('error', 'Bitte zuerst als Admin einloggen.');
        header('Location: ' . app_url('admin/login.php'));
        exit;
    }

    if (!current_admin_user()) {
        add_flash('error', 'Admin-Zugang ist nicht mehr aktiv.');
        header('Location: ' . app_url('admin/login.php'));
        exit;
    }
}

function verify_admin_login(string $username, string $password): ?array
{
    ensure_default_admin_user();

    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !(int) $user['is_active']) {
        return null;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }

    return $user;
}

function login_admin(array $user): void
{
    $_SESSION['admin_user_id'] = (int) $user['id'];
}

function logout_admin(): void
{
    unset($_SESSION['admin_user_id']);
}

function get_admin_users(): array
{
    ensure_admin_user_schema();
    return db()->query('SELECT id, username, display_name, is_active, created_at FROM admin_users ORDER BY created_at ASC')->fetchAll();
}

function create_admin_user(string $username, string $displayName, string $password): void
{
    $stmt = db()->prepare(
        'INSERT INTO admin_users (username, password_hash, display_name, is_active)
         VALUES (:username, :password_hash, :display_name, 1)'
    );

    $stmt->execute([
        'username' => $username,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'display_name' => $displayName,
    ]);
}

function update_admin_user_password(int $userId, string $password): void
{
    db()->prepare('UPDATE admin_users SET password_hash = :password_hash WHERE id = :id')
        ->execute([
            'id' => $userId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
}

function toggle_admin_user(int $userId): void
{
    db()->prepare('UPDATE admin_users SET is_active = IF(is_active=1,0,1) WHERE id = :id')->execute(['id' => $userId]);
}
