<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/events.php';

require_admin();
$eventId = (int) ($_GET['id'] ?? 0);
if ($eventId > 0) {
    delete_event($eventId);
    add_flash('success', 'Event gelöscht.');
}
header('Location: ' . app_url('admin/events.php'));
exit;
