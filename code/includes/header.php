<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Construction Material Management System';
$activeNav = $activeNav ?? '';
$flash     = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= is_logged_in() ? 'dashboard.php' : 'login.php' ?>">
            <span class="brand-mark">CM</span>
            <span class="brand-text">
                <strong>Construction Material</strong>
                <small>Management System</small>
            </span>
        </a>

        <?php if (is_logged_in()): ?>
            <nav class="nav">
                <a href="dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="add_material.php" class="<?= $activeNav === 'add' ? 'active' : '' ?>">Add Material</a>
                <a href="search_material.php" class="<?= $activeNav === 'search' ? 'active' : '' ?>">Search</a>
                <a href="materials.php" class="<?= $activeNav === 'list' ? 'active' : '' ?>">Material List</a>
                <a href="supplier.php" class="<?= $activeNav === 'supplier' ? 'active' : '' ?>">Supplier</a>
            </nav>
            <div class="user-box">
                <span class="user-name"><?= e(current_user_name()) ?></span>
                <a class="btn btn-outline btn-sm" href="logout.php">Logout</a>
            </div>
        <?php else: ?>
            <nav class="nav">
                <a href="login.php" class="<?= $activeNav === 'login' ? 'active' : '' ?>">Login</a>
                <a href="register.php" class="<?= $activeNav === 'register' ? 'active' : '' ?>">Register</a>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main class="container page">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
