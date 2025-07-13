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
            <div class="row">
                <!-- Booking Information Section (Left) -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Booking Details</h3>
                        </div>
                        <div class="card-body">
                            <h4 style="margin-top: 0; color: #303030; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($booking['title']); ?></h4>
                            
                            <div style="background: #f8f8f8; padding: 25px; border-radius: 10px; margin-bottom: 20px;">
                                <table class="booking-details-table" style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Booking ID:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Date:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo date('F j, Y', strtotime($booking['show_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Time:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo date('h:i A', strtotime($booking['show_time'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Seats:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($booking['seats']); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Number of Seats:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo $booking['seats_booked']; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Payment Method:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo ucwords(str_replace('_', ' ', $booking['payment_method'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;">Payment Reference:</th>
                                        <td style="padding: 10px 15px; background: #fff; border-bottom: 1px solid #eee;"><?php echo $booking['payment_reference']; ?></td>
                                    </tr>
                                    <tr>
                                        <th style="text-align: left; padding: 10px 15px; background: #fff;">Status:</th>
                                        <td style="padding: 10px 15px; background: #fff;">
                                            <span class="booking-status <?php echo strtolower($booking['booking_status']); ?>">
                                                <?php echo ucfirst($booking['booking_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background: #303030; color: white; padding: 20px; border-radius: 10px; text-align: center;">
                                <p style="font-size: 1.4em; font-weight: 700; margin: 0;">Total Amount: ₱<?php echo number_format($booking['total_amount'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Movie Poster Section (Right) -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Movie Poster</h3>
                        </div>
                        <div class="card-body" style="text-align: center;">
                            <img src="../<?php echo htmlspecialchars($booking['poster_url']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?>" style="width: 100%; max-width: 300px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons" style="text-align: center; margin-top: 30px;">
            <a href="../index.php" style="display: block; width: 200px; margin: 0 auto 15px auto; padding: 12px 25px; background: #303030; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">Return to Home</a>
            <a href="profile.php" style="display: block; width: 200px; margin: 0 auto; padding: 12px 25px; background: #303030; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">View All Bookings</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
