<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/layout.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/events.php';

require_admin();

$eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$event = $eventId ? get_event($eventId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'event_date' => $_POST['event_date'] ?? '',
        'genre_theme' => trim($_POST['genre_theme'] ?? ''),
        'slot_count' => max(1, (int) ($_POST['slot_count'] ?? 1)),
        'slot_duration_min' => max(15, (int) ($_POST['slot_duration_min'] ?? 60)),
        'description' => trim($_POST['description'] ?? ''),
        'vrchat_world' => trim($_POST['vrchat_world'] ?? ''),
        'start_time' => $_POST['start_time'] ?? '18:00',
        'banner_path' => trim($_POST['banner_path'] ?? ''),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];

    if ($eventId) {
        update_event($eventId, $data);
        add_flash('success', 'Event aktualisiert.');
    } else {
        create_event($data);
        add_flash('success', 'Event erstellt.');
    }

    header('Location: ' . app_url('admin/events.php'));
    exit;
}

$defaults = $event ?: [
    'name' => '',
    'event_date' => date('Y-m-d'),
    'genre_theme' => '',
    'slot_count' => 6,
    'slot_duration_min' => 60,
    'description' => '',
    'vrchat_world' => '',
    'start_time' => '18:00',
    'banner_path' => '',
    'is_published' => 0,
];

render_header($eventId ? 'Event bearbeiten' : 'Neues Event', 'admin');
?>
<main class="container narrow">
    <h2><?= $eventId ? 'Event bearbeiten' : 'Neues Event erstellen' ?></h2>
    <form method="post" class="card form-grid">
        <label>Event-Name
            <input name="name" value="<?= h($defaults['name']) ?>" required>
        </label>
        <label>Datum
            <input type="date" name="event_date" value="<?= h($defaults['event_date']) ?>" required>
        </label>
        <label>Genre / Thema
            <input name="genre_theme" value="<?= h($defaults['genre_theme']) ?>" required>
        </label>
        <label>VRChat World
            <input name="vrchat_world" value="<?= h($defaults['vrchat_world']) ?>" required>
        </label>
        <label>Startzeit
            <input type="time" name="start_time" value="<?= h(substr($defaults['start_time'], 0, 5)) ?>" required>
        </label>
        <label>Zeitslots (Anzahl)
            <input type="number" min="1" max="50" name="slot_count" value="<?= h((string) $defaults['slot_count']) ?>" required>
        </label>
        <label>Slot-Länge (Minuten)
            <input type="number" min="15" step="15" name="slot_duration_min" value="<?= h((string) $defaults['slot_duration_min']) ?>" required>
        </label>
        <label>Banner-Bild Pfad (optional)
            <input name="banner_path" value="<?= h($defaults['banner_path']) ?>" placeholder="uploads/banner.jpg">
        </label>
        <label class="full">Beschreibung
            <textarea name="description" rows="5" required><?= h($defaults['description']) ?></textarea>
        </label>
        <label class="checkbox full">
            <input type="checkbox" name="is_published" value="1" <?= $defaults['is_published'] ? 'checked' : '' ?>>
            Event veröffentlichen
        </label>
        <button type="submit" class="button">Speichern</button>
    </form>
</main>
<?php render_footer(); ?>
