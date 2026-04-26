<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/events.php';
require __DIR__ . '/../includes/auth.php';

$isAdmin = is_admin_logged_in();
$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$access = $code ? get_access_code($code) : null;

if (!$isAdmin && !$access) {
    render_header('DJ Zugang', 'dj');
    ?>
    <main class="container narrow">
        <h2>DJ Zugang</h2>
        <div class="card">
            <p>Dieser Bereich ist nur mit einem gültigen DJ Access-Code erreichbar.</p>
            <p>Bitte verwende den Link, den du vom Team erhalten hast (z. B. <code>?code=DEINCODE</code>).</p>
            <p><small>Als eingeloggter Admin kannst du ohne Code auf die DJ-Verwaltung zugreifen.</small></p>
        </div>
    </main>
    <?php
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'book';
    $slotId = (int) ($_POST['slot_id'] ?? 0);

    if ($action === 'delete_booking' && $isAdmin) {
        delete_booking($slotId);
        add_flash('success', 'Buchung entfernt.');
    } elseif (in_array($action, ['book', 'edit_booking'], true)) {
        $djName = trim($_POST['dj_name'] ?? '');
        $vrchatName = trim($_POST['vrchat_name'] ?? '');
        $note = trim($_POST['note'] ?? '');

        $slotStmt = db()->prepare('SELECT s.id FROM event_slots s WHERE s.id=:slot_id');
        $slotStmt->execute(['slot_id' => $slotId]);
        $slot = $slotStmt->fetch();

        if ($slot && $djName !== '' && $vrchatName !== '') {
            if (!$isAdmin && $action === 'book') {
                $freeCheck = db()->prepare('SELECT b.id FROM dj_bookings b WHERE b.slot_id = :slot_id');
                $freeCheck->execute(['slot_id' => $slotId]);
                if ($freeCheck->fetch()) {
                    add_flash('error', 'Slot ist nicht mehr verfügbar.');
                    header('Location: ' . app_url('dj/index.php?code=' . urlencode($code)));
                    exit;
                }
            }

            upsert_booking($slotId, $djName, $vrchatName, $note, $access['id'] ?? null);
            add_flash('success', $action === 'edit_booking' ? 'Buchung aktualisiert.' : 'Slot erfolgreich reserviert!');
        } else {
            add_flash('error', 'Slot nicht verfügbar oder Angaben unvollständig.');
        }
    }

    $redirect = 'dj/index.php';
    if ($code !== '') {
        $redirect .= '?code=' . urlencode($code);
    }

    header('Location: ' . app_url($redirect));
    exit;
}

$events = $isAdmin
    ? get_events(true)
    : get_events_for_access_code((int) $access['id']);

render_header('DJ Slot Anmeldung', 'dj');
?>
<main class="container">
    <section class="card">
        <h2>DJ Bereich</h2>
        <?php if ($isAdmin): ?>
            <p><strong>Admin-Modus:</strong> Du kannst alle veröffentlichten Events sehen und bestehende Buchungen direkt bearbeiten.</p>
        <?php else: ?>
            <p><strong>Access:</strong> <?= h($access['label']) ?> (Code: <?= h($access['code']) ?>)</p>
            <p>Trage dich hier direkt in freie Slots ein.</p>
        <?php endif; ?>
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
                            <p>Belegt von: <?= h($slot['dj_name']) ?> (<?= h($slot['vrchat_name']) ?>)</p>
                            <p><?= h($slot['note'] ?: '-') ?></p>

                            <?php if ($isAdmin): ?>
                                <form method="post" class="form-grid compact">
                                    <input type="hidden" name="code" value="<?= h($code) ?>">
                                    <input type="hidden" name="slot_id" value="<?= h((string) $slot['id']) ?>">
                                    <input type="hidden" name="action" value="edit_booking">
                                    <input name="dj_name" value="<?= h($slot['dj_name']) ?>" required>
                                    <input name="vrchat_name" value="<?= h($slot['vrchat_name']) ?>" required>
                                    <textarea name="note" rows="2"><?= h($slot['note']) ?></textarea>
                                    <button class="button warning" type="submit">Buchung bearbeiten</button>
                                </form>
                                <form method="post" style="margin-top:.4rem;">
                                    <input type="hidden" name="code" value="<?= h($code) ?>">
                                    <input type="hidden" name="slot_id" value="<?= h((string) $slot['id']) ?>">
                                    <input type="hidden" name="action" value="delete_booking">
                                    <button class="button secondary" type="submit">Buchung löschen</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <form method="post" class="form-grid compact">
                                <input type="hidden" name="code" value="<?= h($code) ?>">
                                <input type="hidden" name="slot_id" value="<?= h((string) $slot['id']) ?>">
                                <input type="hidden" name="action" value="book">
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
