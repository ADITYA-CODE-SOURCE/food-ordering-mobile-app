<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$user = requireApiUser();

$userId = (int) $user['id'];

$orderSql = 'SELECT id, total_amount, delivery_address AS address, order_status, placed_at AS created_at
             FROM orders
             WHERE user_id = ?
             ORDER BY id DESC';
$orderStmt = $conn->prepare($orderSql);
$orderStmt->bind_param('i', $userId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

$orders = [];

while ($order = $orderResult->fetch_assoc()) {
    $orderId = (int) $order['id'];
    $itemSql = 'SELECT oi.food_id, oi.quantity, oi.unit_price AS price, f.name AS food_name
                FROM order_items oi
                INNER JOIN foods f ON f.id = oi.food_id
                WHERE oi.order_id = ?';
    $itemStmt = $conn->prepare($itemSql);
    $itemStmt->bind_param('i', $orderId);
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
