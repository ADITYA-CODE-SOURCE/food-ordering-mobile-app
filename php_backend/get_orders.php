<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$data = getJsonInput();
$userId = (int) ($data['user_id'] ?? 0);

if ($userId <= 0) {
    sendResponse(false, 'Valid user id is required.', null, 422);
}

$orderSql = 'SELECT id, total_amount, address, order_status, created_at
             FROM orders
             WHERE user_id = ?
             ORDER BY id DESC';
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param('i', $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

$orders = [];

while ($order = $orderResult->fetch_assoc()) {
    $itemSql = 'SELECT oi.food_id, oi.quantity, oi.price, fi.name AS food_name
                FROM order_items oi
                INNER JOIN food_items fi ON fi.id = oi.food_id
                WHERE oi.order_id = ?';
    $itemStmt = $conn->prepare($itemSql);
    $itemStmt->bind_param('i', $order['id']);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    $items = [];
    while ($item = $itemResult->fetch_assoc()) {
        $items[] = $item;
    }

    $order['items'] = $items;
    $orders[] = $order;
}

sendResponse(true, 'Orders fetched successfully.', $orders);
