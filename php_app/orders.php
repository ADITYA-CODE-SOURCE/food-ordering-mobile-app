<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$orders = fetch_all($pdo, 'SELECT * FROM orders WHERE user_id = ? ORDER BY placed_at DESC', [current_user()['id']]);
$pageTitle = 'Orders | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="section-head">
    <div>
        <h1 class="section-title" style="font-size:34px;">Order history</h1>
        <p class="section-subtitle" style="color:var(--muted);">Track statuses from pending to delivered, and review each order timeline.</p>
    </div>
</section>
<?php if (!$orders): ?>
    <section class="empty-state"><i class="fa-solid fa-receipt"></i><h2>No orders yet</h2><p class="muted">Your future order timeline will appear here.</p><a class="button" href="menu.php">Start ordering</a></section>
<?php else: ?>
    <div class="grid">
        <?php foreach ($orders as $order): ?>
            <?php $items = fetch_all($pdo, 'SELECT oi.*, f.name FROM order_items oi INNER JOIN foods f ON f.id = oi.food_id WHERE order_id = ?', [$order['id']]); ?>
            <?php $history = fetch_all($pdo, 'SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC', [$order['id']]); ?>
            <article class="panel">
                <div class="section-head" style="margin-top:0;">
                    <div>
                        <div class="tag-row"><strong><?= e($order['order_number']) ?></strong><span class="<?= order_badge_class($order['order_status']) ?>"><?= e($order['order_status']) ?></span></div>
                        <p class="section-subtitle" style="color:var(--muted);margin-top:10px;">Placed on <?= e(date('d M Y, h:i A', strtotime($order['placed_at']))) ?> | <?= e($order['payment_method']) ?></p>
                    </div>
                    <div><strong><?= format_currency((float) $order['total_amount']) ?></strong></div>
                </div>
                <div class="two-column" style="grid-template-columns:1.2fr 1fr;">
                    <div>
                        <h3>Items</h3>
                        <ul class="list-clean">
                            <?php foreach ($items as $item): ?>
                                <li><span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?><?php if (!empty($item['addons_summary'])): ?><br><small class="muted">Add-ons: <?= e($item['addons_summary']) ?></small><?php endif; ?></span><strong><?= format_currency((float) $item['total_price']) ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <h3>Tracking timeline</h3>
                        <div class="order-track">
                            <?php foreach ($history as $step): ?>
                                <div class="order-track-step"><div><strong><?= e($step['status']) ?></strong><p class="meta-copy"><?= e($step['note'] ?? '') ?></p><small class="muted"><?= e(date('d M, h:i A', strtotime($step['created_at']))) ?></small></div></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
