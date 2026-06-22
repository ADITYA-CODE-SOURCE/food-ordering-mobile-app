<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';

$name = sanitize_text($_POST['name'] ?? '');
$phone = sanitize_text($_POST['phone'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = (string) ($_POST['password'] ?? '');

if ($name === '' || $phone === '' || !$email || strlen($password) < 8) {
    set_flash('error', 'All fields are required and password must be at least 8 characters.');
    redirect('../register.php');
}

$exists = fetch_one($pdo, 'SELECT id FROM users WHERE email = ?', [$email]);
if ($exists) {
    set_flash('error', 'Email already registered.');
    redirect('../register.php');
}

$stmt = $pdo->prepare('INSERT INTO users (role, name, email, phone, password) VALUES ("customer", ?, ?, ?, ?)');
$stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
set_flash('success', 'Registration successful. Please login.');
redirect('../login.php');
