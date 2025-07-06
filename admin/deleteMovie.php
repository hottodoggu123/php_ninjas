<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;

// Check if movie ID is provided
if (isset($_GET['id'])) {
    $movie_id = (int) $_GET['id'];
    
    // Get movie details first (for confirmation) using service
    $movie = $movieService->getMovieById($movie_id);
    
    if ($movie) {
        // If confirmed, delete the movie
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            $success = $movieService->deleteMovie($movie_id);
            
            if ($success) {
                $message = "Movie \"" . e($movie['title']) . "\" has been successfully deleted.";
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
        <?php renderAdminSidebar('manageMovies.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Delete Movie</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
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
                                <img src="../<?php echo e($movie['poster_url']); ?>" alt="<?php echo e($movie['title']); ?>">
                            </div>
                            <div class="delete-movie-info">
                                <h3><?php echo e($movie['title']); ?></h3>
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