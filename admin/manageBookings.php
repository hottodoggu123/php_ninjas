<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Fetch all bookings with user and movie information
$bookings = $conn->query("
    SELECT 
        b.id, 
        u.username, 
        m.title as movie_title, 
        b.booking_date, 
        b.show_time, 
        b.seats_booked, 
        b.total_amount, 
        b.payment_status, 
        b.booking_status, 
        b.created_at
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.id ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Cinema Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <?php renderAdminSidebar('manageBookings.php'); ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Manage Bookings</h1>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                <span><?php echo date('F j, Y'); ?></span>
            </div>
        </div>
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>All Bookings</h2>
            </div>
            <div class="table-container">
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Movie</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Seats</th>
                                <th>Amount</th>
                                <th>Payment Status</th>
                                <th>Booking Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo e($booking['id']); ?></td>
                                <td><?php echo e($booking['username']); ?></td>
                                <td><?php echo e($booking['movie_title']); ?></td>
                                <td><?php echo formatDate($booking['booking_date']); ?></td>
                                <td><?php echo formatTime($booking['show_time']); ?></td>
                                <td><?php echo e($booking['seats_booked']); ?></td>
                                <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                                        <?php echo e(ucfirst($booking['payment_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                                        <?php echo e(ucfirst($booking['booking_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($booking['created_at']); ?></td>
                                <td>
                                    <a href="deleteBooking.php?id=<?php echo $booking['id']; ?>" class="movie-action-button delete-button" style="font-size: 0.8em; padding: 4px 8px;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="info-message">No bookings found.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
