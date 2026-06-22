<?php

declare(strict_types=1);

function auth_redirect_path(string $defaultPath, string $adminPath): string
{
    $currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
    return str_contains($currentPath, '/admin/') ? $adminPath : $defaultPath;
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Please login to continue.');
        redirect(auth_redirect_path('login.php', '../login.php'));
    }
}

function require_admin(): void
{
    require_login();

    $user = current_user();
    if (($user['role'] ?? '') !== 'admin') {
        set_flash('error', 'Admin access only.');
        redirect(auth_redirect_path('menu.php', '../menu.php'));
    }
}
