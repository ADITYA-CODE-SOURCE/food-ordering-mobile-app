<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = trim($data['password'] ?? '');

if ($name === '' || $email === '' || $phone === '' || $password === '') {
    sendResponse(false, 'All fields are required.', null, 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Please enter a valid email address.', null, 422);
}

if (strlen($password) < 8) {
    sendResponse(false, 'Password must be at least 8 characters long.', null, 422);
}

$checkSql = 'SELECT id FROM users WHERE email = ?';
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('s', $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    sendResponse(false, 'Email already registered.', null, 409);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = 'INSERT INTO users (role, name, email, phone, password, status) VALUES ("customer", ?, ?, ?, ?, "active")';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $name, $email, $phone, $hashedPassword);

if ($stmt->execute()) {
    sendResponse(true, 'Registration successful. You can now login.');
}

sendResponse(false, 'Registration failed.', null, 500);
