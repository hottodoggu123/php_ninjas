<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;

// Check if user ID is provided
if (isset($_GET['id'])) {
    $user_id = (int) $_GET['id'];
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
    } else {
        // Get user details first (for confirmation)
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            // If confirmed, delete the user
            if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
                // Start transaction to delete user and related data
                $conn->begin_transaction();
                
                try {
                    // Delete user's booked seats
                    $deleteSeatsStmt = $conn->prepare("DELETE FROM booked_seats WHERE user_id = ?");
                    $deleteSeatsStmt->bind_param("i", $user_id);
                    $deleteSeatsStmt->execute();
                    
                    // Delete user's bookings
                    $deleteBookingsStmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
                    $deleteBookingsStmt->bind_param("i", $user_id);
                    $deleteBookingsStmt->execute();
                    
                    // Delete user
                    $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $deleteUserStmt->bind_param("i", $user_id);
                    $deleteUserStmt->execute();
                    
                    $conn->commit();
                    $success = true;
                    $message = "User \"" . e($user['username']) . "\" has been successfully deleted.";
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = "Error deleting user. Please try again.";
                }
            }
        } else {
            $message = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - CineXpress Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('manageUsers.php'); ?>
        
        <!-- Main Content -->
        <main class="admin-content">
            <div class="admin-header">
                <div class="admin-title">
                    <h1>Delete User</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Delete User</h2>
                    <a href="manageUsers.php" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Back to Users</a>
                </div>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageUsers.php" class="button primary-button">Back to Manage Users</a>
                            <a href="dashboard.php" class="button secondary-button">Go to Dashboard</a>
                        </div>
                    </div>
                <?php elseif ($message && !$success): ?>
                    <div class="error-message">
                        <?php echo $message; ?>
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageUsers.php" class="button primary-button">Back to Manage Users</a>
                        </div>
                    </div>
                <?php elseif (isset($user)): ?>
                    <div class="delete-confirmation" style="text-align: center; max-width: 600px; margin: 0 auto;">
                        <h3>Are you sure you want to delete this user?</h3>
                        
                        <div class="delete-user-details" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                            <div class="delete-user-info" style="text-align: center;">
                                <h3><?php echo e($user['username']); ?></h3>
                                
                                <div class="user-details" style="text-align: left; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                                    <p><strong>Username:</strong> <?php echo e($user['username']); ?></p>
                                    <p><strong>Display Name:</strong> <?php echo e($user['display_name'] ?? 'N/A'); ?></p>
                                    <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>
                                    <p><strong>Role:</strong> <?php echo e(ucfirst($user['role'])); ?></p>
                                    <p><strong>Created:</strong> <?php echo formatDate($user['created_at']); ?></p>
                                </div>
                                
                                <p style="color: #d32f2f; font-weight: bold; margin: 20px 0;">⚠️ This action cannot be undone. Deleting this user will also remove all their bookings and reservations.</p>
                                
                                <form method="POST">
                                    <input type="hidden" name="confirm" value="yes">
                                    <div class="button-container">
                                        <button type="submit" class="button" style="background-color: #d32f2f; color: white;">Yes, Delete User</button>
                                        <a href="manageUsers.php" class="button secondary-button">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        No user ID provided.
                        <div class="button-container" style="margin-top: 20px;">
                            <a href="manageUsers.php" class="button primary-button">Back to Manage Users</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
