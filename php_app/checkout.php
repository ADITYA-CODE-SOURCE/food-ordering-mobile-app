<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$cartItems = fetch_all($pdo, 'SELECT c.*, f.name FROM cart c INNER JOIN foods f ON f.id = c.food_id WHERE c.user_id = ?', [$user['id']]);
if (!$cartItems) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

$addresses = fetch_all($pdo, 'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$user['id']]);
$coupons = fetch_all($pdo, 'SELECT * FROM coupons WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY id DESC');
$subtotal = array_reduce($cartItems, fn ($sum, $item) => $sum + ((float) $item['unit_price'] * (int) $item['quantity']), 0.0);
$defaultAddress = $addresses ? $addresses[0]['address_line'] . ', ' . $addresses[0]['city'] : '';

$pageTitle = 'Checkout | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<div class="two-column">
    <section class="panel">
        <div class="section-head" style="margin-top:0;">
            <div>
                <h1 class="section-title" style="font-size:34px;">Checkout</h1>
                <p class="section-subtitle" style="color:var(--muted);">Complete your order with contact details, delivery address, payment method, and coupon support.</p>
            </div>
        </div>
        <form action="actions/place_order_action.php" method="post" data-loading-form>
            <?= csrf_input() ?>
            <div class="form-grid">
                <div>
                    <label>Name</label>
                    <input type="text" name="customer_name" value="<?= e($user['name']) ?>" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="customer_phone" value="<?= e($user['phone']) ?>" required>
                </div>
                <div class="full">
                    <label>Saved address</label>
                    <select name="address_id">
                        <option value="">Select saved address</option>
                        <?php foreach ($addresses as $address): ?>
                            <option value="<?= (int) $address['id'] ?>"><?= e($address['label']) ?> - <?= e($address['address_line']) ?>, <?= e($address['city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="full">
                    <label>Delivery address</label>
                    <textarea name="delivery_address" required><?= e($defaultAddress) ?></textarea>
                </div>
                <div>
                    <label>Payment method</label>
                    <select name="payment_method" required>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="UPI">UPI</option>
                        <option value="Google Pay">Google Pay</option>
                        <option value="PhonePe">PhonePe</option>
                        <option value="Paytm">Paytm</option>
                    </select>
                </div>
                <div>
                    <label>Coupon code</label>
                    <input list="coupon-list" name="coupon_code" placeholder="WELCOME20 / FLAT100">
                    <datalist id="coupon-list">
                        <?php foreach ($coupons as $coupon): ?>
                            <option value="<?= e($coupon['code']) ?>"><?= e($coupon['title']) ?></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="full">
                    <label>Order notes</label>
                    <textarea name="notes" placeholder="Delivery instructions, no onion request, etc."></textarea>
                </div>
                <div class="full">
                    <button type="submit">Place order</button>
                </div>
            </div>
        </form>
    </section>
    <aside class="panel">
        <h2 style="margin-top:0;">Cart summary</h2>
        <ul class="list-clean">
            <?php foreach ($cartItems as $item): ?>
                <li><span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></span><strong><?= format_currency((float) $item['unit_price'] * (int) $item['quantity']) ?></strong></li>
            <?php endforeach; ?>
        </ul>
        <div class="price-row" style="margin-top:18px;"><span>Subtotal</span><strong><?= format_currency($subtotal) ?></strong></div>
        <div class="price-row"><span>Estimated delivery</span><strong><?= format_currency($subtotal >= 499 ? 0 : 40) ?></strong></div>
        <div class="price-row"><strong>Payable now</strong><strong><?= format_currency($subtotal >= 499 ? $subtotal : $subtotal + 40) ?></strong></div>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
