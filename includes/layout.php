<?php

function render_header(string $title, string $active = ''): void
{
    $flashes = flash();
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
            <div class="logo-placeholder">LOGO PLATZ</div>
            <div>
                <h1>VRChat DJ Event Board</h1>
                <p>Internes Buchungssystem für DJ-Slots</p>
            </div>
        </div>
        <nav>
            <a class="<?= $active === 'home' ? 'active' : '' ?>" href="<?= h(app_url('index.php')) ?>">Startseite</a>
            <a class="<?= $active === 'dj' ? 'active' : '' ?>" href="<?= h(app_url('dj/index.php')) ?>">DJ-Bereich</a>
            <a class="<?= $active === 'admin' ? 'active' : '' ?>" href="<?= h(app_url('admin/events.php')) ?>">Admin</a>
        </nav>
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
