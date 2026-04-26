<?php

function get_events(bool $onlyPublic = false): array
{
    $sql = 'SELECT * FROM events';
    if ($onlyPublic) {
        $sql .= ' WHERE is_published = 1';
    }
    $sql .= ' ORDER BY event_date ASC, start_time ASC';

    return db()->query($sql)->fetchAll();
}

function get_next_public_event(): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM events
         WHERE is_published = 1 AND event_date >= :today
         ORDER BY event_date ASC, start_time ASC
         LIMIT 1'
    );
    $stmt->execute(['today' => date('Y-m-d')]);
    $event = $stmt->fetch();
    return $event ?: null;
}

function get_event(int $eventId): ?array
{
    $stmt = db()->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute(['id' => $eventId]);
    $event = $stmt->fetch();
    return $event ?: null;
}

function get_slots_for_event(int $eventId): array
{
    $stmt = db()->prepare(
        'SELECT s.*, b.id AS booking_id, b.dj_name, b.vrchat_name, b.note, b.created_at AS booked_at
         FROM event_slots s
         LEFT JOIN dj_bookings b ON b.slot_id = s.id
         WHERE s.event_id = :event_id
         ORDER BY s.slot_number ASC'
    );
    $stmt->execute(['event_id' => $eventId]);
    return $stmt->fetchAll();
}

function get_event_access_codes(int $eventId): array
{
    $stmt = db()->prepare(
        'SELECT c.*
         FROM dj_access_codes c
         INNER JOIN event_access_code_map m ON m.access_code_id = c.id
         WHERE m.event_id = :event_id
         ORDER BY c.label ASC'
    );
    $stmt->execute(['event_id' => $eventId]);
    return $stmt->fetchAll();
}

function get_all_access_codes(bool $onlyActive = false): array
{
    $sql = 'SELECT * FROM dj_access_codes';
    if ($onlyActive) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY created_at DESC';

    return db()->query($sql)->fetchAll();
}

function event_exists_duplicate(array $data, int $ignoreId = 0): bool
{
    $sql = 'SELECT COUNT(*) FROM events WHERE name = :name AND event_date = :event_date';
    $params = [
        'name' => $data['name'],
        'event_date' => $data['event_date'],
    ];

    if ($ignoreId > 0) {
        $sql .= ' AND id != :ignore_id';
        $params['ignore_id'] = $ignoreId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn() > 0;
}

function create_event(array $data, array $accessCodeIds = []): int
{
    if (event_exists_duplicate($data)) {
        throw new RuntimeException('Ein Event mit diesem Namen und Datum existiert bereits.');
    }

    $stmt = db()->prepare(
        'INSERT INTO events
        (name, event_date, genre_theme, slot_count, slot_duration_min, description, vrchat_world, start_time, banner_path, is_published)
        VALUES
        (:name, :event_date, :genre_theme, :slot_count, :slot_duration_min, :description, :vrchat_world, :start_time, :banner_path, :is_published)'
    );

    $stmt->execute([
        'name' => $data['name'],
        'event_date' => $data['event_date'],
        'genre_theme' => $data['genre_theme'],
        'slot_count' => $data['slot_count'],
        'slot_duration_min' => $data['slot_duration_min'],
        'description' => $data['description'],
        'vrchat_world' => $data['vrchat_world'],
        'start_time' => $data['start_time'],
        'banner_path' => $data['banner_path'],
        'is_published' => $data['is_published'],
    ]);

    $eventId = (int) db()->lastInsertId();
    sync_event_slots_preserve_bookings($eventId);
    set_event_access_codes($eventId, $accessCodeIds);

    return $eventId;
}

function update_event(int $eventId, array $data, array $accessCodeIds = []): void
{
    if (event_exists_duplicate($data, $eventId)) {
        throw new RuntimeException('Es gibt bereits ein anderes Event mit diesem Namen und Datum.');
    }

    $existing = get_event($eventId);
    if (!$existing) {
        throw new RuntimeException('Event nicht gefunden.');
    }

    // Wenn Anzahl Slots reduziert wird, verhindern wir Datenverlust bei bereits belegten Slots.
    if ((int) $data['slot_count'] < (int) $existing['slot_count']) {
        $stmt = db()->prepare(
            'SELECT COUNT(*)
             FROM event_slots s
             INNER JOIN dj_bookings b ON b.slot_id = s.id
             WHERE s.event_id = :event_id AND s.slot_number > :new_slot_count'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'new_slot_count' => $data['slot_count'],
        ]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException('Es sind bereits DJs in Slots eingetragen, die du entfernen würdest. Bitte zuerst umplanen oder Slots freigeben.');
        }
    }

    $stmt = db()->prepare(
        'UPDATE events SET
            name = :name,
            event_date = :event_date,
            genre_theme = :genre_theme,
            slot_count = :slot_count,
            slot_duration_min = :slot_duration_min,
            description = :description,
            vrchat_world = :vrchat_world,
            start_time = :start_time,
            banner_path = :banner_path,
            is_published = :is_published,
            updated_at = CURRENT_TIMESTAMP
         WHERE id = :id'
    );

    $stmt->execute([
        'id' => $eventId,
        'name' => $data['name'],
        'event_date' => $data['event_date'],
        'genre_theme' => $data['genre_theme'],
        'slot_count' => $data['slot_count'],
        'slot_duration_min' => $data['slot_duration_min'],
        'description' => $data['description'],
        'vrchat_world' => $data['vrchat_world'],
        'start_time' => $data['start_time'],
        'banner_path' => $data['banner_path'],
        'is_published' => $data['is_published'],
    ]);

    sync_event_slots_preserve_bookings($eventId);
    set_event_access_codes($eventId, $accessCodeIds);
}

