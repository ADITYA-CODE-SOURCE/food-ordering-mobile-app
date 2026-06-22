<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$favorites = fetch_all($pdo, 'SELECT f.* FROM favorites fav INNER JOIN foods f ON f.id = fav.food_id WHERE fav.user_id = ? ORDER BY fav.created_at DESC', [current_user()['id']]);
$pageTitle = 'Wishlist | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="section-head"><div><h1 class="section-title" style="font-size:34px;">Wishlist</h1><p class="section-subtitle" style="color:var(--muted);">Saved foods for faster reordering and interview-ready personalization.</p></div></section>
<?php if (!$favorites): ?>
    <section class="empty-state"><i class="fa-regular fa-heart"></i><h2>No wishlist items yet</h2><p class="muted">Save your favorite foods from the menu page.</p><a class="button" href="menu.php">Explore menu</a></section>
<?php else: ?>
    <div class="food-grid">
        <?php foreach ($favorites as $food): ?>
            <article class="food-card">
                <div class="food-card-media"><img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';"></div>
                <div class="card-body">
                    <h3><?= e($food['name']) ?></h3>
                    <p class="muted"><?= e($food['short_description']) ?></p>
                    <div class="price-row"><strong><?= format_currency((float) ($food['discount_price'] ?: $food['base_price'])) ?></strong><a class="button secondary" href="food.php?slug=<?= e($food['slug']) ?>">View</a></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
