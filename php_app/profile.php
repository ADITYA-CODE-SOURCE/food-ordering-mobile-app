<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$user = fetch_one($pdo, 'SELECT * FROM users WHERE id = ?', [current_user()['id']]);
$addresses = fetch_all($pdo, 'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$user['id']]);
$pageTitle = 'Profile | Foodly Pro';
require __DIR__ . '/includes/header.php';
?>
<div class="two-column">
    <section class="panel">
        <div class="section-head" style="margin-top:0;">
            <div>
                <h1 class="section-title" style="font-size:34px;">Your profile</h1>
                <p class="section-subtitle" style="color:var(--muted);">Edit contact details and manage support-friendly saved addresses.</p>
            </div>
        </div>
        <form action="actions/profile_action.php" method="post" data-loading-form>
            <div class="form-grid">
                <div><label>Name</label><input type="text" name="name" value="<?= e($user['name']) ?>" required></div>
                <div><label>Phone</label><input type="text" name="phone" value="<?= e($user['phone']) ?>" required></div>
                <div class="full"><label>Email</label><input type="email" value="<?= e($user['email']) ?>" disabled></div>
                <div class="full"><button type="submit">Save profile</button></div>
            </div>
        </form>
    </section>
    <aside class="panel">
        <h2 style="margin-top:0;">Saved addresses</h2>
        <ul class="list-clean">
            <?php foreach ($addresses as $address): ?>
                <li><span><strong><?= e($address['label']) ?></strong><br><?= e($address['address_line']) ?>, <?= e($address['city']) ?></span><span><?= $address['is_default'] ? 'Default' : '' ?></span></li>
            <?php endforeach; ?>
        </ul>
        <form action="actions/profile_action.php" method="post" data-loading-form style="margin-top:18px;">
            <input type="hidden" name="action" value="address">
            <div class="form-grid">
                <div><label>Label</label><input type="text" name="label" placeholder="Home" required></div>
                <div><label>Contact Name</label><input type="text" name="contact_name" value="<?= e($user['name']) ?>" required></div>
                <div><label>Phone</label><input type="text" name="address_phone" value="<?= e($user['phone']) ?>" required></div>
                <div><label>City</label><input type="text" name="city" required></div>
                <div class="full"><label>Address line</label><textarea name="address_line" required></textarea></div>
                <div><label>State</label><input type="text" name="state"></div>
                <div><label>Postal code</label><input type="text" name="postal_code"></div>
                <div class="full"><button type="submit">Add address</button></div>
            </div>
        </form>
    </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
