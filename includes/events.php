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
        'SELECT s.*, b.dj_name, b.vrchat_name, b.note
         FROM event_slots s
         LEFT JOIN dj_bookings b ON b.slot_id = s.id
         WHERE s.event_id = :event_id
         ORDER BY s.slot_number ASC'
    );
    $stmt->execute(['event_id' => $eventId]);
    return $stmt->fetchAll();
}

function create_event(array $data): int
{
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
    regenerate_slots($eventId);
    return $eventId;
}

function update_event(int $eventId, array $data): void
{
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
            is_published = :is_published
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

    regenerate_slots($eventId);
}

function delete_event(int $eventId): void
{
    $stmt = db()->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute(['id' => $eventId]);
}

function regenerate_slots(int $eventId): void
{
    $event = get_event($eventId);
    if (!$event) {
        return;
    }

    db()->prepare('DELETE b FROM dj_bookings b INNER JOIN event_slots s ON s.id = b.slot_id WHERE s.event_id = :event_id')
        ->execute(['event_id' => $eventId]);
    db()->prepare('DELETE FROM event_slots WHERE event_id = :event_id')->execute(['event_id' => $eventId]);

    $start = new DateTimeImmutable($event['event_date'] . ' ' . $event['start_time']);

    $slotStmt = db()->prepare(
        'INSERT INTO event_slots (event_id, slot_number, starts_at, ends_at)
         VALUES (:event_id, :slot_number, :starts_at, :ends_at)'
    );

    for ($i = 1; $i <= (int) $event['slot_count']; $i++) {
        $slotStart = $start->modify('+' . (($i - 1) * (int) $event['slot_duration_min']) . ' minutes');
        $slotEnd = $slotStart->modify('+' . (int) $event['slot_duration_min'] . ' minutes');
        $slotStmt->execute([
            'event_id' => $eventId,
            'slot_number' => $i,
            'starts_at' => $slotStart->format('Y-m-d H:i:s'),
            'ends_at' => $slotEnd->format('Y-m-d H:i:s'),
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
