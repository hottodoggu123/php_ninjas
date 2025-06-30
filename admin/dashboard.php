<?php
require_once '../includes/db.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get dashboard statistics using array implementation
$stats = [
    'total_movies' => 0,
    'total_users' => 0,
    'total_bookings' => 0,
    'total_revenue' => 0,
    'recent_bookings' => []
];

try {
    // Count total movies
    $stmt = $pdo->query("SELECT COUNT(*) FROM movies");
    $stats['total_movies'] = $stmt->fetchColumn();
    
    // Count total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Count total bookings and revenue
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_revenue FROM bookings WHERE booking_status = 'confirmed'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_bookings'] = $result['total_bookings'] ?? 0;
    $stats['total_revenue'] = $result['total_revenue'] ?? 0;
    
    // Get recent bookings
    $stmt = $pdo->query("
        SELECT b.*, m.title, u.username 
        FROM bookings b 
        JOIN movies m ON b.movie_id = m.id 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.created_at DESC 
        LIMIT 10
    ");
    $stats['recent_bookings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<div class="main-content">
    <h2>Admin Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_movies']; ?></div>
            <div class="stat-label">Total Movies</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 3rem;">
        <a href="add_movie.php" class="btn">Add New Movie</a>
        <a href="manage_movies.php" class="btn">Manage Movies</a>
        <a href="manage_bookings.php" class="btn">Manage Bookings</a>
        <a href="manage_users.php" class="btn">Manage Users</a>
    </div>
    
    <!-- Recent Bookings -->
    <div>
        <h3 style="margin-bottom: 1rem;">Recent Bookings</h3>
        <?php if (empty($stats['recent_bookings'])): ?>
            <div class="alert alert-error">No bookings found.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Movie</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seats</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Booked On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_bookings'] as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['username']); ?></td>
                            <td><?php echo htmlspecialchars($booking['title']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                            <td><?php echo date('g:i A', strtotime($booking['show_time'])); ?></td>
                            <td><?php echo $booking['seats_booked']; ?></td>
                            <td>₱<?php echo number_format($booking['total_amount'], 2); ?></td>
                            <td>
                                <span style="color: <?php echo $booking['booking_status'] == 'confirmed' ? 'green' : 'red'; ?>;">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, g:i A', strtotime($booking['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>