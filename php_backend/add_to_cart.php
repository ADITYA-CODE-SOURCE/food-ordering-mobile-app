<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();

$userId = (int) ($data['user_id'] ?? 0);
$foodId = (int) ($data['food_id'] ?? 0);
$quantity = (int) ($data['quantity'] ?? 0);

if ($userId <= 0 || $foodId <= 0 || $quantity <= 0) {
    sendResponse(false, 'User id, food id, and quantity are required.', null, 422);
}

$checkSql = 'SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ? LIMIT 1';
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('ii', $userId, $foodId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $cartItem = $result->fetch_assoc();
    $newQuantity = (int) $cartItem['quantity'] + $quantity;

    $updateSql = 'UPDATE cart SET quantity = ? WHERE id = ?';
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('ii', $newQuantity, $cartItem['id']);

    if ($updateStmt->execute()) {
        sendResponse(true, 'Cart updated successfully.');
    }
} else {
    $insertSql = 'INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, ?)';
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('iii', $userId, $foodId, $quantity);

    if ($insertStmt->execute()) {
        sendResponse(true, 'Item added to cart successfully.');
    }
}

sendResponse(false, 'Unable to add item to cart.', null, 500);
