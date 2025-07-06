<?php
include 'includes/init.php';
include 'includes/header.php';

// Check which section to show
$view = isset($_GET['view']) && $_GET['view'] === 'coming-soon' ? 'coming-soon' : 'now-showing';
$status = $view === 'coming-soon' ? 'coming_soon' : 'now_showing';

// Fetch movies from the database
$movies = $movieService->getMoviesByStatus($status);

// Check if user is logged in and get user dashboard data
$isLoggedIn = isLoggedIn();
$user = null;
$upcomingBookings = null;
$stats = null;

if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    
    // Get user information
    $user = $userService->getUserById($userId);
    
    // Get upcoming bookings
    $upcomingBookings = $userService->getUpcomingBookings($userId, 5);
    
    // Get statistics
    $stats = $userService->getUserStats($userId);
}
?>

<div class="container">
    <?php if ($isLoggedIn && $user): ?>
    <div class="dashboard-header">
        <h1>Welcome, <?php echo e($user['display_name'] ?? $user['username']); ?>!</h1>
        <p>Manage your bookings and account information</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_bookings'] ?? 0; ?></div>
            <div class="stat-label">Bookings Made</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo formatCurrency($stats['total_spent'] ?? 0); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <div class="dashboard-section">
        <div class="section-header">
            <h2>Upcoming Bookings</h2>
            <a href="user/profile.php" class="view-all-link">View All</a>
        </div>

        <div class="bookings-container">
            <?php if ($upcomingBookings && $upcomingBookings->num_rows > 0): ?>
                <?php while ($booking = $upcomingBookings->fetch_assoc()): ?>
                    <?php renderBookingCard($booking, true); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <p>You don't have any upcoming bookings.</p>
                    <a href="user/movies.php" class="button">Book Tickets Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php renderMovieStatusToggle($view); ?>
    <?php renderMovieList($movies); ?>
</div>

<?php include 'includes/footer.php'; ?>