<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';

require_admin();
$current = current_admin_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            $username = trim($_POST['username'] ?? '');
            $displayName = trim($_POST['display_name'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if ($username === '' || strlen($password) < 8) {
                throw new RuntimeException('Benutzername erforderlich, Passwort mindestens 8 Zeichen.');
            }

            create_admin_user($username, $displayName ?: $username, $password);
            add_flash('success', 'Admin-Benutzer erstellt.');
        }

        if ($action === 'toggle') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            if ($userId === (int) $current['id']) {
                throw new RuntimeException('Du kannst deinen eigenen Benutzer nicht deaktivieren.');
            }
            toggle_admin_user($userId);
            add_flash('success', 'Benutzerstatus geändert.');
        }

        if ($action === 'reset_password') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $newPassword = (string) ($_POST['new_password'] ?? '');
            if (strlen($newPassword) < 8) {
                throw new RuntimeException('Neues Passwort muss mindestens 8 Zeichen haben.');
            }
            update_admin_user_password($userId, $newPassword);
            add_flash('success', 'Passwort aktualisiert.');
        }
    } catch (Throwable $e) {
        add_flash('error', $e->getMessage());
    }

    header('Location: ' . app_url('admin/users.php'));
    exit;
}

$users = get_admin_users();

render_header('Admin Benutzer', 'admin');
?>
<main class="container">
    <div class="section-head">
        <h2>Admin-Benutzer verwalten</h2>
        <a class="button secondary" href="<?= h(app_url('admin/dashboard.php')) ?>">Zurück</a>
    </div>

    <form method="post" class="card form-grid">
        <input type="hidden" name="action" value="create">
        <h3>Neuen Admin erstellen</h3>
        <label>Benutzername
            <input name="username" required>
        </label>
        <label>Anzeigename
            <input name="display_name">
        </label>
        <label>Passwort (mind. 8 Zeichen)
            <input type="password" name="password" minlength="8" required>
        </label>
        <button type="submit" class="button">Admin anlegen</button>
    </form>

    <div class="table-wrap card">
        <table>
            <thead>
                <tr><th>User</th><th>Status</th><th>Erstellt</th><th>Aktionen</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <strong><?= h($user['username']) ?></strong><br>
                        <small><?= h($user['display_name']) ?></small>
                        <?php if ((int) $user['id'] === (int) $current['id']): ?>
                            <span class="badge">Du</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $user['is_active'] ? 'Aktiv' : 'Inaktiv' ?></td>
                    <td><?= h($user['created_at']) ?></td>
                    <td>
                        <?php if ((int) $user['id'] !== (int) $current['id']): ?>
                            <form method="post" style="display:inline-block; margin-right:.5rem;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="user_id" value="<?= h((string) $user['id']) ?>">
                                <button class="button secondary" type="submit">Aktiv/Inaktiv</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="user_id" value="<?= h((string) $user['id']) ?>">
                            <input type="password" name="new_password" minlength="8" placeholder="Neues Passwort" required>
                            <button class="button warning" type="submit">Passwort setzen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php render_footer(); ?>
