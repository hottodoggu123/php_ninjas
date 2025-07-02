<?php
include '../includes/init.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['movie_id'])) {
    echo "<p>Movie not found.</p>";
    include '../includes/footer.php';
    exit;
}

$movie_id = (int) $_GET['movie_id'];

// Get movie details
$stmt = $conn->prepare("SELECT title, poster_url FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

// Get available showtimes
$showtimeStmt = $conn->prepare("SELECT id, show_date, show_time FROM showtimes WHERE movie_id = ?");
$showtimeStmt->bind_param("i", $movie_id);
$showtimeStmt->execute();
$showtimes = $showtimeStmt->get_result();
?>

<div class="container">
  <div class="booking-wrapper">

    <!-- Left Panel -->
    <div class="booking-left">
      <h2>Book Ticket: <?php echo htmlspecialchars($movie['title']); ?></h2>

      <div class="booking-content-center">
        <div class="booking-showtime-section">
          <form method="GET" action="">
              <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">

              <label for="showtime">Choose Showtime:</label>
              <select name="showtime_id" id="showtime" required onchange="this.form.submit()">
                  <option value="">-- Select Showtime --</option>
                  <?php 
                  $showtimeStmt->execute();
                  $showtimes = $showtimeStmt->get_result();
                  while ($row = $showtimes->fetch_assoc()): ?>
                      <option value="<?php echo $row['id']; ?>"
                          <?php if (isset($_GET['showtime_id']) && $_GET['showtime_id'] == $row['id']) echo 'selected'; ?>>
                          <?php echo $row['show_date'] . ' at ' . date('h:i A', strtotime($row['show_time'])); ?>
                      </option>
                  <?php endwhile; ?>
              </select>
          </form>
        </div>

        <?php
        if (isset($_GET['showtime_id'])):
            $showtime_id = (int) $_GET['showtime_id'];

            // Get booked seats
            $bookedStmt = $conn->prepare("SELECT seat_number FROM booked_seats WHERE showtime_id = ?");
            $bookedStmt->bind_param("i", $showtime_id);
            $bookedStmt->execute();
            $bookedResult = $bookedStmt->get_result();

            $bookedSeats = [];
            while ($row = $bookedResult->fetch_assoc()) {
                $bookedSeats[] = $row['seat_number'];
            }

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
        endif;
        ?>
      </div>
    </div>

    <!-- Right Panel -->
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

<?php include '../includes/footer.php'; ?>