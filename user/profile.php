<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch email and user details
$stmt = $conn->prepare("SELECT username, display_name, email, phone_number, preferred_genres FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
$updateMessage = '';
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newDisplayName = trim($_POST['display_name']);
    $newEmail = trim($_POST['email']);
    $newPhoneNumber = trim($_POST['phone_number']);
    $newPreferredGenres = trim($_POST['preferred_genres']);
    
    // Basic validation
    if (empty($newDisplayName) || empty($newEmail)) {
        $updateMessage = "Display name and email are required.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $updateMessage = "Please enter a valid email address.";
    } else {
        // Check if email already exists for other users
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $newEmail, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $updateMessage = "This email address is already in use by another account.";
        } else {
            // Update user profile
            $updateStmt = $conn->prepare("UPDATE users SET display_name = ?, email = ?, phone_number = ?, preferred_genres = ? WHERE id = ?");
            $updateStmt->bind_param("ssssi", $newDisplayName, $newEmail, $newPhoneNumber, $newPreferredGenres, $userId);
            
            if ($updateStmt->execute()) {
                $_SESSION['display_name'] = $newDisplayName;
                $updateMessage = "Profile updated successfully!";
                $updateSuccess = true;
                // Refresh the user data
                $result['display_name'] = $newDisplayName;
                $result['email'] = $newEmail;
                $result['phone_number'] = $newPhoneNumber;
                $result['preferred_genres'] = $newPreferredGenres;
            } else {
                $updateMessage = "Error updating profile. Please try again.";
            }
            $updateStmt->close();
        }
        $checkStmt->close();
    }
}

// Fetch upcoming bookings with seat numbers
$upcomingBookingStmt = $conn->prepare("
    SELECT 
        b.id, 
        m.title, 
        m.poster_url,
        b.total_amount,
        s.show_date, 
        s.show_time, 
        b.booking_status,
        GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
    FROM bookings b
    JOIN booked_seats bs ON b.id = bs.booking_id
    JOIN showtimes s ON bs.showtime_id = s.id
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = ? AND s.show_date >= CURDATE()
    GROUP BY b.id
    ORDER BY s.show_date ASC, s.show_time ASC
");
$upcomingBookingStmt->bind_param("i", $userId);
$upcomingBookingStmt->execute();
$upcomingBookings = $upcomingBookingStmt->get_result();

// Fetch past bookings with seat numbers
$pastBookingStmt = $conn->prepare("
    SELECT 
        b.id, 
        m.title, 
        m.poster_url,
        b.total_amount,
        s.show_date, 
        s.show_time, 
        b.booking_status,
        GROUP_CONCAT(bs.seat_number ORDER BY bs.seat_number ASC) AS seats
    FROM bookings b
    JOIN booked_seats bs ON b.id = bs.booking_id
    JOIN showtimes s ON bs.showtime_id = s.id
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = ? AND s.show_date < CURDATE()
    GROUP BY b.id
    ORDER BY s.show_date DESC, s.show_time DESC
");
$pastBookingStmt->bind_param("i", $userId);
$pastBookingStmt->execute();
$pastBookings = $pastBookingStmt->get_result();
?>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="info-message">
            <?php
            echo htmlspecialchars($_SESSION['message']);
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h2>My Profile</h2>
    </div>

    <div class="profile-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Account Information</h3>
            <button onclick="toggleEditForm()" id="editToggleBtn" class="button primary-button" style="margin: 0;">Edit Profile</button>
        </div>
        
        <?php if ($updateMessage): ?>
            <div class="<?php echo $updateSuccess ? 'success-message' : 'error-message'; ?>" style="margin-bottom: 20px; padding: 10px; border-radius: 5px; <?php echo $updateSuccess ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                <?php echo htmlspecialchars($updateMessage); ?>
            </div>
        <?php endif; ?>
        
        <!-- View Mode -->
        <div id="viewMode" class="booking-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($result['username']); ?></p>
            <p><strong>Display Name:</strong> <?php echo htmlspecialchars($result['display_name'] ?? 'Not set'); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($result['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($result['phone_number'] ?? 'Not set'); ?></p>
            <p><strong>Preferred Genres:</strong> <?php echo htmlspecialchars($result['preferred_genres'] ?? 'Not set'); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></p>
        </div>
        
        <!-- Edit Mode -->
        <div id="editMode" style="display: none;">
            <form method="POST" action="">
                <div style="margin-bottom: 15px;">
                    <label for="display_name" style="display: block; margin-bottom: 5px; font-weight: 600;">Display Name *</label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo htmlspecialchars($result['display_name'] ?? ''); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($result['email']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="phone_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($result['phone_number'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="preferred_genres" style="display: block; margin-bottom: 5px; font-weight: 600;">Preferred Genres</label>
                    <input type="text" id="preferred_genres" name="preferred_genres" value="<?php echo htmlspecialchars($result['preferred_genres'] ?? ''); ?>" placeholder="e.g., Action, Comedy, Drama" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_profile" class="button primary-button">Save Changes</button>
                    <button type="button" onclick="toggleEditForm()" class="button secondary-button">Cancel</button>
                </div>
            </form>
        </div>
        
        <script>
        function toggleEditForm() {
            var viewMode = document.getElementById('viewMode');
            var editMode = document.getElementById('editMode');
            var toggleBtn = document.getElementById('editToggleBtn');
            
            if (editMode.style.display === 'none') {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                toggleBtn.textContent = 'Cancel Edit';
                toggleBtn.className = 'button cancel-button';
                toggleBtn.style.margin = '0';
            } else {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                toggleBtn.textContent = 'Edit Profile';
                toggleBtn.className = 'button primary-button';
                toggleBtn.style.margin = '0';
            }
        }
        </script>
    </div>

    <div class="profile-section">
        <h3>Upcoming Bookings</h3>
        
        <?php if ($upcomingBookings->num_rows > 0): ?>
            <div class="bookings-container">
                <?php while ($row = $upcomingBookings->fetch_assoc()): ?>
                    <?php renderBookingCard($row, true, false, false); ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="info-message">
                No upcoming bookings found. <a href="../index.php" style="color: #1976d2; text-decoration: underline;">Browse movies</a> to make a new booking!
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h3>Past Bookings</h3>
        
        <?php if ($pastBookings->num_rows > 0): ?>
            <div class="bookings-container">
                <?php while ($row = $pastBookings->fetch_assoc()): ?>
                    <?php renderBookingCard($row, false, true, false); ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="info-message">
                No past bookings found.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>