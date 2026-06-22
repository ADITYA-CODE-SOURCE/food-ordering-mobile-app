<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$daily = fetch_all($pdo, 'SELECT DATE(placed_at) AS report_date, SUM(total_amount) AS revenue, COUNT(*) AS orders FROM orders WHERE order_status != "Cancelled" GROUP BY DATE(placed_at) ORDER BY report_date DESC LIMIT 7');
$monthly = fetch_all($pdo, 'SELECT DATE_FORMAT(placed_at, "%Y-%m") AS report_month, SUM(total_amount) AS revenue, COUNT(*) AS orders FROM orders WHERE order_status != "Cancelled" GROUP BY DATE_FORMAT(placed_at, "%Y-%m") ORDER BY report_month DESC LIMIT 6');
$pageTitle = 'Reports | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section class="grid">
        <div class="panel"><h1 class="section-title" style="font-size:34px;">Sales reports</h1><p class="section-subtitle" style="color:var(--muted);">Daily and monthly summaries for revenue, order volume, and business tracking.</p></div>
        <div class="two-column">
            <div class="table-card"><table><thead><tr><th>Day</th><th>Orders</th><th>Revenue</th></tr></thead><tbody><?php foreach ($daily as $row): ?><tr><td><?= e($row['report_date']) ?></td><td><?= (int) $row['orders'] ?></td><td><?= format_currency((float) $row['revenue']) ?></td></tr><?php endforeach; ?></tbody></table></div>
            <div class="table-card"><table><thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead><tbody><?php foreach ($monthly as $row): ?><tr><td><?= e($row['report_month']) ?></td><td><?= (int) $row['orders'] ?></td><td><?= format_currency((float) $row['revenue']) ?></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
