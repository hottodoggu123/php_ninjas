<?php
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$bookings = [];

// Array implementation and database manipulation
try {
    $stmt = $pdo->prepare("
        SELECT b.*, m.title, m.poster_url, m.genre, m.duration 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        
        header('Location: my_bookings.php?cancelled=1');
        exit();
    } catch(PDOException $e) {
        $error = "Error cancelling booking: " . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<div class="main-content">
    <h2>My Bookings</h2>
    
    <?php if (isset($_GET['cancelled'])): ?>
        <div class="alert alert-success">Booking cancelled successfully!</div>
    <?php endif; ?>
    
    <?php if (empty($bookings)): ?>
        <div class="alert alert-error">You haven't made any bookings yet.</div>
        <a href="movies.php" class="btn">Browse Movies</a>
    <?php else: ?>
        <div class="bookings-container">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card" style="background: white; padding: 1.5rem; margin-bottom: 1rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 1rem; align-items: center;">
                        <img src="../<?php echo htmlspecialchars($booking['poster_url']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?>" style="width: 80px; height: 120px; object-fit: cover; border-radius: 5px;">
                        
                        <div>
                            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                            <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                            <p><strong>Show Time:</strong> <?php echo date('g:i A', strtotime($booking['show_time'])); ?></p>
                            <p><strong>Seats:</strong> <?php echo $booking['seats_booked']; ?></p>
                            <p><strong>Total Amount:</strong> ₱<?php echo number_format($booking['total_amount'], 2); ?></p>
                            <p><strong>Status:</strong> 
                                <span style="color: <?php echo $booking['booking_status'] == 'confirmed' ? 'green' : 'red'; ?>;">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </p>
                            <p><strong>Booked on:</strong> <?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></p>
                        </div>
                        
                        <div>
                            <?php if ($booking['booking_status'] == 'confirmed' && strtotime($booking['booking_date']) > time()): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" name="cancel_booking" class="btn btn-secondary">Cancel Booking</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>