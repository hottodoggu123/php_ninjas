<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Booking</title>

    <?php
    // Determine correct path for CSS regardless of subfolder depth
    $cssPath = (strpos($_SERVER['PHP_SELF'], '/user/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false)
        ? '../assets/css/style.css'
        : 'assets/css/style.css';
    ?>
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>

<header>
    <div class="nav-container">
        <div class="site-name">Cinema Booking</div>
        <nav>
            <ul class="nav-menu">
                <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') || strpos($_SERVER['PHP_SELF'], '/admin/')) ? '../index.php' : 'index.php'; ?>" class="button">Home</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a class="button">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/')) ? 'profile.php' : 'user/profile.php'; ?>" class="button">Profile</a></li>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') || strpos($_SERVER['PHP_SELF'], '/admin/')) ? '../logout.php' : 'logout.php'; ?>" class="button">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') || strpos($_SERVER['PHP_SELF'], '/admin/')) ? '../login.php' : 'login.php'; ?>" class="button">Login</a></li>
                    <li><a href="<?php echo (strpos($_SERVER['PHP_SELF'], '/user/') || strpos($_SERVER['PHP_SELF'], '/admin/')) ? '../register.php' : 'register.php'; ?>" class="button">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>