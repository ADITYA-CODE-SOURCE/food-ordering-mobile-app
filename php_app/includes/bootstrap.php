<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'food_ordering_app';
$dbUser = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);

    $message = 'Database connection failed. Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD.';

    if (str_ends_with($dbHost, '.railway.internal')) {
        $message = 'Database connection failed. DB_HOST uses a Railway private hostname, which Render cannot resolve. Use Railway public TCP proxy host/port or another public MySQL host.';
    }

    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
        ]);
    } else {
        echo '<h1>Database connection failed</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    exit();
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
