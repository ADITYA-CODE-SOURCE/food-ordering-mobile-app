<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('INSERT INTO coupons (code, title, discount_type, discount_value, min_order_amount, max_discount, expires_at, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)')->execute([
        strtoupper(sanitize_text($_POST['code'] ?? '')),
        sanitize_text($_POST['title'] ?? ''),
        sanitize_text($_POST['discount_type'] ?? 'percentage'),
        (float) ($_POST['discount_value'] ?? 0),
        (float) ($_POST['min_order_amount'] ?? 0),
        $_POST['max_discount'] !== '' ? (float) $_POST['max_discount'] : null,
        $_POST['expires_at'] ?: null,
        $_POST['usage_limit'] !== '' ? (int) $_POST['usage_limit'] : null,
    ]);
    set_flash('success', 'Coupon created.');
    redirect('coupons.php');
}

$coupons = fetch_all($pdo, 'SELECT * FROM coupons ORDER BY created_at DESC');
$pageTitle = 'Coupons | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section class="grid">
        <div class="panel">
            <h1 class="section-title" style="font-size:34px;">Coupon engine</h1>
            <form method="post" data-loading-form>
                <div class="form-grid">
                    <div><label>Code</label><input type="text" name="code" required></div>
                    <div><label>Title</label><input type="text" name="title" required></div>
                    <div><label>Type</label><select name="discount_type"><option value="percentage">percentage</option><option value="fixed">fixed</option></select></div>
                    <div><label>Discount value</label><input type="number" step="0.01" name="discount_value" required></div>
                    <div><label>Min order</label><input type="number" step="0.01" name="min_order_amount" required></div>
                    <div><label>Max discount</label><input type="number" step="0.01" name="max_discount"></div>
                    <div><label>Expiry</label><input type="datetime-local" name="expires_at"></div>
                    <div><label>Usage limit</label><input type="number" name="usage_limit"></div>
                    <div class="full"><button type="submit">Create coupon</button></div>
                </div>
            </form>
        </div>
        <div class="table-card"><table><thead><tr><th>Code</th><th>Discount</th><th>Rule</th><th>Expiry</th><th>Usage</th></tr></thead><tbody><?php foreach ($coupons as $coupon): ?><tr><td><?= e($coupon['code']) ?><br><small class="muted"><?= e($coupon['title']) ?></small></td><td><?= e($coupon['discount_type']) ?> / <?= e((string) $coupon['discount_value']) ?></td><td>Min <?= format_currency((float) $coupon['min_order_amount']) ?></td><td><?= e($coupon['expires_at'] ?: 'No expiry') ?></td><td><?= (int) $coupon['used_count'] ?> used</td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
