<?php

require_once 'config.php';
require_once 'response.php';

requireGetMethod();

$sql = 'SELECT id, name, image FROM categories ORDER BY name ASC';
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

sendResponse(true, 'Categories fetched successfully.', $categories);
