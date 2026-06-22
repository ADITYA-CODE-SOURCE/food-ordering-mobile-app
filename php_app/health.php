<?php

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'status' => 'ok',
    'service' => 'food-ordering-mobile-app',
]);
