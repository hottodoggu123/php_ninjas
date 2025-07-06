<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Get dashboard statistics using optimized service
$stats = $adminService->getDashboardStats();
if (!$stats) {
    $stats = ['movie_count' => 0, 'user_count' => 0, 'booking_count' => 0, 
              'now_showing_count' => 0, 'coming_soon_count' => 0, 'total_revenue' => 0];
}

// Get recent bookings using service
$recentBookings = $adminService->getRecentBookings(5);

// Get recently added movies using service
$recentMovies = $adminService->getRecentMovies(5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cinema Booking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('dashboard.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Dashboard</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="icon-card-grid">
                <div class="icon-card">
                    <div class="icon-card-icon icon-movies">
                        <i class="fas fa-film"></i>
                    </div>
                    <div class="icon-card-content">
                        <h3><?php echo $stats['movie_count']; ?></h3>
                        <p>Movies</p>
                    </div>
                </div>
                
                <div class="icon-card">
                    <div class="icon-card-icon icon-users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="icon-card-content">
                        <h3><?php echo $stats['user_count']; ?></h3>
                        <p>Users</p>
                    </div>
                </div>
                
                <div class="icon-card">
                    <div class="icon-card-icon icon-bookings">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="icon-card-content">
                        <h3><?php echo $stats['booking_count']; ?></h3>
                        <p>Bookings</p>
                    </div>
                </div>
                
                <div class="icon-card">
                    <div class="icon-card-icon icon-revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="icon-card-content">
                        <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                        <p>Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Movie Status Section -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Movie Status</h2>
                </div>
                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-icon status-showing">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <h3><?php echo $stats['now_showing_count']; ?></h3>
                        <p>Now Showing</p>
                    </div>
                    
                    <div class="status-card">
                        <div class="status-icon status-coming">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3><?php echo $stats['coming_soon_count']; ?></h3>
                        <p>Coming Soon</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="addMovie.php" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span>Add Movie</span>
                    </a>
                    
                    <a href="manageMovies.php" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <span>Manage Movies</span>
                    </a>
                    
                    <a href="#" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <span>Add Showtimes</span>
                    </a>
                    
                    <a href="#" class="quick-action-btn">
                        <div class="quick-action-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>
            
            <!-- Two Column Layout for Recent Data -->
            <div class="admin-grid">
                <!-- Recent Bookings -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h2>Recent Bookings</h2>
                    </div>
                    <?php if ($recentBookings && $recentBookings->num_rows > 0): ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Movie</th>
                                        <th>Date</th>
                                        <th>Seats</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $recentBookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo e($booking['username']); ?></td>
                                            <td><?php echo e($booking['title']); ?></td>
                                            <td><?php echo formatDate($booking['show_date']); ?></td>
                                            <td><?php echo $booking['seats']; ?></td>
                                            <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="info-message">No recent bookings found.</div>
                    <?php endif; ?>
                </div>

                <!-- Recent Movies -->
                <div class="admin-section">
                    <div class="admin-section-header">
                        <h2>Recently Added Movies</h2>
                    </div>
                    <?php if ($recentMovies && $recentMovies->num_rows > 0): ?>
                        <div class="recent-movies-grid">
                            <?php while($movie = $recentMovies->fetch_assoc()): ?>
                                <div class="recent-movie">
                                    <div class="recent-movie-poster">
                                        <img src="../<?php echo e($movie['poster_url']); ?>" alt="<?php echo e($movie['title']); ?>">
                                    </div>
                                    <div class="recent-movie-title">
                                        <?php echo e($movie['title']); ?>
                                    </div>
                                    <div class="recent-movie-actions">
                                        <a href="editMovie.php?id=<?php echo $movie['id']; ?>" class="movie-action-button edit-button">Edit</a>
                                        <a href="deleteMovie.php?id=<?php echo $movie['id']; ?>" class="movie-action-button delete-button">Delete</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">No movies have been added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>