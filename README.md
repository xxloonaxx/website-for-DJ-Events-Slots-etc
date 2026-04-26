# VRChat DJ Event & Slot System (PHP + MySQL)

Internes System für:
- **Admin-Bereich** zum Erstellen/Verwalten von Events
- **DJ-Bereich** mit **Access-Code per URL**
- **Startseite** mit Fokus auf nächstes Event + Slot-Übersicht

## Neue Kernfunktionen

- Slot-Buchungen bleiben beim Event-Edit **erhalten**, sofern möglich
- Startseite zeigt das **nächste Event im Vordergrund** + alle Slots sichtbar
- Access-Codes können direkt mit Events verknüpft werden
- Admin kann Events **duplizieren**, publish togglen, Dashboard nutzen
- Banner-System mit Datei-Upload (`jpg/jpeg/png/webp/gif`)
- Eingeloggte Admins können auf der DJ-Seite bestehende Buchungen bearbeiten/löschen
- Schutz vor Duplikat-Events (Name + Datum)
- **Mehrere Admin-Benutzer** mit Benutzerverwaltung
- **Passwörter werden gehasht** (`password_hash` / `password_verify`)

## Installation

1. Datenbank anlegen und Schema importieren:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. `config.php` anpassen:
   - DB Zugangsdaten
   - `default_admin_username` und `default_admin_password` setzen
   - optional `base_url`
3. Schreibrechte für Uploads setzen:
   ```bash
   mkdir -p uploads/banners
   chmod -R 775 uploads
   ```
4. Einmal in `/admin/login.php` einloggen. Der erste Admin aus `config.php` wird automatisch angelegt, falls noch kein Benutzer existiert.

## Wichtige URLs

- Startseite: `/index.php`
- Admin Login: `/admin/login.php`
- Admin Dashboard: `/admin/dashboard.php`
- Admin Benutzerverwaltung: `/admin/users.php`
- DJ Zugang: `/dj/index.php?code=DEINCODE`

## ZIP-Datei

Hinweis: ZIP-Dateien werden nicht versioniert.

Lokal erstellen:
```bash
zip -r dj-slot-system.zip . -x ".git/*" "*.zip"
```

## Troubleshooting: HTTP ERROR 500

Wenn die komplette Seite mit **HTTP ERROR 500** ausfällt, sind fast immer diese Punkte die Ursache:

1. `config.php` enthält falsche DB-Zugangsdaten
2. Die Datenbank wurde noch nicht mit `sql/schema.sql` importiert
3. PHP-Erweiterung `pdo_mysql` ist auf dem Server nicht aktiv
