<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: ' . app_url('admin/events.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (verify_admin_password($password)) {
        login_admin();
        add_flash('success', 'Admin-Login erfolgreich.');
        header('Location: ' . app_url('admin/events.php'));
        exit;
    }
    add_flash('error', 'Passwort ungültig.');
}

render_header('Admin Login', 'admin');
?>
<main class="container narrow">
    <h2>Admin Login</h2>
    <form method="post" class="card form-grid">
        <label>Passwort
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="button">Einloggen</button>
    </form>
</main>
<?php render_footer(); ?>
