<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Get showtime ID from URL
$showtime_id = $_GET['id'] ?? '';

if (empty($showtime_id)) {
    header("Location: manageShowtimes.php?error=" . urlencode("Invalid showtime ID"));
    exit;
}

// Get showtime details with movie info
$stmt = $conn->prepare("SELECT s.*, m.title as movie_title, m.poster_url FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$showtime = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$showtime) {
    header("Location: manageShowtimes.php?error=" . urlencode("Showtime not found"));
    exit;
}

// Check if there are any bookings for this showtime
$stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE showtime_id = ? AND booking_status = 'confirmed'");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$booking_count = $result['booking_count'];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        $conn->begin_transaction();
        
        try {
            // Delete associated bookings first (cascade effect)
            $stmt = $conn->prepare("DELETE FROM bookings WHERE showtime_id = ?");
            $stmt->bind_param("i", $showtime_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete associated booked seats
            $stmt = $conn->prepare("DELETE FROM booked_seats WHERE showtime_id = ?");
            $stmt->bind_param("i", $showtime_id);
            $stmt->execute();
            $stmt->close();
            
            // Delete the showtime
            $stmt = $conn->prepare("DELETE FROM showtimes WHERE id = ?");
            $stmt->bind_param("i", $showtime_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->commit();
            header("Location: manageShowtimes.php?success=" . urlencode("Showtime deleted successfully"));
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: manageShowtimes.php?error=" . urlencode("Failed to delete showtime"));
            exit;
        }
    } else {
        header("Location: manageShowtimes.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Showtime - Cinema Admin</title>
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
                    <h1>Delete Showtime</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <!-- Delete Confirmation -->
            <div class="admin-section">
                <div class="admin-section-header">
                    <h2>Confirm Deletion</h2>
                </div>
                
                <div class="delete-confirmation" style="text-align: center; max-width: 600px; margin: 0 auto;">
                    <h3>Are you sure you want to delete this showtime?</h3>
                    
                    <div class="delete-movie-details" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                        <div class="delete-movie-poster" style="width: 200px; height: 300px; overflow: hidden; border-radius: 8px;">
                            <?php if (!empty($showtime['poster_url'])): ?>
                                <img src="../<?php echo e($showtime['poster_url']); ?>" alt="<?php echo e($showtime['movie_title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="no-poster" style="width: 100%; height: 100%; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-film" style="font-size: 48px; color: #6c757d;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="delete-movie-info" style="text-align: center;">
                            <h3><?php echo e($showtime['movie_title']); ?></h3>
                            
                            <div class="movie-details" style="text-align: left; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                                <p><strong>Date:</strong> <?php echo formatDate($showtime['show_date']); ?></p>
                                <p><strong>Time:</strong> <?php echo formatTime($showtime['show_time']); ?></p>
                                <p><strong>Total Seats:</strong> <?php echo e($showtime['total_seats']); ?></p>
                            </div>
                            
                            <?php if ($booking_count > 0): ?>
                                <p style="color: #d32f2f; font-weight: bold; margin: 20px 0;">⚠️ This showtime has <?php echo $booking_count; ?> confirmed booking(s). Deleting this showtime will also cancel all associated bookings and refund the customers.</p>
                            <?php endif; ?>
                            
                            <p style="color: #d32f2f; font-weight: bold; margin: 20px 0;">⚠️ This action cannot be undone.</p>
                            
                            <form method="POST">
                                <input type="hidden" name="confirm_delete" value="yes">
                                <div class="button-container">
                                    <button type="submit" class="button" style="background-color: #d32f2f; color: white;">Yes, Delete Showtime</button>
                                    <a href="manageShowtimes.php" class="button secondary-button">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
