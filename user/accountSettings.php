<?php
include '../includes/init.php';
include '../includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current user data
$userStmt = $conn->prepare("SELECT username, display_name, email, phone_number, preferred_genres FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile update
    if (isset($_POST['update_profile'])) {
        $displayName = $_POST['display_name'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phone_number'];
        $preferredGenres = isset($_POST['preferred_genres']) ? implode(',', $_POST['preferred_genres']) : null;

        // Update user information
        $updateStmt = $conn->prepare("UPDATE users SET display_name = ?, email = ?, phone_number = ?, preferred_genres = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $displayName, $email, $phoneNumber, $preferredGenres, $userId);

        if ($updateStmt->execute()) {
            $message = "Profile updated successfully!";
            
            // Update session variables
            $_SESSION['display_name'] = $displayName;
            
            // Refresh user data
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $conn->error;
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        $passwordStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $passwordStmt->bind_param("i", $userId);
        $passwordStmt->execute();
        $passwordResult = $passwordStmt->get_result()->fetch_assoc();
        
        if ($passwordResult['password'] === $currentPassword) {
            // Check if new passwords match
            if ($newPassword === $confirmPassword) {
                // Update password
                $updatePasswordStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePasswordStmt->bind_param("si", $newPassword, $userId);
                
                if ($updatePasswordStmt->execute()) {
                    $message = "Password changed successfully!";
                } else {
                    $error = "Error changing password: " . $conn->error;
                }
            } else {
                $error = "New passwords do not match!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    }
}

// Get all movie genres for the preferences section
$genreQuery = "SELECT DISTINCT genre FROM movies ORDER BY genre";
$genreResult = $conn->query($genreQuery);

$userGenres = [];
if (!empty($user['preferred_genres'])) {
    $userGenres = explode(',', $user['preferred_genres']);
}
?>

<div class="container">
    <h1 class="page-header">Account Settings</h1>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="profile-sidebar">
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user['display_name'] ?? $user['username']); ?></h3>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <div class="profile-nav">
                    <ul class="nav nav-pills nav-stacked">
                        <li class="nav-item"><a href="../index.php" class="nav-link">Back to Home</a></li>
                        <li class="nav-item"><a href="profile.php" class="nav-link">Your Profile</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <?php if (!empty($message)): ?>
                <div class="success-message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="profile-section">
                <h3>Personal Information</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small class="form-text text-muted">Username cannot be changed.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" class="form-control" value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Movie Preferences</label>
                        <div class="genre-checkboxes">
                            <?php while ($genre = $genreResult->fetch_assoc()): ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="preferred_genres[]" value="<?php echo $genre['genre']; ?>" 
                                            <?php echo in_array($genre['genre'], $userGenres) ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($genre['genre']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div class="profile-section">
                <h3>Change Password</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
