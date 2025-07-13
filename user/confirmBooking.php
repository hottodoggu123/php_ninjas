<?php
include '../includes/init.php';
include '../includes/header.php';

requireAuth();

// Check if we have booking data in session (coming from payment page)
if (isset($_SESSION['booking_data']) && !$_POST) {
    // Show confirmation page with existing session data
    $bookingData = $_SESSION['booking_data'];
    
    // Get movie details
    $movie = $movieService->getMovieById($bookingData['movie_id']);
    if (!$movie) {
        echo "<div class='container'><p style='color:red;'>Movie not found.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    $seats_booked = count($bookingData['seats']);
    $price_per_seat = $bookingData['price'];
    $total_amount = $seats_booked * $price_per_seat;
    
    // Display confirmation page
    echo "
    <div class='container' style='margin-top: 30px;'>
        <!-- Page Header -->
        <div class='card'>
            <div class='card-header' style='background: #303030; color: white;'>
                <h1 style='color: white; margin: 0;'>Confirm Your Booking</h1>
            </div>
        </div>
        
        <div class='row'>
            <!-- Booking Details Section (Left) -->
            <div class='col-md-8'>
                <div class='card'>
                    <div class='card-header'>
                        <h3>Booking Summary</h3>
                    </div>
                    <div class='card-body'>
                        <h4 style='margin-top: 0; color: #303030; margin-bottom: 20px; text-align: center;'>" . e($movie['title']) . "</h4>
                        
                        <div style='background: #f8f8f8; padding: 25px; border-radius: 10px; margin-bottom: 20px;'>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Date:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatDate($bookingData['show_date']) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Time:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatTime($bookingData['show_time']) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Selected Seats:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . implode(", ", $bookingData['seats']) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Number of Seats:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>{$seats_booked}</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Price per Seat:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatCurrency($price_per_seat) . "</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style='background: #303030; color: white; padding: 20px; border-radius: 10px; text-align: center;'>
                            <p style='font-size: 1.4em; font-weight: 700; margin: 0;'>Total Amount: " . formatCurrency($total_amount) . "</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Movie Poster Section (Right) -->
            <div class='col-md-4'>
                <div class='card'>
                    <div class='card-header'>
                        <h3>Movie Poster</h3>
                    </div>
                    <div class='card-body' style='text-align: center;'>
                        <img src='../" . e($movie['poster_url']) . "' alt='" . e($movie['title']) . "' style='width: 100%; max-width: 300px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); transition: transform 0.3s ease;' onmouseover=\"this.style.transform='scale(1.02)'\" onmouseout=\"this.style.transform='scale(1)'\">
                    </div>
                </div>
            </div>
        </div>
                        
                        <div style='text-align: center; margin-top: 30px;'>
                            <div style='margin-bottom: 15px;'>
                                <a href='payment.php' style='background: #303030; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;' onmouseover=\"this.style.background='#404040'\" onmouseout=\"this.style.background='#303030'\">Proceed to Payment</a>
                            </div>
                            <div>
                                <a href='bookTicket.php?movie_id={$bookingData['movie_id']}' style='background: #6c757d; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;' onmouseover=\"this.style.background='#5a6268'\" onmouseout=\"this.style.background='#6c757d'\">Back to Seat Selection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
    
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $movie_id = (int) $_POST['movie_id'];
    $showtime_id = (int) $_POST['showtime_id'];
    $seats = $_POST['seats'] ?? [];

    if (empty($seats)) {
        echo "<div class='container'><p style='color:red;'>Please select at least one seat.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Get movie details using service
    $movie = $movieService->getMovieById($movie_id);
    if (!$movie) {
        echo "<div class='container'><p style='color:red;'>Movie not found.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    // Get showtime details
    $showtimeStmt = $conn->prepare("SELECT show_date, show_time FROM showtimes WHERE id = ?");
    $showtimeStmt->bind_param("i", $showtime_id);
    $showtimeStmt->execute();
    $showtimeResult = $showtimeStmt->get_result()->fetch_assoc();
    
    if (!$showtimeResult) {
        echo "<div class='container'><p style='color:red;'>Showtime not found.</p></div>";
        include '../includes/footer.php';
        exit;
    }
    
    $seats_booked = count($seats);
    $price_per_seat = $movie['price'] ?? 250.00;
    $total_amount = $seats_booked * $price_per_seat;
    
    // Store booking data in session for payment.php
    $_SESSION['booking_data'] = [
        'movie_id' => $movie_id,
        'showtime_id' => $showtime_id,
        'seats' => $seats,
        'movie_title' => $movie['title'],
        'price' => $price_per_seat,
        'show_date' => $showtimeResult['show_date'],
        'show_time' => $showtimeResult['show_time']
    ];
    
    // Display confirmation page with payment button
    echo "
    <div class='container' style='margin-top: 30px;'>
        <!-- Page Header -->
        <div class='card'>
            <div class='card-header' style='background: #303030; color: white;'>
                <h1 style='color: white; margin: 0;'>Confirm Your Booking</h1>
            </div>
        </div>
        
        <div class='row'>
            <!-- Booking Details Section (Left) -->
            <div class='col-md-8'>
                <div class='card'>
                    <div class='card-header'>
                        <h3>Booking Summary</h3>
                    </div>
                    <div class='card-body'>
                        <h4 style='margin-top: 0; color: #303030; margin-bottom: 20px; text-align: center;'>" . e($movie['title']) . "</h4>
                        
                        <div style='background: #f8f8f8; padding: 25px; border-radius: 10px; margin-bottom: 20px;'>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Date:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatDate($showtimeResult['show_date']) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Time:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatTime($showtimeResult['show_time']) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Selected Seats:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . implode(", ", $seats) . "</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Number of Seats:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>{$seats_booked}</p>
                                </div>
                                <div>
                                    <p style='margin-bottom: 10px;'><strong>Price per Seat:</strong></p>
                                    <p style='color: #666; margin-bottom: 20px;'>" . formatCurrency($price_per_seat) . "</p>
                                </div>
                            </div>
                        </div>
                        
                        <div style='background: #303030; color: white; padding: 20px; border-radius: 10px; text-align: center;'>
                            <p style='font-size: 1.4em; font-weight: 700; margin: 0;'>Total Amount: " . formatCurrency($total_amount) . "</p>
                        </div>
                        
                        <div style='text-align: center; margin-top: 30px;'>
                            <div style='margin-bottom: 15px;'>
                                <a href='payment.php' style='background: #303030; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;' onmouseover=\"this.style.background='#404040'\" onmouseout=\"this.style.background='#303030'\">Proceed to Payment</a>
                            </div>
                            <div>
                                <a href='bookTicket.php?movie_id={$movie_id}' style='background: #6c757d; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;' onmouseover=\"this.style.background='#5a6268'\" onmouseout=\"this.style.background='#6c757d'\">Back to Seat Selection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Movie Poster Section (Right) -->
            <div class='col-md-4'>
                <div class='card'>
                    <div class='card-header'>
                        <h3>Movie Poster</h3>
                    </div>
                    <div class='card-body' style='text-align: center;'>
                        <img src='../" . e($movie['poster_url']) . "' alt='" . e($movie['title']) . "' style='width: 100%; max-width: 300px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); transition: transform 0.3s ease;' onmouseover=\"this.style.transform='scale(1.02)'\" onmouseout=\"this.style.transform='scale(1)'\">
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";
} else {
    echo "<div class='container'><p style='color:red;'>Invalid request.</p></div>";
}

include '../includes/footer.php';
?>