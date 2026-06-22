<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

require_post_request('../profile.php');
require_csrf('../profile.php');

$userId = current_user()['id'];
$action = sanitize_text($_POST['action'] ?? 'profile');

if ($action === 'address') {
    $label = sanitize_text($_POST['label'] ?? 'Home');
    $contactName = sanitize_text($_POST['contact_name'] ?? '');
    $phone = sanitize_text($_POST['address_phone'] ?? '');
    $addressLine = sanitize_text($_POST['address_line'] ?? '');
    $city = sanitize_text($_POST['city'] ?? '');
    $state = sanitize_text($_POST['state'] ?? '');
    $postalCode = sanitize_text($_POST['postal_code'] ?? '');

    if ($contactName === '' || $phone === '' || $addressLine === '' || $city === '') {
        set_flash('error', 'Please complete all required address fields.');
        redirect('../profile.php');
    }

    $pdo->prepare('INSERT INTO addresses (user_id, label, contact_name, phone, address_line, city, state, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$userId, $label, $contactName, $phone, $addressLine, $city, $state, $postalCode]);
    set_flash('success', 'Address added successfully.');
    redirect('../profile.php');
}

$name = sanitize_text($_POST['name'] ?? '');
$phone = sanitize_text($_POST['phone'] ?? '');
if ($name === '' || $phone === '') {
    set_flash('error', 'Name and phone are required.');
    redirect('../profile.php');
}

$pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')->execute([$name, $phone, $userId]);
$_SESSION['user']['name'] = $name;
$_SESSION['user']['phone'] = $phone;
set_flash('success', 'Profile updated.');
redirect('../profile.php');
