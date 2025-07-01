<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();
?>

<div class="container">
    <h2>My Profile</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></p>
</div>

<?php include '../includes/footer.php'; ?>