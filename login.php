<?php
require_once 'includes/db.php';

$error = '';

// Conditional statements
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Database manipulation
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Conditional redirect
                if ($user['role'] == 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="form-container">
    <h2>Login to SM Cinema</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username or Email:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Login</button>
    </form>
    
    <p style="margin-top: 1rem; text-align: center;">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>