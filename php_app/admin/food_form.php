<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$foodId = (int) ($_GET['id'] ?? 0);
$food = $foodId ? fetch_one($pdo, 'SELECT * FROM foods WHERE id = ?', [$foodId]) : null;
$categories = fetch_all($pdo, 'SELECT * FROM categories ORDER BY name');
$pageTitle = ($food ? 'Edit Food' : 'Add Food') . ' | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section class="panel">
        <div class="section-head" style="margin-top:0;"><div><h1 class="section-title" style="font-size:34px;"><?= $food ? 'Edit food' : 'Add food' ?></h1></div></div>
        <form action="food_save.php" method="post" enctype="multipart/form-data" data-loading-form>
            <input type="hidden" name="id" value="<?= (int) ($food['id'] ?? 0) ?>">
            <div class="form-grid">
                <div><label>Name</label><input type="text" name="name" value="<?= e($food['name'] ?? '') ?>" required></div>
                <div><label>Category</label><select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) ($food['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div>
                <div><label>Base price</label><input type="number" step="0.01" name="base_price" value="<?= e((string) ($food['base_price'] ?? '')) ?>" required></div>
                <div><label>Discount price</label><input type="number" step="0.01" name="discount_price" value="<?= e((string) ($food['discount_price'] ?? '')) ?>"></div>
                <div><label>Rating</label><input type="number" step="0.1" min="1" max="5" name="rating" value="<?= e((string) ($food['rating'] ?? 4.5)) ?>" required></div>
                <div><label>Availability</label><select name="availability_status"><option value="available">available</option><option value="limited" <?= ($food['availability_status'] ?? '') === 'limited' ? 'selected' : '' ?>>limited</option><option value="sold_out" <?= ($food['availability_status'] ?? '') === 'sold_out' ? 'selected' : '' ?>>sold_out</option></select></div>
                <div><label>Preparation time</label><input type="text" name="preparation_time" value="<?= e($food['preparation_time'] ?? '25 mins') ?>"></div>
                <div><label>Spice level</label><select name="spice_level"><option value="mild">mild</option><option value="medium" <?= ($food['spice_level'] ?? '') === 'medium' ? 'selected' : '' ?>>medium</option><option value="hot" <?= ($food['spice_level'] ?? '') === 'hot' ? 'selected' : '' ?>>hot</option></select></div>
                <div class="full"><label>Short description</label><input type="text" name="short_description" value="<?= e($food['short_description'] ?? '') ?>" required></div>
                <div class="full"><label>Description</label><textarea name="description" required><?= e($food['description'] ?? '') ?></textarea></div>
                <div class="full"><label>Ingredients</label><textarea name="ingredients"><?= e($food['ingredients'] ?? '') ?></textarea></div>
                <div class="full"><label>Nutritional info</label><textarea name="nutrition_info"><?= e($food['nutrition_info'] ?? '') ?></textarea></div>
                <div>
                    <label>Image upload</label>
                    <input type="file" name="image" <?= $food ? '' : 'required' ?>>
                </div>
                <?php if (!empty($food['image'])): ?>
                    <div class="full">
                        <label>Current image</label>
                        <img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" style="width:220px;height:150px;object-fit:cover;border-radius:16px;border:1px solid var(--border);" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';">
                    </div>
                <?php endif; ?>
                <div class="full"><label><input type="checkbox" name="is_featured" value="1" <?= !empty($food['is_featured']) ? 'checked' : '' ?>> Featured</label> <label><input type="checkbox" name="is_recommended" value="1" <?= !empty($food['is_recommended']) ? 'checked' : '' ?>> Recommended</label> <label><input type="checkbox" name="is_popular" value="1" <?= !empty($food['is_popular']) ? 'checked' : '' ?>> Popular</label> <label><input type="checkbox" name="is_trending" value="1" <?= !empty($food['is_trending']) ? 'checked' : '' ?>> Trending</label> <label><input type="checkbox" name="is_new_arrival" value="1" <?= !empty($food['is_new_arrival']) ? 'checked' : '' ?>> New arrival</label></div>
                <div class="full"><button type="submit">Save food</button></div>
            </div>
        </form>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
