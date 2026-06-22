<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$totals = [
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn(),
    'orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'pending' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status IN ("Pending", "Accepted", "Preparing")')->fetchColumn(),
    'completed' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE order_status = "Delivered"')->fetchColumn(),
    'active_customers' => (int) $pdo->query('SELECT COUNT(DISTINCT user_id) FROM orders')->fetchColumn(),
];

$todayRevenue = (float) $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(placed_at) = CURDATE() AND order_status != "Cancelled"')->fetchColumn();
$monthlyRevenue = (float) $pdo->query('SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE YEAR(placed_at) = YEAR(CURDATE()) AND MONTH(placed_at) = MONTH(CURDATE()) AND order_status != "Cancelled"')->fetchColumn();
$topFoods = fetch_all($pdo, 'SELECT f.name, SUM(oi.quantity) AS total_qty, SUM(oi.total_price) AS total_sales FROM order_items oi INNER JOIN foods f ON f.id = oi.food_id GROUP BY oi.food_id ORDER BY total_qty DESC LIMIT 6');
$recentOrders = fetch_all($pdo, 'SELECT order_number, customer_name, total_amount, order_status, payment_method, placed_at FROM orders ORDER BY placed_at DESC LIMIT 8');

$pageTitle = 'Admin Dashboard | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section class="grid">
        <div class="section-head" style="margin-top:0;">
            <div>
                <h1 class="section-title" style="font-size:34px;">Admin dashboard</h1>
                <p class="section-subtitle" style="color:var(--muted);">Analytics dashboard with revenue, order health, user counts, and top-selling foods.</p>
            </div>
        </div>
        <div class="stats-grid">
            <article class="stats-card"><p>Total users</p><strong><?= $totals['users'] ?></strong></article>
            <article class="stats-card"><p>Total orders</p><strong><?= $totals['orders'] ?></strong></article>
            <article class="stats-card"><p>Today's revenue</p><strong><?= format_currency($todayRevenue) ?></strong></article>
            <article class="stats-card"><p>Pending orders</p><strong><?= $totals['pending'] ?></strong></article>
            <article class="stats-card"><p>Completed orders</p><strong><?= $totals['completed'] ?></strong></article>
            <article class="stats-card"><p>Monthly revenue</p><strong><?= format_currency($monthlyRevenue) ?></strong></article>
        </div>
        <div class="two-column">
            <section class="panel">
                <div class="section-head" style="margin-top:0;">
                    <div><h2 class="section-title" style="font-size:28px;">Top-selling food items</h2></div>
                </div>
                <ul class="list-clean">
                    <?php foreach ($topFoods as $food): ?>
                        <li><span><?= e($food['name']) ?></span><strong><?= (int) $food['total_qty'] ?> sold | <?= format_currency((float) $food['total_sales']) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <section class="panel">
                <div class="section-head" style="margin-top:0;">
                    <div><h2 class="section-title" style="font-size:28px;">Recent orders</h2></div>
                </div>
                <div class="table-card">
                    <table>
                        <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= e($order['order_number']) ?><br><small class="muted"><?= e($order['payment_method']) ?></small></td>
                                <td><?= e($order['customer_name']) ?></td>
                                <td><span class="<?= order_badge_class($order['order_status']) ?>"><?= e($order['order_status']) ?></span></td>
                                <td><?= format_currency((float) $order['total_amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
