<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $label = trim($_POST['label'] ?? 'DJ Team');
    $expiresAt = trim($_POST['expires_at'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($code !== '') {
        db()->prepare('INSERT INTO dj_access_codes (code, label, expires_at, is_active) VALUES (:code, :label, :expires_at, :is_active)')
            ->execute([
                'code' => $code,
                'label' => $label,
                'expires_at' => $expiresAt !== '' ? $expiresAt : null,
                'is_active' => $isActive,
            ]);
        add_flash('success', 'Access-Code gespeichert.');
    }

    header('Location: ' . app_url('admin/access_codes.php'));
    exit;
}

if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];
    db()->prepare('UPDATE dj_access_codes SET is_active = IF(is_active=1,0,1) WHERE id=:id')->execute(['id' => $id]);
    add_flash('success', 'Status geändert.');
    header('Location: ' . app_url('admin/access_codes.php'));
    exit;
}

$codes = db()->query('SELECT * FROM dj_access_codes ORDER BY created_at DESC')->fetchAll();

render_header('DJ Access-Codes', 'admin');
?>
<main class="container">
    <div class="section-head">
        <h2>DJ Access-Codes</h2>
        <a class="button secondary" href="<?= h(app_url('admin/events.php')) ?>">Zurück</a>
    </div>

    <form method="post" class="card form-grid">
        <label>Code (z. B. SPRING26-DJ)
            <input name="code" required>
        </label>
        <label>Beschreibung
            <input name="label" placeholder="Crew / Anlass" required>
        </label>
        <label>Ablaufdatum (optional)
            <input type="datetime-local" name="expires_at">
        </label>
        <label class="checkbox">
            <input type="checkbox" name="is_active" value="1" checked>
            Aktiv
        </label>
        <button class="button" type="submit">Code anlegen</button>
    </form>

    <div class="table-wrap card">
        <table>
            <thead><tr><th>Code</th><th>Label</th><th>Ablauf</th><th>Status</th><th>URL</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($codes as $code): ?>
                <tr>
                    <td><?= h($code['code']) ?></td>
                    <td><?= h($code['label']) ?></td>
                    <td><?= h($code['expires_at'] ?: '-') ?></td>
                    <td><?= $code['is_active'] ? 'Aktiv' : 'Inaktiv' ?></td>
                    <td><code><?= h(app_url('dj/index.php?code=' . urlencode($code['code']))) ?></code></td>
                    <td><a href="<?= h(app_url('admin/access_codes.php?toggle=' . $code['id'])) ?>">Umschalten</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php render_footer(); ?>
