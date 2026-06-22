<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare('DELETE FROM foods WHERE id = ?')->execute([$id]);
    set_flash('success', 'Food deleted successfully.');
}
redirect('foods.php');
