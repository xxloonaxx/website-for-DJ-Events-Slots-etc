<?php

function render_header(string $title, string $active = ''): void
{
    $flashes = flash();
    $isAdmin = function_exists('is_admin_logged_in') && is_admin_logged_in();
    $adminUser = $isAdmin && function_exists('current_admin_user') ? current_admin_user() : null;
    ?>
    <!doctype html>
    <html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?></title>
        <link rel="stylesheet" href="<?= h(app_url('assets/style.css')) ?>">
    </head>
    <body>
    <header class="site-header">
        <div class="logo-area">
            <img class="site-logo" src="<?= h(app_url('assets/logo.png')) ?>" alt="Logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
            <div class="logo-placeholder" style="display:none;">LOGO PLATZ</div>
            <div>
                <h1>VRChat DJ Event Board</h1>
                <p>Internes Buchungssystem für DJ-Slots</p>
            </div>
        </div>
        <nav>
            <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= h(app_url('index.php')) ?>">Homepage</a>
            <a class="<?= $active === 'dj' ? 'active' : '' ?>" href="<?= h(app_url('dj/index.php')) ?>">DJ-Bereich</a>
            <a class="<?= $active === 'admin' ? 'active' : '' ?>" href="<?= h(app_url('admin/events.php')) ?>">Admin</a>
            <?php if ($isAdmin): ?>
                <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= h(app_url('admin/dashboard.php')) ?>">Dashboard</a>
                <a href="<?= h(app_url('admin/users.php')) ?>">Benutzer</a>
                <a href="<?= h(app_url('admin/event_form.php')) ?>">+ Event</a>
                <a href="<?= h(app_url('admin/access_codes.php')) ?>">Codes</a>
                <a href="<?= h(app_url('admin/logout.php')) ?>">Logout</a>
            <?php endif; ?>
        </nav>
        <?php if ($adminUser): ?>
            <p><small>Eingeloggt als: <strong><?= h($adminUser['display_name'] ?: $adminUser['username']) ?></strong> (<?= h($adminUser['username']) ?>)</small></p>
        <?php endif; ?>
    </header>

    <?php if ($flashes): ?>
        <section class="flash-wrap">
            <?php foreach ($flashes as $type => $message): ?>
                <div class="flash <?= h($type) ?>"><?= h($message) ?></div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    <footer class="site-footer">
        <p>© <?= date('Y') ?> VRChat DJ Event Board</p>
    </footer>
    </body>
    </html>
    <?php
}
