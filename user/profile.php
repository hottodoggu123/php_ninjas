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
        <div style="padding: 10px; background-color: #d9edf7; color: #31708f; margin-bottom: 20px; border: 1px solid #bce8f1; border-radius: 5px;">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <h2>My Profile</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>

    <hr>
    <h3>My Bookings</h3>

    <?php if ($bookings->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
            <tr>
                <th>Movie</th>
                <th>Date</th>
                <th>Time</th>
                <th>Seats</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo $row['show_date']; ?></td>
                    <td><?php echo date('h:i A', strtotime($row['show_time'])); ?></td>
                    <td><?php echo htmlspecialchars($row['seats']); ?></td>
                    <td><?php echo ucfirst($row['booking_status']); ?></td>
                    <td>
                        <?php if ($row['booking_status'] !== 'cancelled'): ?>
                            <form method="POST" action="cancelBooking.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');" style="margin: 0;">
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="button" style="background: #d9534f;">Cancel</button>
                            </form>
                        <?php else: ?>
                            Cancelled
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>