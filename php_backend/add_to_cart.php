<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$user = requireApiUser();

$data = getJsonInput();

$foodId = (int) ($data['food_id'] ?? 0);
$quantity = (int) ($data['quantity'] ?? 0);

if ($foodId <= 0 || $quantity <= 0) {
    sendResponse(false, 'Food id and quantity are required.', null, 422);
}

$foodSql = 'SELECT id, COALESCE(discount_price, base_price) AS unit_price, availability_status FROM foods WHERE id = ? LIMIT 1';
$foodStmt = $conn->prepare($foodSql);
$foodStmt->bind_param('i', $foodId);
$foodStmt->execute();
$food = $foodStmt->get_result()->fetch_assoc();

if (!$food) {
    sendResponse(false, 'Food item not found.', null, 404);
}

if (($food['availability_status'] ?? 'available') === 'sold_out') {
    sendResponse(false, 'This food item is currently unavailable.', null, 409);
}

$userId = (int) $user['id'];
$unitPrice = (float) $food['unit_price'];

$checkSql = 'SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ? AND variant_id IS NULL AND addons_summary IS NULL LIMIT 1';
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('ii', $userId, $foodId);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    $cartItem = $result->fetch_assoc();
    $newQuantity = (int) $cartItem['quantity'] + $quantity;
    $cartId = (int) $cartItem['id'];

    $updateSql = 'UPDATE cart SET quantity = ?, unit_price = ? WHERE id = ?';
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('idi', $newQuantity, $unitPrice, $cartId);

    if ($updateStmt->execute()) {
        sendResponse(true, 'Cart updated successfully.');
    }
} else {
    $insertSql = 'INSERT INTO cart (user_id, food_id, variant_id, quantity, unit_price, addons_summary) VALUES (?, ?, NULL, ?, ?, NULL)';
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param('iiid', $userId, $foodId, $quantity, $unitPrice);

    if ($insertStmt->execute()) {
        sendResponse(true, 'Item added to cart successfully.');
    }
}

sendResponse(false, 'Unable to add item to cart.', null, 500);
