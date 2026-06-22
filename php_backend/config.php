<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

mysqli_report(MYSQLI_REPORT_OFF);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = (int) (getenv('DB_PORT') ?: 3306);
$username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'food_ordering_app';

$conn = @new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    $message = 'Database connection failed.';

    if (str_ends_with($host, '.railway.internal')) {
        $message = 'Database connection failed. DB_HOST uses a Railway private hostname, which Render cannot resolve. Use Railway public TCP proxy host/port or another public MySQL host.';
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit();
}

$conn->set_charset('utf8mb4');

function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}
