<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
require __DIR__ . '/includes/events.php';

$events = get_events(true);

render_header('Startseite - VRChat DJ Event Board', 'home');
?>
<main class="container">
    <section class="hero">
        <div class="banner-placeholder">BANNER PLATZ (z. B. 1600x400)</div>
        <h2>Öffentliche Event-Übersicht</h2>
        <p>Hier sehen Gäste und DJs alle veröffentlichten Events inklusive Slots.</p>
    </section>

    <section class="cards">
        <?php if (!$events): ?>
            <div class="card">
                <p>Aktuell sind keine Events veröffentlicht.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <article class="card">
                <?php if (!empty($event['banner_path'])): ?>
                    <img class="banner-img" src="<?= h(app_url($event['banner_path'])) ?>" alt="Banner <?= h($event['name']) ?>">
                <?php endif; ?>
                <h3><?= h($event['name']) ?></h3>
                <ul>
                    <li><strong>Datum:</strong> <?= h($event['event_date']) ?> ab <?= h(substr($event['start_time'], 0, 5)) ?></li>
                    <li><strong>Genre/Thema:</strong> <?= h($event['genre_theme']) ?></li>
                    <li><strong>VRChat World:</strong> <?= h($event['vrchat_world']) ?></li>
                    <li><strong>Slots:</strong> <?= h((string) $event['slot_count']) ?> x <?= h((string) $event['slot_duration_min']) ?> min</li>
                </ul>
                <p><?= nl2br(h($event['description'])) ?></p>
                <a class="button" href="<?= h(app_url('dj/index.php')) ?>">Als DJ Slot anfragen</a>
            </article>
        <?php endforeach; ?>
    </section>
</main>
<?php render_footer(); ?>
