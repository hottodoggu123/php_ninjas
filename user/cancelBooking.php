<?php
include '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = (int) $_POST['booking_id'];
    $userId = $_SESSION['user_id'];

    // Check if the booking belongs to the user
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['message'] = "Invalid or unauthorized booking.";
        header("Location: profile.php");
        exit;
    }
    $stmt->close();

    // Update booking status
    $update = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $update->bind_param("i", $bookingId);
    $update->execute();
    $update->close();

    // Delete associated seat reservations
    $deleteSeats = $conn->prepare("DELETE FROM booked_seats WHERE booking_id = ?");
    $deleteSeats->bind_param("i", $bookingId);
    $deleteSeats->execute();
    $deleteSeats->close();

    $_SESSION['message'] = "Booking cancelled successfully and seats freed.";
    header("Location: profile.php");
    exit;
}

$_SESSION['message'] = "Invalid request.";
header("Location: profile.php");
exit;