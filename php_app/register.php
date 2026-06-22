<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('menu.php');
}

$pageTitle = 'Register | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<section class="panel" style="max-width:620px;margin:0 auto;">
    <div class="section-head" style="margin-top:0;">
        <div>
            <h1 class="section-title" style="font-size:34px;">Create account</h1>
            <p class="section-subtitle" style="color:var(--muted);">Join the platform to save addresses, wishlist items, and order history.</p>
        </div>
    </div>
    <form action="actions/register_action.php" method="post" data-loading-form>
        <div class="form-grid">
            <div>
                <label>Full Name</label>
                <input type="text" name="name" value="<?= old('name') ?>" required>
            </div>
            <div>
                <label>Phone</label>
                <input type="text" name="phone" value="<?= old('phone') ?>" required>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" required minlength="8">
            </div>
            <div class="full">
                <button type="submit">Create account</button>
            </div>
        </div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
