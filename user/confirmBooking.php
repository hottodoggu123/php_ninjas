<?php
include '../includes/init.php';
include '../includes/header.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $movie_id = (int) $_POST['movie_id'];
    $showtime_id = (int) $_POST['showtime_id'];
    $seats = $_POST['seats'] ?? [];

    if (empty($seats)) {
        echo "<div class='container'><p style='color:red;'>Please select at least one seat.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Get movie details using service
    $movie = $movieService->getMovieById($movie_id);
    if (!$movie) {
        echo "<div class='container'><p style='color:red;'>Movie not found.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Get showtime details
    $showtimeStmt = $conn->prepare("SELECT show_date, show_time FROM showtimes WHERE id = ?");
    $showtimeStmt->bind_param("i", $showtime_id);
    $showtimeStmt->execute();
    $showtimeResult = $showtimeStmt->get_result()->fetch_assoc();
    
    if (!$showtimeResult) {
        echo "<div class='container'><p style='color:red;'>Showtime not found.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    $seats_booked = count($seats);
    $price_per_seat = $movie['price'] ?? 250.00;
    $total_amount = $seats_booked * $price_per_seat;
    
    // Store booking data in session for payment.php
    $_SESSION['booking_data'] = [
        'movie_id' => $movie_id,
        'showtime_id' => $showtime_id,
        'seats' => $seats,
        'movie_title' => $movie['title'],
        'price' => $price_per_seat,
        'show_date' => $showtimeResult['show_date'],
        'show_time' => $showtimeResult['show_time']
    ];
    
    // Display confirmation page with payment button
    echo "
    <div class='container' style='text-align:center;'>
        <h2 style='margin-bottom: 10px;'>Review Your Booking</h2>
        <p style='margin-bottom: 10px;'>You're booking the following seat(s): <strong>" . implode(", ", $seats) . "</strong> for <strong>" . e($movie['title']) . "</strong></p>

        <div style='margin: 0 auto 30px auto; text-align: left; max-width: 400px; border: 1px solid #ccc; border-radius: 10px; padding: 20px; background-color: #f9f9f9;'>
            <h3 style='margin-top: 0; text-align:center;'>Booking Details</h3>
            <p><strong>Movie:</strong> " . e($movie['title']) . "</p>
            <p><strong>Date:</strong> " . formatDate($showtimeResult['show_date']) . "</p>
            <p><strong>Time:</strong> " . formatTime($showtimeResult['show_time']) . "</p>
            <p><strong>Number of Seats:</strong> {$seats_booked}</p>
            <p><strong>Price per Seat:</strong> " . formatCurrency($price_per_seat) . "</p>
            <hr>
            <p><strong>Total Amount:</strong> " . formatCurrency($total_amount) . "</p>
        </div>

        <a href='payment.php' class='button primary-button'>Proceed to Payment</a>
        <a href='bookTicket.php?movie_id={$movie_id}' class='button'>Back to Seat Selection</a>
    </div>
    ";
} else {
    echo "<div class='container'><p style='color:red;'>Invalid request.</p></div>";
}

include '../includes/footer.php';
?>