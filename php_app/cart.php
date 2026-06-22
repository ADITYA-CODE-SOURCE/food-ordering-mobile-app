<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$userId = current_user()['id'];
$cartItems = fetch_all($pdo, 'SELECT c.*, f.name, f.image, fv.variant_name
    FROM cart c
    INNER JOIN foods f ON f.id = c.food_id
    LEFT JOIN food_variants fv ON fv.id = c.variant_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC', [$userId]);

$subtotal = 0.0;
foreach ($cartItems as $item) {
    $subtotal += (float) $item['unit_price'] * (int) $item['quantity'];
}

$pageTitle = 'Cart | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="section-head">
    <div>
        <h1 class="section-title" style="font-size:34px;">Your cart</h1>
        <p class="section-subtitle" style="color:var(--muted);">Review items before checkout. Empty cart has a polished fallback state.</p>
    </div>
</section>

<?php if (!$cartItems): ?>
    <section class="empty-state">
        <i class="fa-solid fa-bag-shopping"></i>
        <h2>Your cart feels lonely</h2>
        <p class="muted">Browse the dynamic menu, add items to favorites, and come back here to complete your checkout.</p>
        <a class="button" href="menu.php">Explore menu</a>
    </section>
<?php else: ?>
    <div class="two-column">
        <section class="grid">
            <?php foreach ($cartItems as $item): ?>
                <article class="panel">
                    <div style="display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:center;">
                        <img src="<?= e(food_image($item['image'])) ?>" alt="<?= e($item['name']) ?>" style="width:120px;height:110px;object-fit:cover;border-radius:16px;" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';">
                        <div>
                            <div class="tag-row"><strong><?= e($item['name']) ?></strong><span class="badge info"><?= e($item['variant_name'] ?: 'Standard') ?></span></div>
                            <p class="muted">Unit price: <?= format_currency((float) $item['unit_price']) ?></p>
                            <?php if (!empty($item['addons_summary'])): ?>
                                <p class="muted">Add-ons: <?= e($item['addons_summary']) ?></p>
                            <?php endif; ?>
                            <div class="card-actions">
                                <form action="actions/cart_action.php" method="post" style="display:flex;gap:10px;align-items:center;">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="number" name="quantity" min="1" max="10" value="<?= (int) $item['quantity'] ?>" style="width:90px;">
                                    <button type="submit">Update</button>
                                </form>
                                <form action="actions/cart_action.php" method="post">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button class="button ghost" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        <aside class="panel">
            <h2 style="margin-top:0;">Summary</h2>
            <ul class="list-clean">
                <li><span>Items</span><strong><?= count($cartItems) ?></strong></li>
                <li><span>Subtotal</span><strong><?= format_currency($subtotal) ?></strong></li>
                <li><span>Delivery fee</span><strong><?= format_currency($subtotal >= 499 ? 0 : 40) ?></strong></li>
            </ul>
            <div class="price-row" style="margin-top:18px;"><strong>Total</strong><strong><?= format_currency($subtotal >= 499 ? $subtotal : $subtotal + 40) ?></strong></div>
            <a class="button" style="width:100%;display:inline-flex;justify-content:center;" href="checkout.php">Proceed to checkout</a>
        </aside>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
