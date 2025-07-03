<?php
include 'includes/init.php';
include 'includes/header.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $display_name = trim($_POST['display_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $message = "An account with this email already exists.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Insert user with plain text password (not recommended for production)
        $stmt = $conn->prepare("INSERT INTO users (username, display_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $display_name, $email, $password);

        if ($stmt->execute()) {
            // Auto-login the user
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['display_name'] = $display_name;
            $_SESSION['role'] = 'user';

            // Redirect to index
            header("Location: index.php");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<div class="container">
    <div class="page-header">
        <h2>Create Your Account</h2>
    </div>
    
    <?php if ($message): ?>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="display_name">Display Name:</label>
        <input type="text" id="display_name" name="display_name" required>

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Create Account</button>
        
        <p style="text-align: center; margin-top: 20px; color: #666;">
            Already have an account? <a href="login.php" style="color: #303030; text-decoration: underline;">Login here</a>
        </p>
    </form>
</div>

<?php include 'includes/footer.php'; ?>