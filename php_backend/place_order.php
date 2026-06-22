<?php

require_once 'config.php';
require_once 'response.php';

requirePostMethod();

$user = requireApiUser();

$data = getJsonInput();

$address = trim($data['address'] ?? '');
$items = $data['items'] ?? [];

if ($address === '' || empty($items) || !is_array($items)) {
    sendResponse(false, 'Address and items are required.', null, 422);
}

$userId = (int) $user['id'];
$normalizedItems = [];
$subtotal = 0.0;

foreach ($items as $item) {
    $foodId = (int) ($item['food_id'] ?? 0);
    $quantity = (int) ($item['quantity'] ?? 0);

    if ($foodId <= 0 || $quantity <= 0) {
        sendResponse(false, 'Invalid order item found.', null, 422);
    }

    $foodStmt = $conn->prepare('SELECT id, name, COALESCE(discount_price, base_price) AS unit_price, availability_status FROM foods WHERE id = ? LIMIT 1');
    $foodStmt->bind_param('i', $foodId);
    $foodStmt->execute();
    $food = $foodStmt->get_result()->fetch_assoc();

    if (!$food) {
        sendResponse(false, 'One of the selected food items no longer exists.', null, 404);
    }

    if (($food['availability_status'] ?? 'available') === 'sold_out') {
        sendResponse(false, 'One of the selected food items is unavailable.', null, 409);
    }

    $unitPrice = (float) $food['unit_price'];
    $lineTotal = $unitPrice * $quantity;
    $subtotal += $lineTotal;
    $normalizedItems[] = [
        'food_id' => $foodId,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'line_total' => $lineTotal,
    ];
}

$orderNumber = 'ORD-' . date('Ymd') . '-' . random_int(1000, 9999);
$paymentMethod = 'Cash on Delivery';
$paymentStatus = 'pending';
$orderStatus = 'Pending';
$discountAmount = 0.0;
$deliveryFee = 0.0;
$totalAmount = $subtotal;

$conn->begin_transaction();

try {
    $orderSql = 'INSERT INTO orders (order_number, user_id, address_id, customer_name, customer_phone, delivery_address, payment_method, payment_status, order_status, subtotal, discount_amount, delivery_fee, total_amount, coupon_code, notes) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL)';
    $orderStmt = $conn->prepare($orderSql);
    $customerName = $user['name'];
    $customerPhone = $user['phone'];
    $orderStmt->bind_param('sissssssdddd', $orderNumber, $userId, $customerName, $customerPhone, $address, $paymentMethod, $paymentStatus, $orderStatus, $subtotal, $discountAmount, $deliveryFee, $totalAmount);
    $orderStmt->execute();

    $orderId = $conn->insert_id;

    $itemSql = 'INSERT INTO order_items (order_id, food_id, variant_name, quantity, unit_price, total_price, addons_summary) VALUES (?, ?, NULL, ?, ?, ?, NULL)';
    $itemStmt = $conn->prepare($itemSql);
    $historyStmt = $conn->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)');
    $paymentStmt = $conn->prepare('INSERT INTO payments (order_id, payment_method, transaction_ref, amount, payment_status, paid_at) VALUES (?, ?, NULL, ?, ?, NULL)');
    $notificationStmt = $conn->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
    $soldStmt = $conn->prepare('UPDATE foods SET sold_count = sold_count + ? WHERE id = ?');

    foreach ($normalizedItems as $item) {
        $foodId = (int) $item['food_id'];
        $quantity = (int) $item['quantity'];
        $unitPrice = (float) $item['unit_price'];
        $lineTotal = (float) $item['line_total'];

        $itemStmt->bind_param('iiidd', $orderId, $foodId, $quantity, $unitPrice, $lineTotal);
        $itemStmt->execute();
        $soldStmt->bind_param('ii', $quantity, $foodId);
        $soldStmt->execute();
    }

    $clearCartSql = 'DELETE FROM cart WHERE user_id = ?';
    $clearCartStmt = $conn->prepare($clearCartSql);
    $clearCartStmt->bind_param('i', $userId);
    $clearCartStmt->execute();

    $historyNote = 'Order placed successfully.';
    $historyStmt->bind_param('iss', $orderId, $orderStatus, $historyNote);
    $historyStmt->execute();

    $paymentStmt->bind_param('isds', $orderId, $paymentMethod, $totalAmount, $paymentStatus);
    $paymentStmt->execute();

    $notificationTitle = 'Order placed';
    $notificationMessage = 'Your order ' . $orderNumber . ' has been placed successfully.';
    $notificationStmt->bind_param('iss', $userId, $notificationTitle, $notificationMessage);
    $notificationStmt->execute();

    $conn->commit();
    sendResponse(true, 'Order placed successfully.', ['order_id' => $orderId, 'order_number' => $orderNumber]);
} catch (Exception $exception) {
    $conn->rollback();
    sendResponse(false, 'Unable to place order. ' . $exception->getMessage(), null, 500);
}
