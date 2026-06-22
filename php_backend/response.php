<?php

function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

function requirePostMethod() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Only POST method is allowed.', null, 405);
    }
}

function requireGetMethod() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendResponse(false, 'Only GET method is allowed.', null, 405);
    }
}
