<?php
require_once __DIR__ . '/includes/bootstrap.php';

$search = sanitize_text($_GET['search'] ?? '');
$categorySlug = sanitize_text($_GET['category'] ?? '');
$sort = sanitize_text($_GET['sort'] ?? 'popular');
$queryBase = [];
if ($search !== '') {
    $queryBase['search'] = $search;
}
if ($sort !== 'popular') {
    $queryBase['sort'] = $sort;
}

$categories = fetch_all($pdo, 'SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
$banners = fetch_all($pdo, 'SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order LIMIT 3');

$orderBy = match ($sort) {
    'price_low' => 'COALESCE(discount_price, base_price) ASC',
    'price_high' => 'COALESCE(discount_price, base_price) DESC',
    'rating' => 'rating DESC',
    'new' => 'created_at DESC',
    default => 'is_popular DESC, sold_count DESC, rating DESC',
};

$where = ['1=1'];
$params = [];
if ($search !== '') {
    $where[] = '(f.name LIKE ? OR f.short_description LIKE ? OR f.description LIKE ? OR COALESCE(f.ingredients, "") LIKE ? OR c.name LIKE ?)';
    $searchTerm = '%' . $search . '%';
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}
if ($categorySlug !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

$foods = fetch_all(
    $pdo,
    'SELECT f.*, c.name AS category_name, c.slug AS category_slug
     FROM foods f
     INNER JOIN categories c ON c.id = f.category_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY ' . $orderBy,
    $params
);

$recommended = fetch_all($pdo, 'SELECT id, name, slug, image, short_description, COALESCE(discount_price, base_price) AS selling_price, rating FROM foods WHERE is_recommended = 1 ORDER BY sold_count DESC LIMIT 4');
$recentlyViewed = [];
if (is_logged_in()) {
    $recentlyViewed = fetch_all($pdo, 'SELECT f.id, f.name, f.slug, f.image, f.short_description, COALESCE(f.discount_price, f.base_price) AS selling_price, f.rating
        FROM recently_viewed rv
        INNER JOIN foods f ON f.id = rv.food_id
        WHERE rv.user_id = ?
        ORDER BY rv.viewed_at DESC LIMIT 4', [current_user()['id']]);
}

$pageTitle = 'Menu | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <article class="hero-banner">
        <img src="<?= e($banners[0]['image'] ?? 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1400&q=80') ?>" alt="banner">
            <div class="hero-copy">
                <div class="pill"><i class="fa-solid fa-bolt"></i> Fast online ordering</div>
                <h1><?= e($banners[0]['title'] ?? 'Crave-worthy food delivered fresh') ?></h1>
                <p><?= e($banners[0]['subtitle'] ?? 'Professional menu, wishlist, coupons, reviews, admin analytics, and delivery tracking in one PHP + MySQL platform.') ?></p>
            <div class="card-actions">
                <a class="button" href="checkout.php">Quick Checkout</a>
                <a class="button ghost" href="#food-grid">Explore Menu</a>
            </div>
        </div>
    </article>
    <div class="hero-metrics">
        <div class="panel metric-box">
            <p class="muted">Trending now</p>
            <strong>30 dynamic foods</strong>
            <span class="meta-copy">Database-powered cards with sorting, filters, and wishlist.</span>
        </div>
        <div class="panel metric-box">
            <p class="muted">Featured experiences</p>
            <strong>Flash deals + recommendations</strong>
            <span class="meta-copy">Modern catalog sections similar to food delivery apps.</span>
        </div>
    </div>
</section>

<section class="panel">
    <form class="search-form" method="get">
        <input type="text" name="search" placeholder="Search for pizza, biryani, brownie..." value="<?= e($search) ?>">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['slug']) ?>" <?= $categorySlug === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort">
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Sort: Popular</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to high</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to low</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Top rated</option>
            <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>New arrivals</option>
        </select>
        <button type="submit">Apply</button>
        <?php if ($search !== '' || $categorySlug !== '' || $sort !== 'popular'): ?>
            <a class="button ghost" href="menu.php">Clear</a>
        <?php endif; ?>
    </form>
    <div class="filters">
        <?php foreach ($categories as $category): ?>
            <?php $categoryQuery = $queryBase; $categoryQuery['category'] = $category['slug']; ?>
            <a class="chip <?= $categorySlug === $category['slug'] ? 'active' : '' ?>" href="menu.php?<?= e(http_build_query($categoryQuery)) ?>">
                <i class="fa-solid <?= e($category['icon']) ?>"></i>
                <?= e($category['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($recommended): ?>
    <section>
        <div class="section-head">
            <div>
                <h2 class="section-title" style="font-size:28px;">Recommended for you</h2>
                <p class="section-subtitle" style="color:var(--muted);">High-rated foods picked from best sellers and featured items.</p>
            </div>
        </div>
        <div class="food-grid">
            <?php foreach ($recommended as $food): ?>
                <article class="food-card">
                    <div class="food-card-media">
                        <img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';">
                    </div>
                    <div class="card-body">
                        <div class="tag-row"><span class="badge info"><i class="fa-solid fa-star"></i> <?= e((string) $food['rating']) ?></span></div>
                        <h3><?= e($food['name']) ?></h3>
                        <p class="muted"><?= e($food['short_description']) ?></p>
                        <div class="price-row"><strong><?= format_currency((float) $food['selling_price']) ?></strong><a class="button secondary" href="food.php?slug=<?= e($food['slug']) ?>">View</a></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section id="food-grid">
    <div class="section-head">
        <div>
            <h2 class="section-title" style="font-size:28px;">Dynamic menu page</h2>
            <p class="section-subtitle" style="color:var(--muted);">All foods are loaded from MySQL with category filter, search, wishlist, and sort options.</p>
        </div>
        <p class="table-help"><?= count($foods) ?> food items found</p>
    </div>
    <div class="food-grid">
        <?php foreach ($foods as $food): ?>
            <article class="food-card">
                <div class="food-card-media">
                    <img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';">
                    <div class="tag-row" style="position:absolute;top:14px;left:14px;">
                        <?php if ($food['is_trending']): ?><span class="pill"><i class="fa-solid fa-fire"></i> Trending</span><?php endif; ?>
                        <?php if ($food['is_new_arrival']): ?><span class="pill"><i class="fa-solid fa-sparkles"></i> New</span><?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="tag-row">
                        <span class="badge warning"><?= e($food['category_name']) ?></span>
                        <span class="badge info"><i class="fa-solid fa-star"></i> <?= e((string) $food['rating']) ?></span>
                    </div>
                    <h3><?= e($food['name']) ?></h3>
                    <p class="muted"><?= e($food['short_description']) ?></p>
                    <div class="price-row">
                        <div>
                            <strong><?= format_currency((float) ($food['discount_price'] ?: $food['base_price'])) ?></strong>
                            <?php if (!empty($food['discount_price'])): ?>
                                <small class="muted" style="text-decoration:line-through;"><?= format_currency((float) $food['base_price']) ?></small>
                            <?php endif; ?>
                        </div>
                        <span class="badge <?= $food['availability_status'] === 'available' ? 'success' : 'warning' ?>"><?= e(ucwords(str_replace('_', ' ', $food['availability_status']))) ?></span>
                    </div>
                    <div class="card-actions">
                        <a class="button secondary" href="food.php?slug=<?= e($food['slug']) ?>">Details</a>
                        <?php if (is_logged_in()): ?>
                            <form action="actions/favorite_action.php" method="post" style="display:inline;">
                                <?= csrf_input() ?>
                                <input type="hidden" name="food_id" value="<?= (int) $food['id'] ?>">
                                <button type="submit"><i class="fa-regular fa-heart"></i> Wishlist</button>
                            </form>
                        <?php else: ?>
                            <a class="button ghost" href="login.php">Login to save</a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php if ($recentlyViewed): ?>
    <section>
        <div class="section-head">
            <div>
                <h2 class="section-title" style="font-size:28px;">Recently viewed</h2>
                <p class="section-subtitle" style="color:var(--muted);">Personalized for the logged-in customer.</p>
            </div>
        </div>
        <div class="food-grid">
            <?php foreach ($recentlyViewed as $food): ?>
                <article class="food-card">
                    <div class="food-card-media"><img src="<?= e(food_image($food['image'])) ?>" alt="<?= e($food['name']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= e(asset_path('assets/img/food-placeholder.svg')) ?>';"></div>
                    <div class="card-body">
                        <h3><?= e($food['name']) ?></h3>
                        <p class="muted"><?= e($food['short_description']) ?></p>
                        <div class="price-row"><strong><?= format_currency((float) $food['selling_price']) ?></strong><a class="button secondary" href="food.php?slug=<?= e($food['slug']) ?>">Open</a></div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
