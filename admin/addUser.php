<?php
include '../includes/init.php';

// Check if user is logged in and has admin privileges
requireAdmin();

$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userData = [
        'username' => trim($_POST['username']),
        'display_name' => trim($_POST['display_name']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'],
        'role' => $_POST['role']
    ];
    
    // Basic validation
    if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
        $message = "Please fill in all required fields.";
    } elseif (strlen($userData['password']) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $userData['email']);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $message = "An account with this email already exists.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, display_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $userData['username'], $userData['display_name'], $userData['email'], $userData['password'], $userData['role']);
            
            if ($stmt->execute()) {
                $success = true;
                $message = "User \"" . e($userData['username']) . "\" has been successfully created.";
            } else {
                $message = "Error creating user. Please try again.";
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
    <title>Add User - Cinema Booking Admin</title>
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
                    <h1>Add User</h1>
                </div>
                <div class="admin-user">
                    <span>Welcome, <?php echo e($_SESSION['display_name'] ?? $_SESSION['username']); ?></span>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
            
            <div class="admin-section">
                <div class="admin-section-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Add New User</h2>
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
                            <input type="text" id="username" name="username" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="display_name">Display Name</label>
                            <input type="text" id="display_name" name="display_name" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" style="width: 100%; padding: 8px; box-sizing: border-box;">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="button-container">
                            <button type="submit" class="button primary-button">Create User</button>
                            <a href="manageUsers.php" class="button secondary-button">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
