<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$user = current_user();
$customerName = sanitize_text($_POST['customer_name'] ?? '');
$customerPhone = sanitize_text($_POST['customer_phone'] ?? '');
$deliveryAddress = sanitize_text($_POST['delivery_address'] ?? '');
$paymentMethod = sanitize_text($_POST['payment_method'] ?? 'Cash on Delivery');
$couponCode = strtoupper(sanitize_text($_POST['coupon_code'] ?? ''));
$notes = sanitize_text($_POST['notes'] ?? '');
$addressId = !empty($_POST['address_id']) ? (int) $_POST['address_id'] : null;

if ($customerName === '' || $customerPhone === '' || $deliveryAddress === '') {
    set_flash('error', 'Name, phone, and address are required.');
    redirect('../checkout.php');
}

if (!in_array($paymentMethod, allowed_payment_methods(), true)) {
    set_flash('error', 'Please select a valid payment method.');
    redirect('../checkout.php');
}

if ($addressId !== null) {
    $address = fetch_one($pdo, 'SELECT id FROM addresses WHERE id = ? AND user_id = ?', [$addressId, $user['id']]);
    if (!$address) {
        set_flash('error', 'Selected address is invalid.');
        redirect('../checkout.php');
    }
}

$cartItems = fetch_all($pdo, 'SELECT c.*, f.name FROM cart c INNER JOIN foods f ON f.id = c.food_id WHERE c.user_id = ?', [$user['id']]);
if (!$cartItems) {
    set_flash('error', 'Your cart is empty.');
    redirect('../cart.php');
}

$subtotal = array_reduce($cartItems, fn ($sum, $item) => $sum + ((float) $item['unit_price'] * (int) $item['quantity']), 0.0);
$deliveryFee = $subtotal >= 499 ? 0.0 : 40.0;
$discountAmount = 0.0;

if ($couponCode !== '') {
    $coupon = fetch_one($pdo, 'SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (usage_limit IS NULL OR used_count < usage_limit)', [$couponCode]);
    if ($coupon && $subtotal >= (float) $coupon['min_order_amount']) {
        $discountAmount = $coupon['discount_type'] === 'percentage'
            ? $subtotal * ((float) $coupon['discount_value'] / 100)
            : (float) $coupon['discount_value'];
        if (!empty($coupon['max_discount'])) {
            $discountAmount = min($discountAmount, (float) $coupon['max_discount']);
        }
        $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')->execute([$coupon['id']]);
    }
}

$totalAmount = max(0, $subtotal - $discountAmount + $deliveryFee);

$pdo->beginTransaction();

try {
    $orderNumber = build_order_number();
    $orderStmt = $pdo->prepare('INSERT INTO orders (order_number, user_id, address_id, customer_name, customer_phone, delivery_address, payment_method, payment_status, order_status, subtotal, discount_amount, delivery_fee, total_amount, coupon_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $orderStmt->execute([
        $orderNumber,
        $user['id'],
        $addressId,
        $customerName,
        $customerPhone,
        $deliveryAddress,
        $paymentMethod,
        $paymentMethod === 'Cash on Delivery' ? 'pending' : 'paid',
        'Pending',
        $subtotal,
        $discountAmount,
        $deliveryFee,
        $totalAmount,
        $couponCode ?: null,
        $notes ?: null,
    ]);

    $orderId = (int) $pdo->lastInsertId();
    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, food_id, variant_name, quantity, unit_price, total_price, addons_summary) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $soldStmt = $pdo->prepare('UPDATE foods SET sold_count = sold_count + ? WHERE id = ?');

    foreach ($cartItems as $item) {
        $variantName = null;
        if (!empty($item['variant_id'])) {
            $variant = fetch_one($pdo, 'SELECT variant_name FROM food_variants WHERE id = ?', [$item['variant_id']]);
            $variantName = $variant['variant_name'] ?? null;
        }

        $itemStmt->execute([
            $orderId,
            $item['food_id'],
            $variantName,
            $item['quantity'],
            $item['unit_price'],
            (float) $item['unit_price'] * (int) $item['quantity'],
            $item['addons_summary'] ?: null,
        ]);
        $soldStmt->execute([(int) $item['quantity'], (int) $item['food_id']]);
    }

    $pdo->prepare('INSERT INTO payments (order_id, payment_method, transaction_ref, amount, payment_status, paid_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([
        $orderId,
        $paymentMethod,
        $paymentMethod === 'Cash on Delivery' ? null : strtoupper(substr($paymentMethod, 0, 4)) . '-' . random_int(100000, 999999),
        $totalAmount,
        $paymentMethod === 'Cash on Delivery' ? 'pending' : 'success',
        $paymentMethod === 'Cash on Delivery' ? null : date('Y-m-d H:i:s'),
    ]);

    $pdo->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)')->execute([$orderId, 'Pending', 'Order placed successfully.']);
    $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)')->execute([$user['id'], 'Order placed', 'Your order ' . $orderNumber . ' has been placed successfully.']);
    $pdo->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$user['id']]);

    $pdo->commit();
    set_flash('success', 'Order placed successfully. Tracking ID: ' . $orderNumber);
    redirect('../orders.php');
} catch (Throwable $exception) {
    $pdo->rollBack();
    set_flash('error', 'Unable to place order right now.');
    redirect('../checkout.php');
}
