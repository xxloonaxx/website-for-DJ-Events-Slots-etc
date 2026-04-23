<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/events.php';

require_admin();
$events = get_events(false);

render_header('Admin - Events verwalten', 'admin');
?>
<main class="container">
    <div class="section-head">
        <h2>Event-Verwaltung</h2>
        <div>
            <a class="button" href="<?= h(app_url('admin/event_form.php')) ?>">Neues Event</a>
            <a class="button secondary" href="<?= h(app_url('admin/access_codes.php')) ?>">DJ Access-Codes</a>
            <a class="button secondary" href="<?= h(app_url('admin/logout.php')) ?>">Logout</a>
        </div>
    </div>

    <div class="table-wrap card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Datum</th>
                <th>Genre</th>
                <th>World</th>
                <th>Status</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?= h($event['name']) ?></td>
                    <td><?= h($event['event_date']) ?> <?= h(substr($event['start_time'], 0, 5)) ?></td>
                    <td><?= h($event['genre_theme']) ?></td>
                    <td><?= h($event['vrchat_world']) ?></td>
                    <td><?= $event['is_published'] ? 'Veröffentlicht' : 'Entwurf' ?></td>
                    <td class="actions">
                        <a href="<?= h(app_url('admin/event_form.php?id=' . $event['id'])) ?>">Bearbeiten</a>
                        <a href="<?= h(app_url('admin/slots.php?event_id=' . $event['id'])) ?>">Slots</a>
                        <a href="<?= h(app_url('admin/event_delete.php?id=' . $event['id'])) ?>" onclick="return confirm('Event wirklich löschen?')">Löschen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php render_footer(); ?>
