<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
require __DIR__ . '/includes/events.php';
require __DIR__ . '/includes/auth.php';

$events = get_events(true);
$nextEvent = get_next_public_event();

render_header('Startseite - VRChat DJ Event Board', 'home');
?>
<main class="container">
    <section class="hero-grid">
        <div class="card">
            <h2>Nächstes Event im Fokus</h2>
            <?php if ($nextEvent): ?>
                <?php if (!empty($nextEvent['banner_path'])): ?>
                    <img class="banner-img" src="<?= h(app_url($nextEvent['banner_path'])) ?>" alt="Banner <?= h($nextEvent['name']) ?>">
                <?php else: ?>
                    <div class="banner-placeholder">BANNER PLATZ (z. B. 1600x400)</div>
                <?php endif; ?>
                <h3><?= h($nextEvent['name']) ?></h3>
                <p><strong>Datum:</strong> <?= h($nextEvent['event_date']) ?> <?= h(substr($nextEvent['start_time'], 0, 5)) ?></p>
                <p><strong>Genre/Thema:</strong> <?= h($nextEvent['genre_theme']) ?> | <strong>World:</strong> <?= h($nextEvent['vrchat_world']) ?></p>
                <p><?= nl2br(h($nextEvent['description'])) ?></p>
            <?php else: ?>
                <p>Kein kommendes Event vorhanden.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <p><a class="button" href="<?= h(app_url('dj/index.php')) ?>">DJ-Slot eintragen</a></p>
            <p><a class="button secondary" href="<?= h(app_url('admin/login.php')) ?>">Admin Login</a></p>
            <?php if (is_admin_logged_in()): ?>
                <p><a class="button secondary" href="<?= h(app_url('admin/dashboard.php')) ?>">Admin Dashboard</a></p>
            <?php endif; ?>
            <p><small>Als Admin eingeloggt? Dann kannst du im DJ-Bereich Buchungen direkt bearbeiten.</small></p>
        </div>
    </section>

    <section>
        <h2>Event-Übersicht mit Slots</h2>
        <?php if (!$events): ?>
            <div class="card">
                <p>Aktuell sind keine Events veröffentlicht.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($events as $event): ?>
            <?php $slots = get_slots_for_event((int) $event['id']); ?>
            <article class="card">
                <div class="section-head">
                    <h3><?= h($event['name']) ?></h3>
                    <span class="badge"><?= h($event['event_date']) ?> <?= h(substr($event['start_time'], 0, 5)) ?></span>
                </div>
                <p><strong>Genre:</strong> <?= h($event['genre_theme']) ?> | <strong>World:</strong> <?= h($event['vrchat_world']) ?></p>
                <p><?= nl2br(h($event['description'])) ?></p>

                <ul class="slot-mini">
                    <?php foreach ($slots as $slot): ?>
                        <li>
                            Slot <?= h((string) $slot['slot_number']) ?> (<?= h(date('H:i', strtotime($slot['starts_at']))) ?>-<?= h(date('H:i', strtotime($slot['ends_at']))) ?>):
                            <?php if ($slot['dj_name']): ?>
                                <strong>Belegt</strong> von <?= h($slot['dj_name']) ?>
                            <?php else: ?>
                                <strong>Frei</strong>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p><a class="button" href="<?= h(app_url('dj/index.php')) ?>">Zur DJ-Buchung</a></p>
            </article>
        <?php endforeach; ?>
    </section>
</main>
<?php render_footer(); ?>
