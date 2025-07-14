<?php
include '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $bookingId = (int) $_GET['id'];
    $userId = $_SESSION['user_id'];

    // Check if the booking belongs to the user and is not already cancelled
    $stmt = $conn->prepare("SELECT id, booking_status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    $stmt->store_result();
    
    // Bind the result to variables
    $stmt->bind_result($bookingIdResult, $bookingStatus);

    if ($stmt->num_rows === 0) {
        $_SESSION['message'] = "Invalid or unauthorized booking.";
        header("Location: profile.php");
        exit;
    }
    
    // Fetch the result
    $stmt->fetch();
    $stmt->close();
    
    // Check if the booking is already cancelled
    if ($bookingStatus === 'cancelled') {
        $_SESSION['message'] = "This booking has already been cancelled.";
        header("Location: profile.php");
        exit;
    }

    // Update booking status
    $update = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $update->bind_param("i", $bookingId);
    $update->execute();
    $update->close();

    $_SESSION['message'] = "Booking cancelled successfully and seats freed.";
    header("Location: profile.php");
    exit;
}

$_SESSION['message'] = "Invalid request.";
header("Location: profile.php");
exit;