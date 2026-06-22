<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();

$userId = (int) ($data['user_id'] ?? 0);
$address = trim($data['address'] ?? '');
$totalAmount = (float) ($data['total_amount'] ?? 0);
$items = $data['items'] ?? [];

if ($userId <= 0 || $address === '' || $totalAmount <= 0 || empty($items)) {
    sendResponse(false, 'User id, address, total amount, and items are required.', null, 422);
}

$conn->begin_transaction();

try {
    $orderStatus = 'Pending';
    $orderSql = 'INSERT INTO orders (user_id, total_amount, address, order_status) VALUES (?, ?, ?, ?)';
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param('idss', $userId, $totalAmount, $address, $orderStatus);
    $orderStmt->execute();

    $orderId = $conn->insert_id;

    $itemSql = 'INSERT INTO order_items (order_id, food_id, quantity, price) VALUES (?, ?, ?, ?)';
    $itemStmt = $conn->prepare($itemSql);

    foreach ($items as $item) {
        $foodId = (int) ($item['food_id'] ?? 0);
        $quantity = (int) ($item['quantity'] ?? 0);
        $price = (float) ($item['price'] ?? 0);

        if ($foodId <= 0 || $quantity <= 0 || $price <= 0) {
            throw new Exception('Invalid order item found.');
        }

        $itemStmt->bind_param('iiid', $orderId, $foodId, $quantity, $price);
        $itemStmt->execute();
    }

    $clearCartSql = 'DELETE FROM cart WHERE user_id = ?';
    $clearCartStmt = $conn->prepare($clearCartSql);
    $clearCartStmt->bind_param('i', $userId);
    $clearCartStmt->execute();

    $conn->commit();
    sendResponse(true, 'Order placed successfully.', ['order_id' => $orderId]);
} catch (Exception $exception) {
    $conn->rollback();
    sendResponse(false, 'Unable to place order. ' . $exception->getMessage(), null, 500);
}
