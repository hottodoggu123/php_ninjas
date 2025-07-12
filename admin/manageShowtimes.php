<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Get all showtimes with movie details
$showtimes = $adminService->getAllShowtimes();

// Handle search functionality
$searchTerm = $_GET['search'] ?? '';
if ($searchTerm) {
    $showtimes = $adminService->searchShowtimes($searchTerm);
}

// Get success/error messages
$successMessage = $_GET['success'] ?? '';
$errorMessage = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Showtimes - Cinema Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('manageShowtimes.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Manage Showtimes</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if ($successMessage): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo e($successMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo e($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <!-- Search and Add Section -->
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>All Showtimes</h2>
                    <a href="addShowtime.php" class="movie-action-button edit-button">Add Showtime</a>
                </div>
                
                <!-- Search Form -->
                <form method="GET" class="search-form" style="margin-bottom: 20px;">
                    <div class="search-container">
                        <input type="text" name="search" placeholder="Search by movie title..." 
                               value="<?php echo e($searchTerm); ?>" class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if ($searchTerm): ?>
                            <a href="manageShowtimes.php" class="clear-search">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Showtimes Table -->
            <div class="admin-section">
                <?php if ($showtimes && $showtimes->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Movie</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Total Seats</th>
                                    <th>Available Seats</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($showtime = $showtimes->fetch_assoc()): ?>
                                    <?php
                                    // Calculate available seats
                                    $availableSeats = ($showtime['total_seats'] ?? 40) - ($showtime['booked_seats'] ?? 0);
                                    $statusClass = $availableSeats <= 0 ? 'sold-out' : 'available';
                                    ?>
                                    <tr>
                                        <td><?php echo e($showtime['id']); ?></td>
                                        <td>
                                            <strong><?php echo e($showtime['movie_title'] ?? $showtime['title']); ?></strong>
                                        </td>
                                        <td><?php echo formatDate($showtime['show_date']); ?></td>
                                        <td><?php echo formatTime($showtime['show_time']); ?></td>
                                        <td><?php echo e($showtime['total_seats'] ?? 40); ?></td>
                                        <td class="<?php echo $statusClass; ?>">
                                            <?php echo $availableSeats; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $statusClass; ?>">
                                                <?php echo $availableSeats <= 0 ? 'Sold Out' : 'Available'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="editShowtime.php?id=<?php echo $showtime['id']; ?>" 
                                               class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Edit</a>
                                            <a href="deleteShowtime.php?id=<?php echo $showtime['id']; ?>" 
                                               class="movie-action-button delete-button" style="font-size: 0.8em; padding: 4px 8px;">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="info-message">
                        <i class="fas fa-calendar-times"></i>
                        <?php if ($searchTerm): ?>
                            No showtimes found matching "<?php echo e($searchTerm); ?>".
                        <?php else: ?>
                            No showtimes have been scheduled yet.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
