<?php
include '../includes/db.php';
session_start(); // Assuming you will use sessions for user logins

$user_id = 1; // Placeholder; change with $_SESSION['user_id'] after login system
$movie_id = $_POST['movie_id'];
$seat = $_POST['seat'];

if (!empty($movie_id) && !empty($seat)) {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $movie_id, $seat);

    if ($stmt->execute()) {
        echo "Ticket booked successfully! <a href='my_bookings.php'>View My Bookings</a>";
    } else {
        echo "Booking failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid input.";
}
?>