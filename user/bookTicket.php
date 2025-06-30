<?php
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$movie_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Fetch movie details
try {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        header('Location: movies.php');
        exit();
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Process booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_date = $_POST['booking_date'];
    $show_time = $_POST['show_time'];
    $seats = (int)$_POST['seats'];
    
    // Conditional validation
    if (empty($booking_date) || empty($show_time) || $seats < 1) {
        $error = 'Please fill in all fields with valid values';
    } else {
        $total_amount = $movie['price'] * $seats;
        
        try {
            // Database manipulation - Insert booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, movie_id, booking_date, show_time, seats_booked, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $movie_id, $booking_date, $show_time, $seats, $total_amount]);
            
            $success = 'Booking confirmed! Total amount: ₱' . number_format($total_amount, 2);
        } catch(PDOException $e) {
            $error = 'Booking failed: ' . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="main-content">
    <h2>Book Tickets for <?php echo htmlspecialchars($movie['title']); ?></h2>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div>
            <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="width: 100%; max-width: 300px; border-radius: 10px;">
        </div>
        <div>
            <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
            <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
            <p><strong>Duration:</strong> <?php echo $movie['duration']; ?> minutes</p>
            <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie['rating']); ?></p>
            <p><strong>Price per ticket:</strong> ₱<?php echo number_format($movie['price'], 2); ?></p>
            <p><strong>Description:</strong></p>
            <p><?php echo htmlspecialchars($movie['description']); ?></p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <a href="my_bookings.php" class="btn">View My Bookings</a>
    <?php else: ?>
        <div class="form-container">
            <h3>Booking Details</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="booking_date">Booking Date:</label>
                    <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="show_time">Show Time:</label>
                    <select id="show_time" name="show_time" required>
                        <option value="">Select Show Time</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="13:00">1:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="19:00">7:00 PM</option>
                        <option value="22:00">10:00 PM</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="seats">Number of Seats:</label>
                    <input type="number" id="seats" name="seats" min="1" max="10" required>
                </div>
                
                <div class="form-group">
                    <p><strong>Total Amount:</strong> ₱<span id="total-amount">0.00</span></p>
                </div>
                
                <button type="submit" class="btn">Confirm Booking</button>
                <a href="movies.php" class="btn btn-secondary">Back to Movies</a>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('seats').addEventListener('input', function() {
    const seats = parseInt(this.value) || 0;
    const price = <?php echo $movie['price']; ?>;
    const total = seats * price;
    document.getElementById('total-amount').textContent = total.toFixed(2);
});
</script>

<?php include '../includes/footer.php'; ?>