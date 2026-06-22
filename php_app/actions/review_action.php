<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

$foodId = (int) ($_POST['food_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$reviewText = sanitize_text($_POST['review_text'] ?? '');

if ($foodId <= 0 || $rating < 1 || $rating > 5) {
    set_flash('error', 'Please select a valid rating.');
    redirect($_SERVER['HTTP_REFERER'] ?? '../menu.php');
}

$pdo->prepare('INSERT INTO reviews (user_id, food_id, rating, review_text) VALUES (?, ?, ?, ?)')->execute([current_user()['id'], $foodId, $rating, $reviewText]);
$pdo->prepare('UPDATE foods SET rating = (SELECT AVG(rating) FROM reviews WHERE food_id = ?), review_count = (SELECT COUNT(*) FROM reviews WHERE food_id = ?) WHERE id = ?')->execute([$foodId, $foodId, $foodId]);
set_flash('success', 'Review submitted successfully.');
redirect($_SERVER['HTTP_REFERER'] ?? '../menu.php');
