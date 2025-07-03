<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

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

    $seats_booked = count($seats);
    $price_per_seat = 250.00;
    $total_amount = $seats_booked * $price_per_seat;

    // Insert into bookings table
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, booking_date, show_time, seats_booked, total_amount) 
                            VALUES (?, ?, CURDATE(), (SELECT show_time FROM showtimes WHERE id = ?), ?, ?)");
    $stmt->bind_param("iiiid", $user_id, $movie_id, $showtime_id, $seats_booked, $total_amount);
    $stmt->execute();

    $booking_id = $stmt->insert_id;

    // Insert into booked_seats table
    $seatStmt = $conn->prepare("INSERT INTO booked_seats (booking_id, showtime_id, seat_number) VALUES (?, ?, ?)");
    foreach ($seats as $seat) {
        $seatStmt->bind_param("iis", $booking_id, $showtime_id, $seat);
        $seatStmt->execute();
    }

    echo "
    <div class='container' style='text-align:center;'>
        <h2 style='margin-bottom: 10px;'>🎉 Booking Confirmed!</h2>
        <p style='margin-bottom: 10px;'>You've successfully booked the following seat(s): <strong>" . implode(", ", $seats) . "</strong></p>

        <div style='margin: 0 auto 30px auto; text-align: left; max-width: 400px; border: 1px solid #ccc; border-radius: 10px; padding: 20px; background-color: #f9f9f9;'>
            <h3 style='margin-top: 0; text-align:center;'>Booking Breakdown</h3>
            <p><strong>Number of Seats:</strong> {$seats_booked}</p>
            <p><strong>Price per Seat:</strong> ₱" . number_format($price_per_seat, 2) . "</p>
            <hr>
            <p><strong>Total Amount:</strong> ₱" . number_format($total_amount, 2) . "</p>
        </div>

        <a href='../index.php' class='button'>Continue to Home</a>
    </div>
    ";
} else {
    echo "<div class='container'><p style='color:red;'>Invalid request.</p></div>";
}

include '../includes/footer.php';
?>