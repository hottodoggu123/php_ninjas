<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Get all active movies for dropdown
$movies = $conn->query("SELECT id, title FROM movies WHERE status = 'now_showing' ORDER BY title");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'] ?? '';
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';
    $total_seats = $_POST['total_seats'] ?? 40;
    
    $errors = [];
    
    // Validation
    if (empty($movie_id)) {
        $errors[] = 'Please select a movie.';
    }
    
    if (empty($show_date)) {
        $errors[] = 'Please select a show date.';
    } elseif (strtotime($show_date) < strtotime('today')) {
        $errors[] = 'Show date cannot be in the past.';
    }
    
    if (empty($show_time)) {
        $errors[] = 'Please select a show time.';
    }
    
    if (empty($total_seats) || $total_seats < 1) {
        $errors[] = 'Please enter a valid number of seats.';
    }
    
    // Check for duplicate showtime
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM showtimes WHERE movie_id = ? AND show_date = ? AND show_time = ?");
        $stmt->bind_param("iss", $movie_id, $show_date, $show_time);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'A showtime already exists for this movie at the selected date and time.';
        }
        $stmt->close();
    }
    
    if (empty($errors)) {
        // Insert new showtime
        $stmt = $conn->prepare("INSERT INTO showtimes (movie_id, show_date, show_time, total_seats) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $movie_id, $show_date, $show_time, $total_seats);
        
        if ($stmt->execute()) {
            header("Location: manageShowtimes.php?success=" . urlencode("Showtime added successfully"));
            exit;
        } else {
            $errors[] = 'Failed to add showtime. Please try again.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Showtime - Cinema Admin</title>
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
                    <h1>Add Showtime</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Add Showtime Form -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Showtime Details</h2>
                </div>
                
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="movie_id">Movie <span class="required">*</span></label>
                        <select name="movie_id" id="movie_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                            <option value="">Select Movie</option>
                            <?php if ($movies && $movies->num_rows > 0): ?>
                                <?php while($movie = $movies->fetch_assoc()): ?>
                                    <option value="<?php echo $movie['id']; ?>" 
                                            <?php echo (isset($_POST['movie_id']) && $_POST['movie_id'] == $movie['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($movie['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="show_date">Show Date <span class="required">*</span></label>
                        <input type="date" name="show_date" id="show_date" 
                               value="<?php echo e($_POST['show_date'] ?? ''); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    
                    <div class="form-group">
                        <label for="show_time">Show Time <span class="required">*</span></label>
                        <input type="time" name="show_time" id="show_time" 
                               value="<?php echo e($_POST['show_time'] ?? ''); ?>" required
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    </div>
                    
                    <div class="form-group">
                        <label for="total_seats">Total Seats <span class="required">*</span></label>
                        <input type="number" name="total_seats" id="total_seats" 
                               value="<?php echo e($_POST['total_seats'] ?? 40); ?>" 
                               min="1" max="200" required
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                        <small class="form-help">Number of seats available for this showtime</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-plus"></i> Add Showtime
                        </button>
                        <a href="manageShowtimes.php" class="button" style="background-color: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-flex; align-items: center; gap: 8px; margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
