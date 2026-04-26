CREATE DATABASE IF NOT EXISTS dj_slot_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dj_slot_system;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    event_date DATE NOT NULL,
    genre_theme VARCHAR(190) NOT NULL,
    slot_count INT NOT NULL,
    slot_duration_min INT NOT NULL DEFAULT 60,
    description TEXT NOT NULL,
    vrchat_world VARCHAR(190) NOT NULL,
    start_time TIME NOT NULL DEFAULT '18:00:00',
    banner_path VARCHAR(255) NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event_name_date (name, event_date)
);

CREATE TABLE IF NOT EXISTS event_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    slot_number INT NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slot_no (event_id, slot_number),
    CONSTRAINT fk_slots_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(190) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dj_access_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    label VARCHAR(190) NOT NULL,
    expires_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_access_code_map (
    event_id INT NOT NULL,
    access_code_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, access_code_id),
    CONSTRAINT fk_eacm_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT fk_eacm_code FOREIGN KEY (access_code_id) REFERENCES dj_access_codes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS dj_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL UNIQUE,
    dj_name VARCHAR(190) NOT NULL,
    vrchat_name VARCHAR(190) NOT NULL,
    note TEXT NULL,
    access_code_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_slot FOREIGN KEY (slot_id) REFERENCES event_slots(id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_code FOREIGN KEY (access_code_id) REFERENCES dj_access_codes(id) ON DELETE SET NULL
);