function delete_event(int $eventId): void
{
    $stmt = db()->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute(['id' => $eventId]);
}

function duplicate_event(int $eventId): int
{
    $event = get_event($eventId);
    if (!$event) {
        throw new RuntimeException('Event nicht gefunden.');
    }

    $newData = $event;
    $newData['name'] = $event['name'] . ' (Kopie)';
    $newData['event_date'] = date('Y-m-d', strtotime($event['event_date'] . ' +7 days'));
    $newData['is_published'] = 0;

    $linkedCodeIds = array_map(static fn(array $row): int => (int) $row['id'], get_event_access_codes($eventId));

    return create_event($newData, $linkedCodeIds);
}

function sync_event_slots_preserve_bookings(int $eventId): void
{
    $event = get_event($eventId);
    if (!$event) {
        return;
    }

    $existingSlots = db()->prepare('SELECT id, slot_number FROM event_slots WHERE event_id = :event_id ORDER BY slot_number ASC');
    $existingSlots->execute(['event_id' => $eventId]);
    $byNumber = [];
    foreach ($existingSlots->fetchAll() as $slot) {
        $byNumber[(int) $slot['slot_number']] = (int) $slot['id'];
    }

    $start = new DateTimeImmutable($event['event_date'] . ' ' . $event['start_time']);

    $updateStmt = db()->prepare('UPDATE event_slots SET starts_at = :starts_at, ends_at = :ends_at WHERE id = :id');
    $insertStmt = db()->prepare(
        'INSERT INTO event_slots (event_id, slot_number, starts_at, ends_at)
         VALUES (:event_id, :slot_number, :starts_at, :ends_at)'
    );

    for ($i = 1; $i <= (int) $event['slot_count']; $i++) {
        $slotStart = $start->modify('+' . (($i - 1) * (int) $event['slot_duration_min']) . ' minutes');
        $slotEnd = $slotStart->modify('+' . (int) $event['slot_duration_min'] . ' minutes');

        $params = [
            'starts_at' => $slotStart->format('Y-m-d H:i:s'),
            'ends_at' => $slotEnd->format('Y-m-d H:i:s'),
        ];

        if (isset($byNumber[$i])) {
            $params['id'] = $byNumber[$i];
            $updateStmt->execute($params);
        } else {
            $insertStmt->execute([
                'event_id' => $eventId,
                'slot_number' => $i,
                'starts_at' => $params['starts_at'],
                'ends_at' => $params['ends_at'],
            ]);
        }
    }

    // Überschüssige freie Slots entfernen.
    if ((int) $event['slot_count'] > 0) {
        $stmt = db()->prepare(
            'DELETE s FROM event_slots s
             LEFT JOIN dj_bookings b ON b.slot_id = s.id
             WHERE s.event_id = :event_id
             AND s.slot_number > :slot_count
             AND b.id IS NULL'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'slot_count' => $event['slot_count'],
        ]);
    }
}

