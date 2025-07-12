<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;
$user = null;

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header('Location: manageUsers.php');
    exit;
}

$user_id = (int) $_GET['id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: manageUsers.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'username' => trim($_POST['username']),
        'display_name' => trim($_POST['display_name']),
        'email' => trim($_POST['email']),
        'role' => $_POST['role']
    ];
    
    // Basic validation
    if (empty($userData['username']) || empty($userData['email'])) {
        $message = "Please fill in all required fields.";
    } else {
        // Check if email already exists for other users
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $userData['email'], $user_id);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $message = "An account with this email already exists.";
        } else {
            // Update user (without password)
            $updateStmt = $conn->prepare("UPDATE users SET username = ?, display_name = ?, email = ?, role = ? WHERE id = ?");
            $updateStmt->bind_param("ssssi", $userData['username'], $userData['display_name'], $userData['email'], $userData['role'], $user_id);
            
            if ($updateStmt->execute()) {
                $success = true;
                $message = "User \"" . e($userData['username']) . "\" has been successfully updated.";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $message = "Error updating user. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Cinema Booking Admin</title>
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
                    <h1>Edit User</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Edit User: <?php echo e($user['username']); ?></h2>
                    <a href="manageUsers.php" class="movie-action-button edit-button" style="font-size: 0.8em; padding: 4px 8px;">Back to Users</a>
                </div>
                
                <?php if ($message): ?>
                    <div class="<?php echo $success ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" class="movie-form">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required value="<?php echo e($user['username']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="display_name">Display Name</label>
                            <input type="text" id="display_name" name="display_name" value="<?php echo e($user['display_name']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required value="<?php echo e($user['email']); ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" style="width: 100%; padding: 8px; box-sizing: border-box;">
                                <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <p style="font-size: 0.9em; color: #666; margin: 10px 0;">
                                <strong>Note:</strong> Password cannot be changed from this form. Users must reset their password through the login page.
                            </p>
                        </div>
                        
                        <div class="button-container">
                            <button type="submit" class="button primary-button">Update User</button>
                            <a href="manageUsers.php" class="button secondary-button">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
