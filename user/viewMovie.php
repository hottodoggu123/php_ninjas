<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_GET['movie_id'])) {
    echo "<p>Movie not found.</p>";
    include '../includes/footer.php';
    exit;
}

$movie_id = (int) $_GET['movie_id'];

// Get movie details
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) {
    echo "<p>Movie not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Get showtimes for this movie
$showtimeStmt = $conn->prepare("SELECT id, show_date, show_time FROM showtimes WHERE movie_id = ? ORDER BY show_date, show_time");
$showtimeStmt->bind_param("i", $movie_id);
$showtimeStmt->execute();
$showtimes = $showtimeStmt->get_result();
?>

<div class="container">
  <div class="booking-wrapper">

    <!-- Left Panel - Movie Details -->
    <div class="booking-left">
      <h2>Movie Details</h2>
      
      <div class="movie-details">
        <div class="detail-row">
          <strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?>
        </div>
        
        <div class="detail-row">
          <strong>Duration:</strong> <?php echo htmlspecialchars($movie['duration']); ?> minutes
        </div>
        
        <div class="detail-row">
          <strong>Rating:</strong> <?php echo htmlspecialchars($movie['rating']); ?>
        </div>
        
        <div class="detail-row">
          <strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($movie['release_date'])); ?>
        </div>
        
        <div class="detail-row">
          <strong>Ticket Price:</strong> ₱<?php echo number_format($movie['price'], 2); ?>
        </div>
        
        <div class="detail-row">
          <strong>Status:</strong> 
          <span class="<?php echo $movie['status'] === 'now_showing' ? 'status-showing' : 'status-coming'; ?>">
            <?php echo $movie['status'] === 'now_showing' ? 'Now Showing' : 'Coming Soon'; ?>
          </span>
        </div>
      </div>

      <div class="movie-description">
        <h3>Description</h3>
        <p>
          <?php echo htmlspecialchars($movie['description']); ?>
        </p>
      </div>

      <?php if ($showtimes->num_rows > 0): ?>
        <div class="showtimes-section">
          <h3>Available Showtimes</h3>
          <div class="showtimes-grid">
            <?php while ($showtime = $showtimes->fetch_assoc()): ?>
              <div class="showtime-card">
                <div class="date">
                  <?php echo date('F j, Y', strtotime($showtime['show_date'])); ?>
                </div>
                <div class="time">
                  <?php echo date('h:i A', strtotime($showtime['show_time'])); ?>
                </div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="no-showtimes">
          <p>No showtimes available for this movie yet.</p>
        </div>
      <?php endif; ?>

      <div class="action-buttons">
        <?php if ($movie['status'] === 'now_showing' && $showtimes->num_rows > 0): ?>
          <a href="bookTicket.php?movie_id=<?php echo $movie['id']; ?>" class="button primary-button">Book Tickets</a>
        <?php endif; ?>
        <a href="../index.php" class="button secondary-button">Back to Home</a>
      </div>
    </div>

    <!-- Right Panel - Movie Poster -->
    <div class="booking-right">
      <div class="view-movie-poster-card">
        <div class="poster-section">
          <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?> Poster">
        </div>
        <div class="title-section">
          <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include '../includes/footer.php'; ?>
