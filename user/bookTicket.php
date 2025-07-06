<?php
include '../includes/init.php';
include '../includes/header.php';

requireAuth();

if (!isset($_GET['movie_id'])) {
    echo "<p>Movie not found.</p>";
    include '../includes/footer.php';
    exit;
}

$movie_id = (int) $_GET['movie_id'];

// Get movie details using service
$movie = $movieService->getMovieById($movie_id);
if (!$movie) {
    echo "<p>Movie not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Get available showtimes using service
$showtimes = $bookingService->getShowtimes($movie_id);
?>

<div class="container">
  <div class="booking-wrapper">

    <!-- Left Panel -->
    <div class="booking-left">
      <h2>Book Ticket: <?php echo e($movie['title']); ?></h2>

      <div class="booking-content-center">
        <div class="booking-showtime-section">
          <form method="GET" action="">
              <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">

              <label for="showtime">Choose Showtime:</label>
              <select name="showtime_id" id="showtime" required onchange="this.form.submit()">
                  <option value="">-- Select Showtime --</option>
                  <?php if ($showtimes): ?>
                      <?php while ($row = $showtimes->fetch_assoc()): ?>
                          <option value="<?php echo $row['id']; ?>"
                              <?php if (isset($_GET['showtime_id']) && $_GET['showtime_id'] == $row['id']) echo 'selected'; ?>>
                              <?php echo formatDate($row['show_date']) . ' at ' . formatTime($row['show_time']); ?>
                          </option>
                      <?php endwhile; ?>
                  <?php endif; ?>
              </select>
          </form>
        </div>

        <?php
        if (isset($_GET['showtime_id'])):
            $showtime_id = (int) $_GET['showtime_id'];

            // Get booked seats using service (with race condition protection)
            $bookedSeats = $bookingService->getBookedSeats($showtime_id);
            
            if ($bookedSeats !== false) {
                // Seat selection form
                echo "<div class='seat-selection-section'>";
                echo "<form method='POST' action='confirmBooking.php' id='seatForm'>";
                echo "<input type='hidden' name='movie_id' value='{$movie_id}'>";
                echo "<input type='hidden' name='showtime_id' value='{$showtime_id}'>";
                echo "<label>Select Your Seat(s):</label>";
                echo "<div class='seat-grid-container'>";
                echo "<div class='screen'>SCREEN</div>";
                echo "<div class='seat-grid'>";

                for ($i = 1; $i <= 40; $i++) {
                    $seat = "S" . $i;
                    $isBooked = in_array($seat, $bookedSeats);
                    $disabled = $isBooked ? "disabled" : "";
                    $extraClass = $isBooked ? "booked" : "";

                    echo "
                        <label class='seat-box $extraClass'>
                            <input type='checkbox' name='seats[]' value='{$seat}' $disabled style='display: none;'>
                            {$seat}
                        </label>
                    ";
                }

                echo "</div>";
                echo "<button type='submit' class='button' style='margin-top: 20px;'>Confirm Booking</button>";
                echo "</form>";
                echo "</div>";
            } else {
                echo "<div class='error-message'>Error loading seat information. Please try again.</div>";
            }
        endif;
        ?>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="booking-right">
      <div class="view-movie-poster-card">
        <div class="poster-section">
          <img src="../<?php echo e($movie['poster_url']); ?>" alt="<?php echo e($movie['title']); ?> Poster">
        </div>
        <div class="title-section">
          <h3><?php echo e($movie['title']); ?></h3>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  const seatBoxes = document.querySelectorAll('.seat-box');

  seatBoxes.forEach(box => {
    const input = box.querySelector('input[type="checkbox"]');
    if (!box.classList.contains('booked')) {
      box.addEventListener('click', () => {
        input.checked = !input.checked;
        box.classList.toggle('selected', input.checked);
      });
    }
  });
</script>

<div class="clear-fix"></div>

<?php include '../includes/footer.php'; ?>