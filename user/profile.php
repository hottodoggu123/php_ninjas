<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Fetch bookings with seat numbers
$bookingStmt = $conn->prepare("
    SELECT 
        b.id, 
        m.title, 
        s.show_date, 
        s.show_time, 
        b.booking_status,
        GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
    FROM bookings b
    JOIN booked_seats bs ON b.id = bs.booking_id
    JOIN showtimes s ON bs.showtime_id = s.id
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY s.show_date DESC, s.show_time DESC
");
$bookingStmt->bind_param("i", $userId);
$bookingStmt->execute();
$bookings = $bookingStmt->get_result();
?>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="info-message">
            <?php
            echo htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h2>My Profile</h2>
    </div>

    <div class="profile-section">
        <h3>Account Information</h3>
        <div class="booking-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></p>
        </div>
    </div>

    <div class="profile-section">
        <h3>My Bookings</h3>
        
        <?php if ($bookings->num_rows > 0): ?>
            <?php while ($row = $bookings->fetch_assoc()): ?>
                <div class="booking-item">
                    <div class="booking-header">
                        <div class="movie-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="booking-status <?php echo $row['booking_status']; ?>">
                            <?php echo ucfirst($row['booking_status']); ?>
                        </div>
                    </div>
                    <div class="booking-details">
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($row['show_date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($row['show_time'])); ?></p>
                        <p><strong>Seats:</strong> <?php echo htmlspecialchars($row['seats']); ?></p>
                        
                        <?php if ($row['booking_status'] !== 'cancelled'): ?>
                            <form method="POST" action="cancelBooking.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');" style="margin-top: 15px; display: inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" style="background: #d32f2f; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85em; cursor: pointer;">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="info-message">
                No bookings found. <a href="../index.php" style="color: #1976d2; text-decoration: underline;">Browse movies</a> to make your first booking!
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>