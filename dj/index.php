<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/events.php';

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$access = $code ? get_access_code($code) : null;

if (!$access) {
    render_header('DJ Zugang', 'dj');
    ?>
    <main class="container narrow">
        <h2>DJ Zugang</h2>
        <div class="card">
            <p>Dieser Bereich ist nur mit einem gültigen DJ Access-Code erreichbar.</p>
            <p>Bitte verwende den Link, den du vom Team erhalten hast (z. B. <code>?code=DEINCODE</code>).</p>
        </div>
    </main>
    <?php
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slot_id'])) {
    $slotId = (int) $_POST['slot_id'];
    $djName = trim($_POST['dj_name'] ?? '');
    $vrchatName = trim($_POST['vrchat_name'] ?? '');
    $note = trim($_POST['note'] ?? '');

    $slotStmt = db()->prepare('SELECT s.id, s.event_id FROM event_slots s LEFT JOIN dj_bookings b ON b.slot_id=s.id WHERE s.id=:slot_id AND b.id IS NULL');
    $slotStmt->execute(['slot_id' => $slotId]);
    $slot = $slotStmt->fetch();

    if ($slot && $djName !== '' && $vrchatName !== '') {
        db()->prepare('INSERT INTO dj_bookings (slot_id, dj_name, vrchat_name, note, access_code_id) VALUES (:slot_id,:dj_name,:vrchat_name,:note,:access_code_id)')
            ->execute([
                'slot_id' => $slotId,
                'dj_name' => $djName,
                'vrchat_name' => $vrchatName,
                'note' => $note,
                'access_code_id' => $access['id'],
            ]);
        add_flash('success', 'Slot erfolgreich reserviert!');
    } else {
        add_flash('error', 'Slot nicht verfügbar oder Angaben unvollständig.');
    }

    header('Location: ' . app_url('dj/index.php?code=' . urlencode($code)));
    exit;
}

$events = get_events(true);

render_header('DJ Slot Anmeldung', 'dj');
?>
<main class="container">
    <section class="card">
        <h2>DJ Bereich</h2>
        <p><strong>Access:</strong> <?= h($access['label']) ?> (Code: <?= h($access['code']) ?>)</p>
        <p>Trage dich hier direkt in freie Slots ein.</p>
    </section>

    <?php foreach ($events as $event): ?>
        <?php $slots = get_slots_for_event((int) $event['id']); ?>
        <section class="card">
            <h3><?= h($event['name']) ?> – <?= h($event['event_date']) ?></h3>
            <p><strong>Genre:</strong> <?= h($event['genre_theme']) ?> | <strong>World:</strong> <?= h($event['vrchat_world']) ?></p>
            <p><?= nl2br(h($event['description'])) ?></p>

            <div class="slot-grid">
                <?php foreach ($slots as $slot): ?>
                    <div class="slot <?= $slot['dj_name'] ? 'booked' : 'free' ?>">
                        <p><strong>Slot <?= h((string) $slot['slot_number']) ?></strong></p>
                        <p><?= h(date('H:i', strtotime($slot['starts_at']))) ?> - <?= h(date('H:i', strtotime($slot['ends_at']))) ?></p>
                        <?php if ($slot['dj_name']): ?>
                            <p>Belegt von: <?= h($slot['dj_name']) ?></p>
                        <?php else: ?>
                            <form method="post" class="form-grid compact">
                                <input type="hidden" name="code" value="<?= h($code) ?>">
                                <input type="hidden" name="slot_id" value="<?= h((string) $slot['id']) ?>">
                                <input name="dj_name" placeholder="DJ Name" required>
                                <input name="vrchat_name" placeholder="VRChat Name" required>
                                <textarea name="note" rows="2" placeholder="Notiz / Set Info"></textarea>
                                <button class="button" type="submit">Jetzt eintragen</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<?php render_footer(); ?>
