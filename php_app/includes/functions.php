<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function sanitize_text(?string $value): string
{
    return trim(filter_var((string) $value, FILTER_SANITIZE_SPECIAL_CHARS));
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function cart_count(PDO $pdo): int
{
    $user = current_user();
    if (!$user) {
        return 0;
    }

    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?');
    $stmt->execute([$user['id']]);

    return (int) $stmt->fetchColumn();
}

function fetch_all(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(PDO $pdo, string $sql, array $params = []): ?array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
    return trim($value, '-');
}

function order_badge_class(string $status): string
{
    return match ($status) {
        'Delivered' => 'badge success',
        'Cancelled' => 'badge danger',
        'Out For Delivery', 'Ready' => 'badge info',
        'Preparing', 'Accepted' => 'badge warning',
        default => 'badge muted',
    };
}

function format_currency(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

function handle_upload(string $field, string $targetDir): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        throw new RuntimeException('Only jpg, jpeg, png, and webp images are allowed.');
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $filename = uniqid('food_', true) . '.' . $ext;
    $destination = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    return 'uploads/foods/' . $filename;
}

function build_order_number(): string
{
    return 'ORD-' . date('Ymd') . '-' . random_int(1000, 9999);
}

function allowed_payment_methods(): array
{
    return ['Cash on Delivery', 'UPI', 'Google Pay', 'PhonePe', 'Paytm'];
}

function normalize_addons_summary(array $addonRows): ?string
{
    if ($addonRows === []) {
        return null;
    }

    usort($addonRows, static fn (array $left, array $right): int => strcmp($left['name'], $right['name']));

    $parts = array_map(
        static fn (array $addon): string => sprintf('%s (%s)', $addon['name'], format_currency((float) $addon['price'])),
        $addonRows
    );

    return implode(', ', $parts);
}

function order_status_note(string $status): string
{
    return match ($status) {
        'Accepted' => 'Order accepted by the restaurant team.',
        'Preparing' => 'Your meal is being prepared in the kitchen.',
        'Ready' => 'Order packed and ready for pickup.',
        'Out For Delivery' => 'Order handed over to the delivery partner.',
        'Delivered' => 'Order delivered successfully.',
        'Cancelled' => 'Order was cancelled by the admin team.',
        default => 'Order status updated successfully.',
    };
}

function asset_path(string $path): string
{
    global $basePath;
    return ($basePath ?? '') . ltrim($path, '/');
}

function food_image(?string $image): string
{
    $image = trim((string) $image);
    if ($image === '') {
        return asset_path('assets/img/food-placeholder.svg');
    }

    if (preg_match('/^https?:\/\//i', $image) === 1) {
        return $image;
    }

    return asset_path($image);
}
