<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_login();

require_post_request('../menu.php');
require_csrf('../menu.php');

$foodId = (int) ($_POST['food_id'] ?? 0);
if ($foodId <= 0) {
    set_flash('error', 'Invalid food selection.');
    redirect('../menu.php');
}

$stmt = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, food_id) VALUES (?, ?)');
$stmt->execute([current_user()['id'], $foodId]);
set_flash('success', 'Food saved to wishlist.');
redirect($_SERVER['HTTP_REFERER'] ?? '../favorites.php');
