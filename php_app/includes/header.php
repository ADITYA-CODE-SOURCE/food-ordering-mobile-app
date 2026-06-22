<?php
$flash = get_flash();
$user = current_user();
$menuCartCount = isset($pdo) ? cart_count($pdo) : 0;
$pageTitle = $pageTitle ?? 'Foodly Pro';
$basePath = $basePath ?? (str_contains(str_replace('\\', '/', $_SERVER['PHP_SELF']), '/admin/') ? '../' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= e($basePath) ?>assets/css/style.css">
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="<?= e($basePath) ?>menu.php">
                <span class="brand-badge"><i class="fa-solid fa-bowl-food"></i></span>
                <span>
                    <strong>Foodly Pro</strong>
                    <small>Fresh food delivery platform</small>
                </span>
            </a>
            <nav class="topnav">
                <a href="<?= e($basePath) ?>menu.php">Menu</a>
                <a href="<?= e($basePath) ?>orders.php">Orders</a>
                <a href="<?= e($basePath) ?>favorites.php">Wishlist</a>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <a href="<?= e($basePath) ?>admin/index.php">Admin</a>
                <?php endif; ?>
            </nav>
            <div class="topnav-actions">
                <a class="icon-button" href="<?= e($basePath) ?>cart.php" aria-label="Cart">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span class="count-pill"><?= $menuCartCount ?></span>
                </a>
                <?php if ($user): ?>
                    <div class="profile-pill">
                        <span><?= e($user['name']) ?></span>
                        <a href="<?= e($basePath) ?>profile.php">Profile</a>
                        <form action="<?= e($basePath) ?>logout.php" method="post" style="display:inline;">
                            <?= csrf_input() ?>
                            <button type="submit" style="background:none;border:none;padding:0;color:inherit;font:inherit;cursor:pointer;">Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a class="button ghost" href="<?= e($basePath) ?>login.php">Login</a>
                    <a class="button" href="<?= e($basePath) ?>register.php">Register</a>
                <?php endif; ?>
            </div>
        </header>
        <?php if ($flash): ?>
            <div class="toast toast-<?= e($flash['type']) ?>" data-toast>
                <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>
        <main class="page-container">
