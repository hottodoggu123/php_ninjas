<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$success = false;

// Check if movie ID is provided
if (isset($_GET['id'])) {
    $movie_id = (int) $_GET['id'];
    
    // Get movie details first (for confirmation)
    $stmt = $conn->prepare("SELECT title, poster_url FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $movie = $stmt->get_result()->fetch_assoc();
    
    if ($movie) {
        // If confirmed, delete the movie
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            // First delete any associated showtimes
            $showtime_stmt = $conn->prepare("DELETE FROM showtimes WHERE movie_id = ?");
            $showtime_stmt->bind_param("i", $movie_id);
            $showtime_stmt->execute();
            
            // Then delete any associated bookings
            $booking_stmt = $conn->prepare("DELETE FROM bookings WHERE movie_id = ?");
            $booking_stmt->bind_param("i", $movie_id);
            $booking_stmt->execute();
            
            // Finally delete the movie
            $delete_stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
            $delete_stmt->bind_param("i", $movie_id);
            
            if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
                $success = true;
                $message = "Movie \"" . htmlspecialchars($movie['title']) . "\" has been successfully deleted.";
            } else {
                $message = "Error deleting movie. Please try again.";
            }
        }
    } else {
        $message = "Movie not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Movie - Cinema Booking Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>Cinema Admin</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="manageMovies.php" class="active">
                        <i class="fas fa-film"></i>
                        <span>Movies</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Showtimes</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Delete Movie</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageMovies.php" class="button primary-button">Back to Manage Movies</a>
                            <a href="dashboard.php" class="button secondary-button">Go to Dashboard</a>
                        </div>
                    </div>
                <?php elseif ($message && !$success): ?>
                    <div class="error-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageMovies.php" class="button primary-button">Back to Manage Movies</a>
                        </div>
                    </div>
                <?php elseif (isset($movie)): ?>
                    <div class="delete-confirmation">
                        <h2>Are you sure you want to delete this movie?</h2>
                        
                        <div class="delete-movie-details">
                            <div class="delete-movie-poster">
                                <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            </div>
                            <div class="delete-movie-info">
                                <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                                <p>This action cannot be undone. Deleting this movie will also remove all associated showtimes and bookings.</p>
                                
                                <form method="POST">
                                    <input type="hidden" name="confirm" value="yes">
                                    <div class="button-container">
                                        <button type="submit" class="button danger-button">Yes, Delete Movie</button>
                                        <a href="manageMovies.php" class="button secondary-button">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        No movie ID provided.
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageMovies.php" class="button primary-button">Back to Manage Movies</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>