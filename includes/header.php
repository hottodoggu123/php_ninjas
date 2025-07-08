<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Booking</title>
    <link rel="stylesheet" href="<?php echo getAssetPath('assets/css/style.css'); ?>">
</head>
<body>

<header>
    <div class="nav-container">
        <div class="site-name">Cinema Booking</div>
        
        <nav>
            <ul class="nav-menu">
                <!-- Search form -->
                <li class="search-item">
                    <form action="<?php echo getLinkPath('user/advancedSearch.php'); ?>" method="GET">
                        <input type="text" name="search" placeholder="Search...">
                        <button type="submit" class="search-button">Search</button>
                    </form>
                </li>
                <li><a href="<?php 
                    if (isAdmin()) {
                        echo getLinkPath('admin/dashboard.php');
                    } else {
                        echo getLinkPath('index.php');
                    }
                ?>" class="button">Home</a></li>

                <?php if (isLoggedIn()): ?>
                    <li><a class="button">Hi, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></a></li>
                    
                    <?php if (isAdmin()): ?>
                        <!-- Admin Navigation -->
                        <li><a href="<?php echo getLinkPath('admin/dashboard.php'); ?>" class="button">Admin Dashboard</a></li>
                        <li><a href="<?php echo getLinkPath('admin/manageMovies.php'); ?>" class="button">Manage Movies</a></li>
                    <?php else: ?>
                        <!-- Regular User Navigation -->
                        <li><a href="<?php echo getLinkPath('user/profile.php'); ?>" class="button">Profile</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo getLinkPath('logout.php'); ?>" class="button">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo getLinkPath('login.php'); ?>" class="button">Login</a></li>
                    <li><a href="<?php echo getLinkPath('register.php'); ?>" class="button">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>