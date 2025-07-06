<?php
include '../includes/init.php';
include '../includes/header.php';

// Redirect if not logged in or no booking ID in session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['booking_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$bookingId = $_SESSION['booking_id'];

// Get booking details
$stmt = $conn->prepare("
    SELECT 
        b.id, 
        m.title, 
        m.poster_url,
        s.show_date, 
        s.show_time, 
        b.seats_booked,
        b.total_amount,
        b.payment_method,
        b.payment_status,
        b.payment_reference,
        b.booking_status,
        GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    JOIN showtimes s ON b.showtime_id = s.id
    LEFT JOIN booked_seats bs ON b.id = bs.booking_id
    WHERE b.id = ? AND b.user_id = ?
    GROUP BY b.id
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// If booking not found, redirect to profile
if (!$booking) {
    $_SESSION['message'] = "Booking not found.";
    header("Location: profile.php");
    exit;
}

// Clear the booking ID from session after retrieving it
unset($_SESSION['booking_id']);
?>

<div class="container">
    <div class="page-header">
        <h2>Booking Confirmed!</h2>
    </div>
    
    <div class="booking-confirmation">
        <div class="success-icon">✓</div>
        
        <div class="confirmation-message">
            <h3>Thank you for your booking!</h3>
            <p>Your booking has been confirmed and your tickets are ready. You can view your booking details below.</p>
        </div>
        
        <div class="booking-details-card">
            <div class="booking-poster">
                <img src="../<?php echo htmlspecialchars($booking['poster_url']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?>">
            </div>
            
            <div class="booking-info">
                <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                
                <table class="booking-details-table">
                    <tr>
                        <th>Booking ID:</th>
                        <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                    </tr>
                    <tr>
                        <th>Date:</th>
                        <td><?php echo date('F j, Y', strtotime($booking['show_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Time:</th>
                        <td><?php echo date('h:i A', strtotime($booking['show_time'])); ?></td>
                    </tr>
                    <tr>
                        <th>Seats:</th>
                        <td><?php echo htmlspecialchars($booking['seats']); ?></td>
                    </tr>
                    <tr>
                        <th>Number of Seats:</th>
                        <td><?php echo $booking['seats_booked']; ?></td>
                    </tr>
                    <tr>
                        <th>Total Amount:</th>
                        <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td><?php echo ucwords(str_replace('_', ' ', $booking['payment_method'])); ?></td>
                    </tr>
                    <tr>
                        <th>Payment Reference:</th>
                        <td><?php echo $booking['payment_reference']; ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="booking-status <?php echo strtolower($booking['booking_status']); ?>">
                                <?php echo ucfirst($booking['booking_status']); ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="../index.php" class="button">Return to Home</a>
            <a href="profile.php" class="button primary-button">View All Bookings</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
