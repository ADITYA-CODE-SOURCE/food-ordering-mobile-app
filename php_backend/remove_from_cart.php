<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$user = requireApiUser();

$data = getJsonInput();

$foodId = (int) ($data['food_id'] ?? 0);

if ($foodId <= 0) {
    sendResponse(false, 'Food id is required.', null, 422);
}

$userId = (int) $user['id'];
$sql = 'DELETE FROM cart WHERE user_id = ? AND food_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $userId, $foodId);

if ($stmt->execute()) {
    sendResponse(true, 'Item removed from cart successfully.');
}

sendResponse(false, 'Unable to remove item from cart.', null, 500);
