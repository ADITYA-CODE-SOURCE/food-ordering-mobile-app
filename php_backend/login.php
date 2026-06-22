<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if ($email === '' || $password === '') {
    sendResponse(false, 'Email and password are required.', null, 422);
}

$sql = 'SELECT id, name, email, phone, password FROM users WHERE email = ? LIMIT 1';
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendResponse(false, 'Invalid email or password.', null, 401);
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    sendResponse(false, 'Invalid email or password.', null, 401);
}

unset($user['password']);

sendResponse(true, 'Login successful.', ['user' => $user]);
