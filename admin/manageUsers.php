<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

// Fetch all users (including admins)
$users = $conn->query("SELECT id, username, display_name, email, role, created_at FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Cinema Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">
    <?php renderAdminSidebar('manageUsers.php'); ?>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div class="admin-header">
            <div class="admin-title">
                <h1>Manage Users</h1>
            </div>
            <div class="admin-user">
                <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                <span><?php echo date('F j, Y'); ?></span>
            </div>
        </div>
        <div class="admin-section">
            <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>All Users</h2>
                <a href="addUser.php" class="movie-action-button edit-button">Add User</a>
            </div>
            <div class="table-container">
                <?php if ($users && $users->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Display Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo e($user['id']); ?></td>
                                <td><?php echo e($user['username']); ?></td>
                                <td><?php echo e($user['display_name'] ?? 'N/A'); ?></td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><?php echo e(ucfirst($user['role'])); ?></td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <a href="editUser.php?id=<?php echo $user['id']; ?>" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Edit</a>
                                    <a href="deleteUser.php?id=<?php echo $user['id']; ?>" class="movie-action-button delete-button" style="font-size: 0.8em; padding: 4px 8px;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="info-message">No users found.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
