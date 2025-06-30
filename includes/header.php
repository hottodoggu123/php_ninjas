<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM Cinema</title>
    <link rel="stylesheet" href="/php%20ninjas/assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <span class="sm-text">SM</span><span class="cinema-text">CINEMA</span>
                <span class="sm-logo">SM</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="user/movies.php">Movies</a></li>
                <li><a href="#cinemas">Cinemas</a></li>
                <li><a href="#events">Events & Experiences</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="user/my_bookings.php">My Bookings</a></li>
                    <li><a href="user/profile.php">Profile</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
                <li><a href="#shop">Shop</a></li>
            </ul>
        </nav>
    </header>