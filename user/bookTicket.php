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
    <!-- Page Header with proper background -->
    <div class="card">
        <div class="card-header" style="background: #303030; color: white;">
            <h1 style="color: white; margin: 0;">Book Ticket: <?php echo e($movie['title']); ?></h1>
        </div>
    </div>
    
    <!-- Movie Details and Showtime Selection -->
    <div class="card">
        <div class="card-header">
            <h3>Movie Details & Showtime Selection</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Movie Details on the Left -->
                <div class="col-md-8">
                    <div style="padding-right: 30px;">
                        <h3 style="color: #303030; margin-bottom: 20px;"><?php echo e($movie['title']); ?></h3>
                        <div style="margin-bottom: 15px;">
                            <p style="margin-bottom: 8px;"><strong>Genre:</strong> <?php echo e($movie['genre']); ?></p>
                            <p style="margin-bottom: 8px;"><strong>Duration:</strong> <?php echo e($movie['duration']); ?> minutes</p>
                            <p style="margin-bottom: 8px;"><strong>Price:</strong> <?php echo formatCurrency($movie['price']); ?></p>
                            <p style="margin-bottom: 15px;"><strong>Description:</strong></p>
                            <p style="color: #666; line-height: 1.6;"><?php echo e($movie['description']); ?></p>
                        </div>
                        
                        <!-- Showtime Selection -->
                        <?php if (!isset($_GET['showtime_id'])): ?>
                            <h4 style="color: #303030; margin: 30px 0 20px 0;">Select Showtime</h4>
                            <?php if ($showtimes->num_rows > 0): ?>
                                <form method="get" action="">
                                    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                                    <?php while ($showtime = $showtimes->fetch_assoc()): ?>
                                        <div style="margin-bottom: 12px;">
                                            <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; background: #f8f8f8; transition: all 0.3s ease; <?php echo (isset($_GET['showtime_id']) && $_GET['showtime_id'] == $showtime['id']) ? 'border-color: #303030; background: #f0f0f0;' : ''; ?>" onmouseover="this.style.borderColor='#303030'; this.style.background='#f0f0f0';" onmouseout="this.style.borderColor='#ddd'; this.style.background='#f8f8f8';">
                                                <input type="radio" name="showtime_id" value="<?php echo $showtime['id']; ?>" 
                                                       <?php echo (isset($_GET['showtime_id']) && $_GET['showtime_id'] == $showtime['id']) ? 'checked' : ''; ?>
                                                       style="margin-right: 15px; transform: scale(1.2);">
                                                <div>
                                                    <strong style="font-size: 1.1em; color: #303030;"><?php echo formatDate($showtime['show_date']); ?></strong>
                                                    <span style="margin-left: 15px; color: #666;">at <?php echo formatTime($showtime['show_time']); ?></span>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endwhile; ?>
                                    <div style="margin-top: 25px;">
                                        <button type="submit" style="background: #303030; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='#404040'" onmouseout="this.style.background='#303030'">Select Showtime</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p style="color: #dc3545;">No showtimes available for this movie.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Movie Poster on the Right -->
                <div class="col-md-4">
                    <div style="text-align: center;">
                        <img src="../<?php echo e($movie['poster_url']); ?>" 
                             alt="<?php echo e($movie['title']); ?>" 
                             style="width: 100%; max-width: 300px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); transition: transform 0.3s ease;" 
                             onmouseover="this.style.transform='scale(1.02)'" 
                             onmouseout="this.style.transform='scale(1)'">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Selection (only shown when showtime is selected) -->
    <?php if (isset($_GET['showtime_id'])): ?>
        <?php 
        $showtime_id = (int) $_GET['showtime_id'];
        $bookedSeats = $bookingService->getBookedSeats($showtime_id);
        ?>
        
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <h3>Select Your Seats</h3>
            </div>
            <div class="card-body">
                <?php if ($bookedSeats !== false): ?>
                    <div style="text-align: center;">
                        <form method="POST" action="confirmBooking.php">
                            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                            <input type="hidden" name="showtime_id" value="<?php echo $showtime_id; ?>">
                            
                            <!-- Seat Legend -->
                            <div style="display: flex; justify-content: center; gap: 30px; margin-bottom: 30px; background: #f8f8f8; padding: 15px; border-radius: 10px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 20px; height: 20px; background: #e9ecef; border: 1px solid #ccc; border-radius: 3px;"></div>
                                    <span>Available</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 20px; height: 20px; background: #303030; border: 1px solid #303030; border-radius: 3px;"></div>
                                    <span>Selected</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 20px; height: 20px; background: #dc3545; border: 1px solid #dc3545; border-radius: 3px;"></div>
                                    <span>Booked</span>
                                </div>
                            </div>
                            
                            <!-- Screen -->
                            <div style="background: #666; color: white; text-align: center; padding: 15px; margin-bottom: 30px; border-radius: 10px; font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                                SCREEN
                            </div>
                            
                            <!-- Seat Grid with better centering -->
                            <div style="display: flex; justify-content: center; margin-bottom: 30px;">
                                <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px; background: #f8f8f8; padding: 25px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <?php
                                    $rows = ['A', 'B', 'C', 'D', 'E'];
                                    $seatsPerRow = 8;
                                    
                                    foreach ($rows as $row) {
                                        for ($i = 1; $i <= $seatsPerRow; $i++) {
                                            $seatId = $row . $i;
                                            $isBooked = in_array($seatId, $bookedSeats);
                                            
                                            if ($isBooked) {
                                                echo '<div style="width: 45px; height: 45px; border: 2px solid #dc3545; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #dc3545; color: white; font-weight: bold; font-size: 12px; cursor: not-allowed; opacity: 0.7;">' . $seatId . '</div>';
                                            } else {
                                                echo '<label style="width: 45px; height: 45px; border: 2px solid #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #e9ecef; cursor: pointer; font-weight: bold; font-size: 12px; transition: all 0.3s ease;" class="seat-label" data-seat="' . $seatId . '">';
                                                echo '<input type="checkbox" name="seats[]" value="' . $seatId . '" style="display: none;" onchange="toggleSeat(this)">';
                                                echo $seatId;
                                                echo '</label>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <style>
                            .seat-label:hover {
                                border-color: #303030 !important;
                                background: #f0f0f0 !important;
                            }
                            .seat-selected {
                                background: #303030 !important;
                                border-color: #303030 !important;
                                color: white !important;
                            }
                            </style>
                            
                            <script>
                            function toggleSeat(checkbox) {
                                if (checkbox.checked) {
                                    checkbox.parentElement.classList.add('seat-selected');
                                } else {
                                    checkbox.parentElement.classList.remove('seat-selected');
                                }
                                updateSeatCount();
                            }
                            
                            function updateSeatCount() {
                                const selectedSeats = document.querySelectorAll('input[name="seats[]"]:checked').length;
                                const seatCountElement = document.getElementById('seat-count');
                                if (seatCountElement) {
                                    seatCountElement.textContent = selectedSeats;
                                }
                            }
                            </script>
                            
                            <div style="margin-bottom: 20px;">
                                <span style="font-size: 1.1em; font-weight: 600; color: #303030;">Selected Seats: <span id="seat-count">0</span></span>
                            </div>
                            
                            <div style="text-align: center;">
                                <div style="margin-bottom: 15px;">
                                    <button type="submit" style="background: #303030; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; width: 200px;" onmouseover="this.style.background='#404040'" onmouseout="this.style.background='#303030'">Confirm Seat Selection</button>
                                </div>
                                <div>
                                    <a href="?movie_id=<?php echo $movie_id; ?>" style="background: #6c757d; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">Change Showtime</a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <p style="color: #dc3545;">Error loading seat information. Please try again.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
