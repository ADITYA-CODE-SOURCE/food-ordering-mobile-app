<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();

$userId = (int) ($data['user_id'] ?? 0);
$foodId = (int) ($data['food_id'] ?? 0);

if ($userId <= 0 || $foodId <= 0) {
    sendResponse(false, 'User id and food id are required.', null, 422);
}

$sql = 'DELETE FROM cart WHERE user_id = ? AND food_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $userId, $foodId);

if ($stmt->execute()) {
    sendResponse(true, 'Item removed from cart successfully.');
}

sendResponse(false, 'Unable to remove item from cart.', null, 500);
