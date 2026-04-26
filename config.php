<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'dj_slot_system',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'VRChat DJ Slot System',
        'base_url' => '',
        'timezone' => 'Europe/Berlin',
        'session_name' => 'dj_slot_admin',
        // Nur für Erstinstallation: wird automatisch gehasht in admin_users gespeichert.
        'default_admin_username' => 'admin',
        'default_admin_password' => 'BitteSofortAendern123!',
    ],
];
