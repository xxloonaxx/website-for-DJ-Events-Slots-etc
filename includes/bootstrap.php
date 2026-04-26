<?php
$config = require __DIR__ . '/../config.php';

date_default_timezone_set($config['app']['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($config['app']['session_name']);
    session_start();
}

function render_system_error(string $headline, string $message): void
{
    if (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
    }

    echo '<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>Systemhinweis</title><style>body{font-family:Arial,sans-serif;margin:0;background:#0f1224;color:#f0f4ff}main{max-width:900px;margin:4rem auto;padding:1.5rem}';
    echo '.box{background:#1a2040;border:1px solid #3a4678;border-radius:12px;padding:1rem 1.25rem}code{background:#0b0f22;padding:.15rem .35rem;border-radius:6px}</style></head><body>';
    echo '<main><div class="box"><h1>' . h($headline) . '</h1><p>' . nl2br(h($message)) . '</p>';
    echo '<p><strong>Bitte prüfen:</strong></p><ul><li>DB-Zugang in <code>config.php</code></li><li>MySQL läuft und Datenbank wurde per <code>sql/schema.sql</code> importiert</li><li>PHP-Erweiterungen <code>pdo</code> + <code>pdo_mysql</code> sind aktiv</li></ul>';
    echo '</div></main></body></html>';
    exit;
}

function db(): PDO
{
    static $pdo;
    global $config;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        render_system_error(
            'Datenbank-Treiber fehlt',
            'Auf dem Server fehlt pdo_mysql. Dadurch konnte keine Verbindung zur MySQL-Datenbank aufgebaut werden.'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['db']['host'],
        $config['db']['port'],
        $config['db']['database'],
        $config['db']['charset']
    );

    try {
        $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        render_system_error(
            'Datenbank-Verbindung fehlgeschlagen',
            "Die Website konnte nicht mit MySQL verbinden.\n\nFehler: " . $e->getMessage()
        );
    }

    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function flash(?string $key = null)
{
    if ($key === null) {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function add_flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function app_url(string $path = ''): string
{
    global $config;
    $base = rtrim($config['app']['base_url'], '/');
    return $base . '/' . ltrim($path, '/');
}
