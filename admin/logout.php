<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
logout_admin();
add_flash('success', 'Abgemeldet.');
header('Location: ' . app_url('admin/login.php'));
exit;
