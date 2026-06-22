<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$userId = current_user()['id'];
$action = sanitize_text($_POST['action'] ?? 'add');

if ($action === 'remove') {
    $cartId = (int) ($_POST['cart_id'] ?? 0);
    $pdo->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?')->execute([$cartId, $userId]);
    set_flash('success', 'Item removed from cart.');
    redirect('../cart.php');
}

if ($action === 'update') {
    $cartId = (int) ($_POST['cart_id'] ?? 0);
    $quantity = max(1, min(10, (int) ($_POST['quantity'] ?? 1)));
    $pdo->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?')->execute([$quantity, $cartId, $userId]);
    set_flash('success', 'Cart updated.');
    redirect('../cart.php');
}

$foodId = (int) ($_POST['food_id'] ?? 0);
$variantId = !empty($_POST['variant_id']) ? (int) $_POST['variant_id'] : null;
$quantity = max(1, min(10, (int) ($_POST['quantity'] ?? 1)));
$addonIds = array_map('intval', $_POST['addons'] ?? []);

$food = fetch_one($pdo, 'SELECT * FROM foods WHERE id = ?', [$foodId]);
if (!$food) {
    set_flash('error', 'Food item not found.');
    redirect('../menu.php');
}

$unitPrice = (float) ($food['discount_price'] ?: $food['base_price']);
if ($variantId) {
    $variant = fetch_one($pdo, 'SELECT * FROM food_variants WHERE id = ? AND food_id = ?', [$variantId, $foodId]);
    if ($variant) {
        $unitPrice = (float) $variant['price'];
    }
}

$selectedAddons = [];
if ($addonIds !== []) {
    $addonPlaceholders = implode(',', array_fill(0, count($addonIds), '?'));
    $selectedAddons = fetch_all(
        $pdo,
        'SELECT a.id, a.name, a.price
         FROM food_addons fa
         INNER JOIN addons a ON a.id = fa.addon_id
         WHERE fa.food_id = ? AND a.is_active = 1 AND a.id IN (' . $addonPlaceholders . ')',
        array_merge([$foodId], $addonIds)
    );

    foreach ($selectedAddons as $addon) {
        $unitPrice += (float) $addon['price'];
    }
}

$addonsSummary = normalize_addons_summary($selectedAddons);

$existing = fetch_one(
    $pdo,
    'SELECT id, quantity FROM cart WHERE user_id = ? AND food_id = ? AND ((variant_id IS NULL AND ? IS NULL) OR variant_id = ?) AND COALESCE(addons_summary, "") = COALESCE(?, "")',
    [$userId, $foodId, $variantId, $variantId, $addonsSummary]
);
if ($existing) {
    $pdo->prepare('UPDATE cart SET quantity = ?, unit_price = ?, addons_summary = ? WHERE id = ?')->execute([(int) $existing['quantity'] + $quantity, $unitPrice, $addonsSummary, $existing['id']]);
} else {
    $pdo->prepare('INSERT INTO cart (user_id, food_id, variant_id, quantity, unit_price, addons_summary) VALUES (?, ?, ?, ?, ?, ?)')->execute([$userId, $foodId, $variantId, $quantity, $unitPrice, $addonsSummary]);
}

set_flash('success', 'Added to cart successfully.');
redirect('../cart.php');
