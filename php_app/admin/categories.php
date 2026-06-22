<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_text($_POST['name'] ?? '');
    $icon = sanitize_text($_POST['icon'] ?? 'fa-utensils');
    $description = sanitize_text($_POST['description'] ?? '');
    if ($name !== '') {
        $pdo->prepare('INSERT INTO categories (name, slug, icon, description, is_active) VALUES (?, ?, ?, ?, 1)')->execute([$name, slugify($name), $icon, $description]);
        set_flash('success', 'Category added.');
    }
    redirect('categories.php');
}

$categories = fetch_all($pdo, 'SELECT * FROM categories ORDER BY name');
$pageTitle = 'Categories | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section class="grid">
        <div class="panel">
            <h1 class="section-title" style="font-size:34px;">Category CRUD</h1>
            <form method="post" data-loading-form>
                <div class="form-grid">
                    <div><label>Name</label><input type="text" name="name" required></div>
                    <div><label>Icon class</label><input type="text" name="icon" placeholder="fa-pizza-slice"></div>
                    <div class="full"><label>Description</label><input type="text" name="description"></div>
                    <div class="full"><button type="submit">Add category</button></div>
                </div>
            </form>
        </div>
        <div class="table-card"><table><thead><tr><th>Category</th><th>Slug</th><th>Icon</th><th>Description</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><?= e($category['name']) ?></td><td><?= e($category['slug']) ?></td><td><i class="fa-solid <?= e($category['icon']) ?>"></i> <?= e($category['icon']) ?></td><td><?= e($category['description'] ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
