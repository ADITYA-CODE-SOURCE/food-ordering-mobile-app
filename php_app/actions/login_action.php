<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = (string) ($_POST['password'] ?? '');

if (!$email || strlen($password) < 6) {
    set_flash('error', 'Enter a valid email and password.');
    redirect('../login.php');
}

$user = fetch_one($pdo, 'SELECT * FROM users WHERE email = ? AND status = "active"', [$email]);
if (!$user || !password_verify($password, $user['password'])) {
    set_flash('error', 'Invalid login credentials.');
    redirect('../login.php');
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'phone' => $user['phone'],
];
$pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
set_flash('success', 'Welcome back, ' . $user['name'] . '.');
redirect($user['role'] === 'admin' ? '../admin/index.php' : '../menu.php');