function set_event_access_codes(int $eventId, array $accessCodeIds): void
{
    db()->prepare('DELETE FROM event_access_code_map WHERE event_id = :event_id')->execute(['event_id' => $eventId]);

    $insert = db()->prepare('INSERT INTO event_access_code_map (event_id, access_code_id) VALUES (:event_id, :access_code_id)');
    foreach ($accessCodeIds as $codeId) {
        $insert->execute([
            'event_id' => $eventId,
            'access_code_id' => (int) $codeId,
        ]);
    }
}

function get_access_code(string $code): ?array
{
    $stmt = db()->prepare('SELECT * FROM dj_access_codes WHERE code = :code AND is_active = 1');
    $stmt->execute(['code' => $code]);
    $record = $stmt->fetch();

    if (!$record) {
        return null;
    }

    if (!empty($record['expires_at']) && new DateTimeImmutable($record['expires_at']) < new DateTimeImmutable()) {
        return null;
    }

    return $record;
}

function get_events_for_access_code(int $accessCodeId): array
{
    $stmt = db()->prepare(
        'SELECT e.*
         FROM events e
         WHERE e.is_published = 1
         AND (
            NOT EXISTS (SELECT 1 FROM event_access_code_map m WHERE m.event_id = e.id)
            OR EXISTS (SELECT 1 FROM event_access_code_map m2 WHERE m2.event_id = e.id AND m2.access_code_id = :access_code_id)
         )
         ORDER BY e.event_date ASC, e.start_time ASC'
    );
    $stmt->execute(['access_code_id' => $accessCodeId]);
    return $stmt->fetchAll();
}

function upsert_booking(int $slotId, string $djName, string $vrchatName, string $note, ?int $accessCodeId = null): void
{
    $existing = db()->prepare('SELECT id FROM dj_bookings WHERE slot_id = :slot_id');
    $existing->execute(['slot_id' => $slotId]);
    $booking = $existing->fetch();

    if ($booking) {
        db()->prepare('UPDATE dj_bookings SET dj_name=:dj_name, vrchat_name=:vrchat_name, note=:note, access_code_id=:access_code_id WHERE slot_id=:slot_id')
            ->execute([
                'slot_id' => $slotId,
                'dj_name' => $djName,
                'vrchat_name' => $vrchatName,
                'note' => $note,
                'access_code_id' => $accessCodeId,
            ]);
        return;
    }

    db()->prepare('INSERT INTO dj_bookings (slot_id, dj_name, vrchat_name, note, access_code_id) VALUES (:slot_id,:dj_name,:vrchat_name,:note,:access_code_id)')
        ->execute([
            'slot_id' => $slotId,
            'dj_name' => $djName,
            'vrchat_name' => $vrchatName,
            'note' => $note,
            'access_code_id' => $accessCodeId,
        ]);
}

function delete_booking(int $slotId): void
{
    db()->prepare('DELETE FROM dj_bookings WHERE slot_id = :slot_id')->execute(['slot_id' => $slotId]);
}

function get_admin_stats(): array
{
    $today = date('Y-m-d');
    $stats = [];
    $stats['events_total'] = (int) db()->query('SELECT COUNT(*) FROM events')->fetchColumn();
    $stmt = db()->prepare('SELECT COUNT(*) FROM events WHERE event_date >= :today');
    $stmt->execute(['today' => $today]);
    $stats['events_upcoming'] = (int) $stmt->fetchColumn();
    $stats['slots_total'] = (int) db()->query('SELECT COUNT(*) FROM event_slots')->fetchColumn();
    $stats['slots_booked'] = (int) db()->query('SELECT COUNT(*) FROM dj_bookings')->fetchColumn();
    $stats['slots_free'] = max(0, $stats['slots_total'] - $stats['slots_booked']);

    return $stats;
}
