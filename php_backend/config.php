<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

mysqli_report(MYSQLI_REPORT_OFF);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = getenv('DB_HOST') ?: 'localhost';
$port = (int) (getenv('DB_PORT') ?: 3306);
$username = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'foodapp_db';
$appKey = getenv('APP_KEY') ?: 'local-dev-insecure-key-change-me';

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

function abortJson(int $statusCode, string $message): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data' => null,
    ]);
    exit();
}

function base64UrlEncode(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function base64UrlDecode(string $value): string|false
{
    $padding = strlen($value) % 4;
    if ($padding > 0) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($value, '-_', '+/'), true);
}

function issueApiToken(array $user): string
{
    global $appKey;

    $payload = json_encode([
        'sub' => (int) $user['id'],
        'exp' => time() + (7 * 24 * 60 * 60),
    ], JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        abortJson(500, 'Unable to create access token.');
    }

    $encodedPayload = base64UrlEncode($payload);
    $signature = hash_hmac('sha256', $encodedPayload, $appKey, true);

    return $encodedPayload . '.' . base64UrlEncode($signature);
}

function getBearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', trim($header), $matches) !== 1) {
        return null;
    }

    return trim($matches[1]);
}

function validateApiToken(string $token): ?array
{
    global $appKey;

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
        return null;
    }

    [$encodedPayload, $encodedSignature] = $parts;
    $payloadJson = base64UrlDecode($encodedPayload);
    $signature = base64UrlDecode($encodedSignature);
    if ($payloadJson === false || $signature === false) {
        return null;
    }

    $expectedSignature = hash_hmac('sha256', $encodedPayload, $appKey, true);
    if (!hash_equals($expectedSignature, $signature)) {
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload) || empty($payload['sub']) || empty($payload['exp'])) {
        return null;
    }

    if ((int) $payload['exp'] < time()) {
        return null;
    }

    return $payload;
}

function requireApiUser(): array
{
    global $conn;

    $token = getBearerToken();
    if ($token === null) {
        abortJson(401, 'Authentication required.');
    }

    $payload = validateApiToken($token);
    if ($payload === null) {
        abortJson(401, 'Invalid or expired access token.');
    }

    $userId = (int) $payload['sub'];
    $stmt = $conn->prepare('SELECT id, name, email, phone, status FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        abortJson(500, 'Unable to verify access token.');
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || ($user['status'] ?? 'active') !== 'active') {
        abortJson(401, 'Account is not available.');
    }

    return $user;
}
