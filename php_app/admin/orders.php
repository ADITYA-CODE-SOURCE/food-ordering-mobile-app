<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = sanitize_text($_POST['order_status'] ?? 'Pending');
    $pdo->prepare('UPDATE orders SET order_status = ? WHERE id = ?')->execute([$status, $orderId]);
    $pdo->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)')->execute([$orderId, $status, order_status_note($status)]);
    set_flash('success', $status === 'Cancelled' ? 'Order cancelled successfully.' : 'Order status updated successfully.');
    redirect('orders.php');
}

$orders = fetch_all($pdo, 'SELECT o.*, u.email FROM orders o INNER JOIN users u ON u.id = o.user_id ORDER BY o.placed_at DESC');
$pageTitle = 'Manage Orders | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section>
        <div class="section-head" style="margin-top:0;"><div><h1 class="section-title" style="font-size:34px;">Order management</h1><p class="section-subtitle" style="color:var(--muted);">Update delivery status from Pending to Delivered or Cancelled.</p></div></div>
        <div class="table-card">
            <table>
                <thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Total</th><th>Status</th><th>Update</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?= e($order['order_number']) ?></strong><br><small class="muted"><?= e(date('d M Y, h:i A', strtotime($order['placed_at']))) ?></small></td>
                        <td><?= e($order['customer_name']) ?><br><small class="muted"><?= e($order['email']) ?></small></td>
                        <td><?= e($order['payment_method']) ?><br><small class="muted"><?= e($order['payment_status']) ?></small></td>
                        <td><?= format_currency((float) $order['total_amount']) ?></td>
                        <td><span class="<?= order_badge_class($order['order_status']) ?>"><?= e($order['order_status']) ?></span></td>
                        <td>
                            <form method="post" class="card-actions">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select name="order_status">
                                    <?php foreach (['Pending','Accepted','Preparing','Ready','Out For Delivery','Delivered','Cancelled'] as $status): ?>
                                        <option value="<?= e($status) ?>" <?= $order['order_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
