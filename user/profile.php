<?php
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Conditional validation
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } else {
        try {
            // Check if username/email already exists for other users
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username or email already exists';
            } else {
                // Update basic info
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $email, $user_id]);
                
                // Handle password change
                if (!empty($current_password) || !empty($new_password)) {
                    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                        $error = 'All password fields are required to change password';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match';
                    } elseif (!password_verify($current_password, $user['password'])) {
                        $error = 'Current password is incorrect';
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                    }
                }
                
                if (empty($error)) {
                    $success = 'Profile updated successfully!';
                    $_SESSION['username'] = $username;
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get user statistics
$total_bookings = 0;
$total_spent = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_bookings, SUM(total_amount) as total_spent FROM bookings WHERE user_id = ? AND booking_status = 'confirmed'");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_bookings = $stats['total_bookings'] ?? 0;
    $total_spent = $stats['total_spent'] ?? 0;
} catch(PDOException $e) {
    // Handle error silently for stats
}

include '../includes/header.php';
?>

<div class="main-content">
    <h2>My Profile</h2>
    
    <!-- User Statistics -->
    <div class="dashboard-stats" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_bookings; ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">₱<?php echo number_format($total_spent, 2); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
            <div class="stat-label">Member Since</div>
        </div>
    </div>
    
    <div class="form-container">
        <h3>Update Profile</h3>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <hr style="margin: 2rem 0;">
            <h4>Change Password (Optional)</h4>
            
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            
            <button type="submit" class="btn">Update Profile</button>
            <a href="../index.php" class="btn btn-secondary">Back to Home</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>