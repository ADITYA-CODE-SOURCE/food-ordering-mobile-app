<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$users = fetch_all($pdo, 'SELECT id, role, name, email, phone, status, created_at FROM users ORDER BY created_at DESC');
$pageTitle = 'Users | Foodly Pro';
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>
    <section>
        <div class="section-head" style="margin-top:0;"><div><h1 class="section-title" style="font-size:34px;">User management</h1><p class="section-subtitle" style="color:var(--muted);">Role-based authentication and customer overview.</p></div></div>
        <div class="table-card"><table><thead><tr><th>Name</th><th>Role</th><th>Contact</th><th>Status</th><th>Joined</th></tr></thead><tbody><?php foreach ($users as $user): ?><tr><td><?= e($user['name']) ?></td><td><?= e($user['role']) ?></td><td><?= e($user['email']) ?><br><small class="muted"><?= e($user['phone']) ?></small></td><td><span class="badge <?= $user['status'] === 'active' ? 'success' : 'danger' ?>"><?= e($user['status']) ?></span></td><td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div>
    </section>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
