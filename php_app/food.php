<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = sanitize_text($_GET['slug'] ?? '');
$food = fetch_one($pdo, 'SELECT f.*, c.name AS category_name FROM foods f INNER JOIN categories c ON c.id = f.category_id WHERE f.slug = ?', [$slug]);

if (!$food) {
    set_flash('error', 'Food item not found.');
    redirect('menu.php');
}

$variants = fetch_all($pdo, 'SELECT * FROM food_variants WHERE food_id = ? ORDER BY sort_order, price', [$food['id']]);
$addons = fetch_all($pdo, 'SELECT a.* FROM food_addons fa INNER JOIN addons a ON a.id = fa.addon_id WHERE fa.food_id = ? AND a.is_active = 1', [$food['id']]);
$reviews = fetch_all($pdo, 'SELECT r.*, u.name FROM reviews r INNER JOIN users u ON u.id = r.user_id WHERE food_id = ? ORDER BY r.created_at DESC', [$food['id']]);

if (is_logged_in()) {
    $stmt = $pdo->prepare('INSERT INTO recently_viewed (user_id, food_id, viewed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE viewed_at = NOW()');
    $stmt->execute([current_user()['id'], $food['id']]);
}

$pageTitle = $food['name'] . ' | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<div class="two-column">
    <section class="panel">
        <img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" style="width:100%;height:340px;object-fit:cover;border-radius:20px;" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';">
        <div class="section-head">
            <div>
                <span class="badge warning"><?= e($food['category_name']) ?></span>
                <h1 class="section-title" style="font-size:34px;margin-top:14px;"><?= e($food['name']) ?></h1>
                <p class="section-subtitle" style="color:var(--muted);"><?= e($food['description']) ?></p>
            </div>
            <div>
                <strong style="font-size:26px;"><?= format_currency((float) ($food['discount_price'] ?: $food['base_price'])) ?></strong>
                <p class="table-help"><i class="fa-solid fa-star"></i> <?= e((string) $food['rating']) ?> | <?= (int) $food['review_count'] ?> reviews</p>
            </div>
        </div>
        <div class="filters">
            <span class="chip"><i class="fa-solid fa-pepper-hot"></i> <?= e(ucfirst($food['spice_level'])) ?></span>
            <span class="chip"><i class="fa-regular fa-clock"></i> <?= e($food['preparation_time']) ?></span>
            <span class="chip"><i class="fa-solid fa-fire"></i> <?= e($food['nutrition_info']) ?></span>
        </div>
        <div class="grid" style="grid-template-columns:repeat(2,minmax(0,1fr));margin-bottom:22px;">
            <div class="panel" style="box-shadow:none;">
                <h3>Ingredients</h3>
                <p class="muted"><?= e($food['ingredients'] ?: 'Ingredients information will be updated soon.') ?></p>
            </div>
            <div class="panel" style="box-shadow:none;">
                <h3>Nutritional info</h3>
                <p class="muted"><?= e($food['nutrition_info'] ?: 'Nutritional information will be updated soon.') ?></p>
            </div>
        </div>

        <div class="section-head"><div><h2 class="section-title" style="font-size:26px;">Reviews & ratings</h2></div></div>
        <div class="grid">
            <?php foreach ($reviews as $review): ?>
                <article class="panel" style="box-shadow:none;">
                    <div class="tag-row"><strong><?= e($review['name']) ?></strong><span class="badge info"><i class="fa-solid fa-star"></i> <?= (int) $review['rating'] ?>/5</span></div>
                    <p class="muted"><?= e($review['review_text'] ?: 'No written review.') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <aside class="panel">
        <h2 style="margin-top:0;">Customize order</h2>
        <?php if (is_logged_in()): ?>
            <form action="actions/cart_action.php" method="post" data-loading-form>
                <?= csrf_input() ?>
                <input type="hidden" name="food_id" value="<?= (int) $food['id'] ?>">
                <label>Variant</label>
                <select name="variant_id">
                    <option value="">Default price</option>
                    <?php foreach ($variants as $variant): ?>
                        <option value="<?= (int) $variant['id'] ?>"><?= e($variant['variant_name']) ?> - <?= format_currency((float) $variant['price']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label style="display:block;margin-top:16px;">Quantity</label>
                <input type="number" min="1" max="10" name="quantity" value="1" required>
                <?php if ($addons): ?>
                    <div style="margin-top:16px;">
                        <label>Add-ons</label>
                        <?php foreach ($addons as $addon): ?>
                            <label style="display:flex;gap:10px;align-items:center;margin-top:10px;">
                                <input type="checkbox" name="addons[]" value="<?= (int) $addon['id'] ?>">
                                <span><?= e($addon['name']) ?> - <?= format_currency((float) $addon['price']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <button type="submit" style="margin-top:18px;width:100%;">Add to cart</button>
            </form>
            <form action="actions/favorite_action.php" method="post" style="margin-top:14px;">
                <?= csrf_input() ?>
                <input type="hidden" name="food_id" value="<?= (int) $food['id'] ?>">
                <button class="button ghost" type="submit" style="width:100%;">Save to wishlist</button>
            </form>
            <form action="actions/review_action.php" method="post" data-loading-form style="margin-top:22px;">
                <?= csrf_input() ?>
                <input type="hidden" name="food_id" value="<?= (int) $food['id'] ?>">
                <label>Rate this food</label>
                <select name="rating" required>
                    <option value="">Choose rating</option>
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
                <label style="display:block;margin-top:14px;">Feedback</label>
                <textarea name="review_text" placeholder="What did you like about this item?"></textarea>
                <button type="submit" style="margin-top:14px;width:100%;">Submit review</button>
            </form>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-user-lock"></i>
                <h3>Login required</h3>
                <p class="muted">Login to add this food to cart, wishlist, or post a review.</p>
                <a class="button" href="login.php">Login now</a>
            </div>
        <?php endif; ?>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
