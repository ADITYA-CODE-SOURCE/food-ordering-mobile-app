<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('menu.php');
}

$pageTitle = 'Login | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="panel" style="max-width:520px;margin:0 auto;">
    <div class="section-head" style="margin-top:0;">
        <div>
            <h1 class="section-title" style="font-size:34px;">Welcome back</h1>
            <p class="section-subtitle" style="color:var(--muted);">Login to manage orders, favorites, and checkout faster.</p>
        </div>
    </div>
    <form action="actions/login_action.php" method="post" data-loading-form>
        <div class="form-grid">
            <div class="full">
                <label>Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </div>
            <div class="full">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="full">
                <button type="submit">Login</button>
            </div>
        </div>
    </form>
    <p class="meta-copy">Demo admin: <strong>admin@foodapp.test</strong> / <strong>admin123</strong></p>
    <p class="meta-copy">Demo customer: <strong>customer@foodapp.test</strong> / <strong>user12345</strong></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
