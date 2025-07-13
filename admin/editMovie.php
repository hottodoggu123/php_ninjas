<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;
$movie = null;

// Check if movie ID is provided
if (!isset($_GET['id'])) {
    header('Location: manageMovies.php');
    exit;
}

$movie_id = (int) $_GET['id'];

// Get movie details using service
$movie = $movieService->getMovieById($movie_id);

if (!$movie) {
    header('Location: manageMovies.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movieData = [
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'genre' => trim($_POST['genre']),
        'duration' => (int)$_POST['duration'],
        'rating' => trim($_POST['rating']),
        'release_date' => $_POST['release_date'],
        'poster_url' => trim($_POST['poster_url']),
        'status' => $_POST['status'],
        'price' => (float)$_POST['price']
    ];
    
    // Basic validation
    if (empty($movieData['title']) || empty($movieData['description']) || empty($movieData['poster_url'])) {
        $message = "Please fill in all required fields.";
    } else {
        $success = $movieService->updateMovie($movie_id, $movieData);
        if ($success) {
            $message = "Movie \"" . e($movieData['title']) . "\" has been successfully updated.";
            // Refresh movie data
            $movie = $movieService->getMovieById($movie_id);
        } else {
            $message = "Error updating movie. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - CineXpress Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('manageMovies.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Edit Movie</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2><?php echo e($movie['title']); ?></h2>
                    <a href="manageMovies.php" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Back to Movies</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" class="movie-form">
                        <div class="form-group">
                            <label for="title">Movie Title *</label>
                            <input type="text" id="title" name="title" required value="<?php echo e($movie['title']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" required rows="4" style="width: 100%; padding: 8px; box-sizing: border-box;"><?php echo e($movie['description']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="genre">Genre</label>
                                <input type="text" id="genre" name="genre" value="<?php echo e($movie['genre']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                            </div>
                            
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" id="duration" name="duration" min="1" value="<?php echo $movie['duration']; ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select id="rating" name="rating" style="width: 100%; padding: 8px; box-sizing: border-box;">
                                    <option value="G" <?php echo ($movie['rating'] === 'G') ? 'selected' : ''; ?>>G</option>
                                    <option value="PG" <?php echo ($movie['rating'] === 'PG') ? 'selected' : ''; ?>>PG</option>
                                    <option value="PG-13" <?php echo ($movie['rating'] === 'PG-13') ? 'selected' : ''; ?>>PG-13</option>
                                    <option value="R" <?php echo ($movie['rating'] === 'R') ? 'selected' : ''; ?>>R</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="release_date">Release Date</label>
                                <input type="date" id="release_date" name="release_date" value="<?php echo $movie['release_date']; ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="poster_url">Poster URL *</label>
                            <input type="url" id="poster_url" name="poster_url" required value="<?php echo e($movie['poster_url']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" style="width: 100%; padding: 8px; box-sizing: border-box;">
                                    <option value="now_showing" <?php echo ($movie['status'] === 'now_showing') ? 'selected' : ''; ?>>Now Showing</option>
                                    <option value="coming_soon" <?php echo ($movie['status'] === 'coming_soon') ? 'selected' : ''; ?>>Coming Soon</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Ticket Price (₱)</label>
                                <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo $movie['price']; ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                            </div>
                        </div>
                        
                        <div class="button-container">
                            <button type="submit" class="button primary-button">Update Movie</button>
                            <a href="manageMovies.php" class="button secondary-button">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
