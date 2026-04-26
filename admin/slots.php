<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/events.php';

require_admin();
$eventId = (int) ($_GET['event_id'] ?? 0);
$event = get_event($eventId);
if (!$event) {
    add_flash('error', 'Event nicht gefunden.');
    header('Location: ' . app_url('admin/events.php'));
    exit;
}

if (isset($_GET['clear_slot'])) {
    $slotId = (int) $_GET['clear_slot'];
    db()->prepare('DELETE FROM dj_bookings WHERE slot_id = :slot_id')->execute(['slot_id' => $slotId]);
    add_flash('success', 'Slot-Buchung entfernt.');
    header('Location: ' . app_url('admin/slots.php?event_id=' . $eventId));
    exit;
}

$slots = get_slots_for_event($eventId);

render_header('Slots - ' . $event['name'], 'admin');
?>
<main class="container">
    <div class="section-head">
        <h2>Slots: <?= h($event['name']) ?></h2>
        <a class="button secondary" href="<?= h(app_url('admin/events.php')) ?>">Zurück</a>
    </div>

    <div class="table-wrap card">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Zeit</th>
                <th>Status</th>
                <th>DJ</th>
                <th>VRChat</th>
                <th>Notiz</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?= h((string) $slot['slot_number']) ?></td>
                    <td><?= h(date('H:i', strtotime($slot['starts_at']))) ?> - <?= h(date('H:i', strtotime($slot['ends_at']))) ?></td>
                    <td><?= $slot['dj_name'] ? 'Belegt' : 'Frei' ?></td>
                    <td><?= h($slot['dj_name'] ?? '-') ?></td>
                    <td><?= h($slot['vrchat_name'] ?? '-') ?></td>
                    <td><?= h($slot['note'] ?? '-') ?></td>
                    <td>
                        <?php if ($slot['dj_name']): ?>
                            <a href="<?= h(app_url('admin/slots.php?event_id=' . $eventId . '&clear_slot=' . $slot['id'])) ?>">Freigeben</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php render_footer(); ?>
