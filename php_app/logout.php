<?php
require_once __DIR__ . '/includes/bootstrap.php';
session_destroy();
session_start();
set_flash('success', 'Logged out successfully.');
redirect('login.php');
