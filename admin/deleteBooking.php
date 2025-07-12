<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;

// Check if booking ID is provided
if (isset($_GET['id'])) {
    $booking_id = (int) $_GET['id'];
    
    // Get booking details with user and movie information
    $stmt = $conn->prepare("
        SELECT 
            b.id, 
            u.username, 
            m.title as movie_title, 
            b.booking_date, 
            b.show_time, 
            b.seats_booked, 
            b.total_amount, 
            b.payment_status, 
            b.booking_status, 
            b.created_at
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN movies m ON b.movie_id = m.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if ($booking) {
        // If confirmed, delete the booking
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            // Start transaction to delete booking and related data
            $conn->begin_transaction();
            
            try {
                // Delete booked seats first
                $deleteSeatsStmt = $conn->prepare("DELETE FROM booked_seats WHERE booking_id = ?");
                $deleteSeatsStmt->bind_param("i", $booking_id);
                $deleteSeatsStmt->execute();
                
                // Delete booking
                $deleteBookingStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
                $deleteBookingStmt->bind_param("i", $booking_id);
                $deleteBookingStmt->execute();
                
                $conn->commit();
                $success = true;
                $message = "Booking #" . $booking_id . " for \"" . e($booking['movie_title']) . "\" has been successfully deleted.";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error deleting booking. Please try again.";
            }
        }
    } else {
        $message = "Booking not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Booking - Cinema Booking Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('manageBookings.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Delete Booking</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Delete Booking</h2>
                    <a href="manageBookings.php" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Back to Bookings</a>
                </div>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageBookings.php" class="button primary-button">Back to Manage Bookings</a>
                            <a href="dashboard.php" class="button secondary-button">Go to Dashboard</a>
                        </div>
                    </div>
                <?php elseif ($message && !$success): ?>
                    <div class="error-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageBookings.php" class="button primary-button">Back to Manage Bookings</a>
                        </div>
                    </div>
                <?php elseif (isset($booking)): ?>
                    <div class="delete-confirmation" style="text-align: center; max-width: 600px; margin: 0 auto;">
                        <h3>Are you sure you want to delete this booking?</h3>
                        
                        <div class="delete-booking-details" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                            <div class="delete-booking-info" style="text-align: center;">
                                <h3>Booking #<?php echo e($booking['id']); ?></h3>
                                
                                <div class="booking-details" style="text-align: left; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                                    <p><strong>User:</strong> <?php echo e($booking['username']); ?></p>
                                    <p><strong>Movie:</strong> <?php echo e($booking['movie_title']); ?></p>
                                    <p><strong>Date:</strong> <?php echo formatDate($booking['booking_date']); ?></p>
                                    <p><strong>Time:</strong> <?php echo formatTime($booking['show_time']); ?></p>
                                    <p><strong>Seats:</strong> <?php echo e($booking['seats_booked']); ?></p>
                                    <p><strong>Amount:</strong> <?php echo formatCurrency($booking['total_amount']); ?></p>
                                    <p><strong>Payment Status:</strong> 
                                        <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                            <?php echo e(ucfirst($booking['payment_status'])); ?>
                                        </span>
                                    </p>
                                    <p><strong>Booking Status:</strong> 
                                        <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                                            <?php echo e(ucfirst($booking['booking_status'])); ?>
                                        </span>
                                    </p>
                                    <p><strong>Created:</strong> <?php echo formatDate($booking['created_at']); ?></p>
                                </div>
                                
                                <p style="color: #d32f2f; font-weight: bold; margin: 20px 0;">⚠️ This action cannot be undone. Deleting this booking will also remove all associated seat reservations.</p>
                                
                                <form method="POST">
                                    <input type="hidden" name="confirm" value="yes">
                                    <div class="button-container">
                                        <button type="submit" class="button" style="background-color: #d32f2f; color: white;">Yes, Delete Booking</button>
                                        <a href="manageBookings.php" class="button secondary-button">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        No booking ID provided.
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageBookings.php" class="button primary-button">Back to Manage Bookings</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
