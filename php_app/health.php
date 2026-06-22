<?php

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$pdo->query('SELECT 1');

echo json_encode([
    'success' => true,
    'status' => 'ok',
    'service' => 'food-ordering-mobile-app',
    'database' => 'ok',
]);
