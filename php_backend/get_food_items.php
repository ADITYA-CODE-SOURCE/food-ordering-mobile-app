<?php

require_once 'config.php';
require_once 'response.php';

requireGetMethod();

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

if ($categoryId > 0) {
    $sql = 'SELECT id, category_id, name, description, price, image, rating, is_available
            FROM food_items
            WHERE category_id = ?
            ORDER BY id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = 'SELECT id, category_id, name, description, price, image, rating, is_available
            FROM food_items
            ORDER BY id DESC';
    $result = $conn->query($sql);
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

sendResponse(true, 'Food items fetched successfully.', $items);
