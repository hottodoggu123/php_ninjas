<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all movies
$movies = $conn->query("SELECT * FROM movies");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies - Cinema Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>Cinema Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
            <li><a href="manageMovies.php" class="active"><i class="fas fa-film"></i><span>Movies</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i><span>Users</span></a></li>
            <li><a href="#"><i class="fas fa-ticket-alt"></i><span>Bookings</span></a></li>
            <li><a href="#"><i class="fas fa-calendar-alt"></i><span>Showtimes</span></a></li>
            <li><a href="#"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Manage Movies</h1>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                <span><?php echo date('F j, Y'); ?></span>
            </div>
        </div>
        <div class="admin-section">
            <div class="admin-section-header">
                <h2>All Movies</h2>
                <a href="addMovie.php" class="movie-action-button edit-button" style="float:right;">Add Movie</a>
            </div>
            <div class="table-container">
                <?php if ($movies && $movies->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php foreach ($movies->fetch_fields() as $field): ?>
                                    <th><?php echo htmlspecialchars($field->name); ?></th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $movies->data_seek(0); while($row = $movies->fetch_assoc()): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo htmlspecialchars($cell); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <a href="editMovie.php?id=<?php echo $row['id']; ?>" class="movie-action-button edit-button">Edit</a>
                                    <a href="deleteMovie.php?id=<?php echo $row['id']; ?>" class="movie-action-button delete-button" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="info-message">No movies found.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
