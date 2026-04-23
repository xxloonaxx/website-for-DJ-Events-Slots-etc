<?php

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['is_admin']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        add_flash('error', 'Bitte zuerst als Admin einloggen.');
        header('Location: ' . app_url('admin/login.php'));
        exit;
    }
}

function verify_admin_password(string $password): bool
{
    global $config;
    return hash_equals($config['app']['admin_password'], $password);
}

function login_admin(): void
{
    $_SESSION['is_admin'] = true;
}

function logout_admin(): void
{
    unset($_SESSION['is_admin']);
}
