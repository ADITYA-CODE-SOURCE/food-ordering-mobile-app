<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_post_request('login.php');
require_csrf('login.php');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
}

session_destroy();
session_start();
set_flash('success', 'Logged out successfully.');
redirect('login.php');
