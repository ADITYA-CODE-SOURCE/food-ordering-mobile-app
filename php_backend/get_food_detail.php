<?php

require_once 'config.php';
require_once 'response.php';

requireGetMethod();

$foodId = isset($_GET['food_id']) ? (int) $_GET['food_id'] : 0;

if ($foodId <= 0) {
    sendResponse(false, 'Valid food id is required.', null, 422);
}

$sql = 'SELECT id, category_id, name, description, price, image, rating, is_available
        FROM food_items
        WHERE id = ? LIMIT 1';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $foodId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendResponse(false, 'Food item not found.', null, 404);
}

sendResponse(true, 'Food detail fetched successfully.', $result->fetch_assoc());
