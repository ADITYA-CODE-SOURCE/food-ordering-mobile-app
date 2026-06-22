<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$id = (int) ($_POST['id'] ?? 0);
$name = sanitize_text($_POST['name'] ?? '');
$categoryId = (int) ($_POST['category_id'] ?? 0);
$shortDescription = sanitize_text($_POST['short_description'] ?? '');
$description = sanitize_text($_POST['description'] ?? '');
$ingredients = sanitize_text($_POST['ingredients'] ?? '');
$nutrition = sanitize_text($_POST['nutrition_info'] ?? '');
$prepTime = sanitize_text($_POST['preparation_time'] ?? '25 mins');
$basePrice = (float) ($_POST['base_price'] ?? 0);
$discountPrice = $_POST['discount_price'] !== '' ? (float) $_POST['discount_price'] : null;
$rating = (float) ($_POST['rating'] ?? 4.5);
$availability = sanitize_text($_POST['availability_status'] ?? 'available');
$spiceLevel = sanitize_text($_POST['spice_level'] ?? 'medium');

if ($name === '' || $categoryId <= 0 || $shortDescription === '' || $description === '' || $basePrice <= 0) {
    set_flash('error', 'Please complete all required food fields.');
    redirect('food_form.php' . ($id ? '?id=' . $id : ''));
}

$imagePath = null;
try {
    $imagePath = handle_upload('image', dirname(__DIR__) . '/uploads/foods');
} catch (Throwable $exception) {
    set_flash('error', $exception->getMessage());
    redirect('food_form.php' . ($id ? '?id=' . $id : ''));
}

$flags = [
    !empty($_POST['is_featured']) ? 1 : 0,
    !empty($_POST['is_recommended']) ? 1 : 0,
    !empty($_POST['is_popular']) ? 1 : 0,
    !empty($_POST['is_trending']) ? 1 : 0,
    !empty($_POST['is_new_arrival']) ? 1 : 0,
];

if ($id > 0) {
    $existing = fetch_one($pdo, 'SELECT image FROM foods WHERE id = ?', [$id]);
    $image = $imagePath ?: ($existing['image'] ?? null);
    $stmt = $pdo->prepare('UPDATE foods SET category_id = ?, name = ?, slug = ?, short_description = ?, description = ?, ingredients = ?, nutrition_info = ?, preparation_time = ?, image = ?, base_price = ?, discount_price = ?, rating = ?, availability_status = ?, spice_level = ?, is_featured = ?, is_recommended = ?, is_popular = ?, is_trending = ?, is_new_arrival = ? WHERE id = ?');
    $stmt->execute([$categoryId, $name, slugify($name), $shortDescription, $description, $ingredients, $nutrition, $prepTime, $image, $basePrice, $discountPrice, $rating, $availability, $spiceLevel, ...$flags, $id]);
    set_flash('success', 'Food updated successfully.');
} else {
    if ($imagePath === null) {
        $imagePath = 'assets/img/food-placeholder.svg';
    }
    $stmt = $pdo->prepare('INSERT INTO foods (category_id, name, slug, short_description, description, ingredients, nutrition_info, preparation_time, image, base_price, discount_price, rating, availability_status, spice_level, is_featured, is_recommended, is_popular, is_trending, is_new_arrival) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$categoryId, $name, slugify($name), $shortDescription, $description, $ingredients, $nutrition, $prepTime, $imagePath, $basePrice, $discountPrice, $rating, $availability, $spiceLevel, ...$flags]);
    set_flash('success', 'Food created successfully.');
}

redirect('foods.php');
