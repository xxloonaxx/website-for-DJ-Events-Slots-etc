<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/events.php';

require_admin();
$stats = get_admin_stats();
$upcoming = get_events(false);

render_header('Admin Dashboard', 'dashboard');
?>
<main class="container">
    <h2>Admin Dashboard</h2>

    <section class="kpi-grid">
        <div class="card kpi"><span class="num"><?= h((string) $stats['events_total']) ?></span><span>Events gesamt</span></div>
        <div class="card kpi"><span class="num"><?= h((string) $stats['events_upcoming']) ?></span><span>Bevorstehende Events</span></div>
        <div class="card kpi"><span class="num"><?= h((string) $stats['slots_booked']) ?></span><span>Gebuchte Slots</span></div>
        <div class="card kpi"><span class="num"><?= h((string) $stats['slots_free']) ?></span><span>Freie Slots</span></div>
        <div class="card kpi"><span class="num"><?= h((string) $stats['admin_users']) ?></span><span>Admin-Benutzer</span></div>
    </section>

    <section class="card">
        <h3>Schnellzugriff</h3>
        <p>
            <a class="button" href="<?= h(app_url('admin/event_form.php')) ?>">Neues Event</a>
            <a class="button secondary" href="<?= h(app_url('admin/events.php')) ?>">Events</a>
            <a class="button secondary" href="<?= h(app_url('admin/access_codes.php')) ?>">Codes</a>
            <a class="button secondary" href="<?= h(app_url('admin/users.php')) ?>">Benutzer</a>
            <a class="button secondary" href="<?= h(app_url('index.php')) ?>">Homepage</a>
        </p>
    </section>

    <section class="card">
        <h3>Nächste Events</h3>
        <ul>
            <?php foreach (array_slice($upcoming, 0, 10) as $event): ?>
                <li><?= h($event['event_date']) ?> – <?= h($event['name']) ?>
                    (<a href="<?= h(app_url('admin/slots.php?event_id=' . $event['id'])) ?>">Slots</a>)
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>
<?php render_footer(); ?>
