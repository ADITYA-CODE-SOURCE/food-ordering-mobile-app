<?php

require_once 'config.php';
require_once 'response.php';

requireGetMethod();

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

if ($categoryId > 0) {
    $sql = 'SELECT id,
                   category_id,
                   name,
                   short_description AS description,
                   COALESCE(discount_price, base_price) AS price,
                   COALESCE(image, "") AS image,
                   rating,
                   CASE WHEN availability_status = "sold_out" THEN 0 ELSE 1 END AS is_available
            FROM foods
            WHERE category_id = ?
            ORDER BY id DESC';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = 'SELECT id,
                   category_id,
                   name,
                   short_description AS description,
                   COALESCE(discount_price, base_price) AS price,
                   COALESCE(image, "") AS image,
                   rating,
                   CASE WHEN availability_status = "sold_out" THEN 0 ELSE 1 END AS is_available
            FROM foods
            ORDER BY id DESC';
    $result = $conn->query($sql);
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

sendResponse(true, 'Food items fetched successfully.', $items);
