<aside class="panel sidebar">
    <nav class="grid" style="gap:8px;">
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'foods.php' ? 'active' : '' ?>" href="foods.php"><i class="fa-solid fa-burger"></i> Foods</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'food_form.php' ? 'active' : '' ?>" href="food_form.php"><i class="fa-solid fa-plus"></i> Add Food</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>" href="categories.php"><i class="fa-solid fa-layer-group"></i> Categories</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>" href="orders.php"><i class="fa-solid fa-truck-fast"></i> Orders</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>" href="users.php"><i class="fa-solid fa-users"></i> Users</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'coupons.php' ? 'active' : '' ?>" href="coupons.php"><i class="fa-solid fa-ticket"></i> Coupons</a>
        <a class="<?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : '' ?>" href="reports.php"><i class="fa-solid fa-chart-pie"></i> Reports</a>
    </nav>
</aside>
