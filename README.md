# VRChat DJ Event & Slot System (PHP + MySQL)

Internes System für:
- **Admin-Bereich** zum Erstellen/Verwalten von Events.
- **DJ-Bereich** mit **Access-Code per URL** für Slot-Eintragungen.
- **Startseite** zur Veröffentlichung von Events.
- Platzhalter für **Logo** und **Banner**.

## Features

- Event-Felder:
  - Name
  - Datum
  - Genre/Thema
  - Anzahl Zeitslots
  - Slotdauer (Standard 60 Minuten)
  - Beschreibung
  - VRChat World
  - Startzeit
  - optionaler Banner-Pfad
- Automatische Slot-Generierung beim Speichern eines Events
- DJ-Selbsteintragung auf freie Slots
- Admin-Ansicht zum Freigeben belegter Slots
- Verwaltung von DJ Access-Codes (aktiv/inaktiv + optionales Ablaufdatum)

## Installation

1. Datenbank anlegen und Schema importieren:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
2. `config.php` anpassen:
   - DB Zugangsdaten
   - `admin_password` **sofort ändern**
   - optional `base_url`
3. Webserver auf Projektordner zeigen lassen.

## Wichtige URLs

- Startseite: `/index.php`
- Admin Login: `/admin/login.php`
- DJ Zugang: `/dj/index.php?code=DEINCODE`

## Sicherheitshinweise (für Produktion)

- Admin-Passwort als Hash + Benutzerverwaltung statt Klartext.
- HTTPS erzwingen.
- CSRF-Tokens ergänzen.
- Upload-Handling für Banner absichern (mime/size checks).

## ZIP-Datei

Hinweis: Die ZIP wird **nicht** im Git-Repository versioniert (damit PRs ohne Binärdatei-Probleme funktionieren).

Erstelle sie lokal bei Bedarf:
```bash
zip -r dj-slot-system.zip . -x ".git/*" "*.zip"
```


## Troubleshooting: HTTP ERROR 500

Wenn die komplette Seite mit **HTTP ERROR 500** ausfällt, sind fast immer diese Punkte die Ursache:

1. `config.php` enthält falsche DB-Zugangsdaten
2. Die Datenbank wurde noch nicht mit `sql/schema.sql` importiert
3. PHP-Erweiterung `pdo_mysql` ist auf dem Server nicht aktiv

Ab dieser Version zeigt die App bei DB-Problemen eine verständliche Systemseite statt eines generischen 500-Fehlers.
