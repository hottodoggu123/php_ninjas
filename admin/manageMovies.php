<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Fetch all movies using service
$movies = $movieService->getAllMovies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies - CineXpress Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <?php renderAdminSidebar('manageMovies.php'); ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Manage Movies</h1>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                <span><?php echo date('F j, Y'); ?></span>
            </div>
        </div>
        <div class="admin-section">
            <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>All Movies</h2>
                <a href="addMovie.php" class="movie-action-button edit-button">Add Movie</a>
            </div>
            <div class="table-container">
                <?php if ($movies && $movies->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php 
                                $movies->data_seek(0);
                                $firstRow = $movies->fetch_assoc();
                                $movies->data_seek(0);
                                foreach (array_keys($firstRow) as $field): ?>
                                    <th><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?></th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $movies->fetch_assoc()): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo e($cell); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <a href="editMovie.php?id=<?php echo $row['id']; ?>" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Edit</a>
                                    <a href="deleteMovie.php?id=<?php echo $row['id']; ?>" class="movie-action-button delete-button" style="font-size: 0.8em; padding: 4px 8px;">Delete</a>
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
