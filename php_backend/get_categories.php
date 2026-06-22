<?php

require_once 'config.php';
require_once 'response.php';

requireGetMethod();

$sql = 'SELECT id, name, COALESCE(image, "") AS image FROM categories WHERE is_active = 1 ORDER BY name ASC';
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

sendResponse(true, 'Categories fetched successfully.', $categories);
