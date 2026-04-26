<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: ' . app_url('admin/dashboard.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = verify_admin_login($username, $password);
    if ($user) {
        login_admin($user);
        add_flash('success', 'Admin-Login erfolgreich. Willkommen ' . ($user['display_name'] ?: $user['username']) . '!');
        header('Location: ' . app_url('admin/dashboard.php'));
        exit;
    }

    add_flash('error', 'Login fehlgeschlagen. Benutzername oder Passwort ungültig.');
}

render_header('Admin Login', 'admin');
?>
<main class="container narrow">
    <h2>Admin Login</h2>
    <form method="post" class="card form-grid">
        <label>Benutzername
            <input name="username" required autocomplete="username">
        </label>
        <label>Passwort
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="button">Einloggen</button>
    </form>
</main>
<?php render_footer(); ?>
