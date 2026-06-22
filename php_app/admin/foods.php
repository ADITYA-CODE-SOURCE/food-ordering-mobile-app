<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$foods = fetch_all($pdo, 'SELECT f.*, c.name AS category_name FROM foods f INNER JOIN categories c ON c.id = f.category_id ORDER BY f.created_at DESC');
$pageTitle = 'Manage Foods | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section>
        <div class="section-head" style="margin-top:0;">
            <div><h1 class="section-title" style="font-size:34px;">Food management</h1><p class="section-subtitle" style="color:var(--muted);">Add, edit, delete, upload images, and manage price, rating, category, and availability.</p></div>
            <a class="button" href="food_form.php">Add food</a>
        </div>
        <div class="table-card">
            <table>
                <thead><tr><th>Food</th><th>Category</th><th>Price</th><th>Rating</th><th>Availability</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($foods as $food): ?>
                        <tr>
                            <td><strong><?= e($food['name']) ?></strong><br><small class="muted"><?= e($food['slug']) ?></small></td>
                            <td><?= e($food['category_name']) ?></td>
                            <td><?= format_currency((float) ($food['discount_price'] ?: $food['base_price'])) ?></td>
                            <td><?= e((string) $food['rating']) ?></td>
                            <td><span class="<?= $food['availability_status'] === 'available' ? 'badge success' : 'badge warning' ?>"><?= e($food['availability_status']) ?></span></td>
                            <td class="card-actions"><a class="button secondary" href="food_form.php?id=<?= (int) $food['id'] ?>">Edit</a><a class="button ghost" href="food_delete.php?id=<?= (int) $food['id'] ?>" onclick="return confirm('Delete this food item?')">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
