<?php
include '../includes/init.php';
include '../includes/header.php';

// Redirect if not logged in or no booking data in session
if (!isLoggedIn() || !isset($_SESSION['booking_data'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$bookingData = $_SESSION['booking_data'];

// Get movie and showtime details
$stmt = $conn->prepare("
    SELECT m.title, m.price, s.show_date, s.show_time
    FROM movies m
    JOIN showtimes s ON m.id = s.movie_id
    WHERE s.id = ?
");
$stmt->bind_param("i", $bookingData['showtime_id']);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// Calculate total
$seatsBooked = count($bookingData['seats']);
$price = $bookingData['price'] ?? $result['price'];
$totalAmount = $price * $seatsBooked;

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'];
    
    try {
        // Use the booking service to create booking with transaction safety
        $bookingId = $bookingService->createBooking(
            $userId,
            $bookingData['movie_id'],
            $bookingData['showtime_id'],
            $bookingData['seats'],
            $totalAmount,
            $paymentMethod
        );
        
        // Clear booking data from session
        unset($_SESSION['booking_data']);
        
        // Set success message and redirect to booking confirmation page
        $_SESSION['message'] = "Payment completed successfully! Your booking has been confirmed.";
        $_SESSION['booking_id'] = $bookingId;
        
        header("Location: bookingConfirmation.php");
        exit;
        
    } catch (Exception $e) {
        $error = "Payment failed: " . $e->getMessage();
        // Make sure the booking data is still available for the error message links
    }
}
?>

<div class="container" style="margin-top: 30px;">
    <!-- Page Header -->
    <div class="card">
        <div class="card-header" style="background: #303030; color: white;">
            <h1 style="color: white; margin: 0;">Payment</h1>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <?php echo e($error); ?>
            <div style="margin-top: 15px;">
                <?php if (isset($bookingData) && isset($bookingData['movie_id']) && isset($bookingData['showtime_id'])): ?>
                    <a href="bookTicket.php?movie_id=<?php echo e($bookingData['movie_id']); ?>&showtime_id=<?php echo e($bookingData['showtime_id']); ?>" style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; text-decoration: none; display: inline-block;">Return to Seat Selection</a>
                <?php else: ?>
                    <a href="../index.php" style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 5px; text-decoration: none; display: inline-block;">Return to Home</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Payment Details</h3>
                </div>
                <div class="card-body">
                    <form method="post" id="payment-form">
                        <div style="margin-bottom: 30px;">
                            <h4 style="margin-bottom: 20px; color: #303030;">Select Payment Method</h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; background: #f8f8f8; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#303030'" onmouseout="this.style.borderColor='#ddd'">
                                    <input type="radio" name="payment_method" value="credit_card" checked style="margin-right: 10px;">
                                    <span style="font-size: 1.5em; margin-right: 10px;">💳</span>
                                    <span style="font-weight: 600;">Credit Card</span>
                                </label>
                                
                                <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; background: #f8f8f8; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#303030'" onmouseout="this.style.borderColor='#ddd'">
                                    <input type="radio" name="payment_method" value="debit_card" style="margin-right: 10px;">
                                    <span style="font-size: 1.5em; margin-right: 10px;">💳</span>
                                    <span style="font-weight: 600;">Debit Card</span>
                                </label>
                                
                                <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; background: #f8f8f8; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#303030'" onmouseout="this.style.borderColor='#ddd'">
                                    <input type="radio" name="payment_method" value="paypal" style="margin-right: 10px;">
                                    <span style="font-size: 1.5em; margin-right: 10px;">🅿️</span>
                                    <span style="font-weight: 600;">PayPal</span>
                                </label>
                                
                                <label style="display: flex; align-items: center; padding: 15px; border: 2px solid #ddd; border-radius: 10px; cursor: pointer; background: #f8f8f8; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#303030'" onmouseout="this.style.borderColor='#ddd'">
                                    <input type="radio" name="payment_method" value="mobile_payment" style="margin-right: 10px;">
                                    <span style="font-size: 1.5em; margin-right: 10px;">📱</span>
                                    <span style="font-weight: 600;">Mobile Payment</span>
                                </label>
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <h4 style="margin-bottom: 20px; color: #303030;">Payment Information</h4>
                            <div style="background: #f8f8f8; padding: 20px; border-radius: 10px;">
                                <div style="margin-bottom: 15px;">
                                    <label for="card_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Card Number</label>
                                    <input type="text" id="card_number" placeholder="XXXX XXXX XXXX XXXX" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <label for="expiry_date" style="display: block; margin-bottom: 5px; font-weight: 600;">Expiry Date</label>
                                        <input type="text" id="expiry_date" placeholder="MM/YY" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    </div>
                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                        <label for="cvv" style="display: block; margin-bottom: 5px; font-weight: 600;">CVV</label>
                                        <input type="text" id="cvv" placeholder="XXX" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    </div>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="card_holder" style="display: block; margin-bottom: 5px; font-weight: 600;">Cardholder Name</label>
                                    <input type="text" id="card_holder" placeholder="Name as appears on card" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                </div>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 30px;">
                            <div style="margin-bottom: 15px;">
                                <button type="submit" style="background: #303030; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; width: 200px;" onmouseover="this.style.background='#404040'" onmouseout="this.style.background='#303030'">Complete Payment</button>
                            </div>
                            <div>
                                <a href="confirmBooking.php" style="background: #6c757d; color: white; padding: 12px 30px; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-block; width: 200px;" onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">Back to Confirmation</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div style="padding: 10px;">
                        <h4 style="color: #303030; margin-bottom: 15px; text-align: center;"><?php echo e($bookingData['movie_title']); ?></h4>
                        
                        <div style="background: #f8f8f8; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span><strong>Date:</strong></span>
                                <span><?php echo formatDate($bookingData['show_date']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span><strong>Time:</strong></span>
                                <span><?php echo formatTime($bookingData['show_time']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span><strong>Seats:</strong></span>
                                <span><?php echo implode(", ", $bookingData['seats']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span><strong>Tickets:</strong></span>
                                <span><?php echo $seatsBooked; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span><strong>Price per Ticket:</strong></span>
                                <span><?php echo formatCurrency($bookingData['price']); ?></span>
                            </div>
                        </div>
                        
                        <div style="background: #303030; color: white; padding: 15px; border-radius: 8px; text-align: center;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 1.2em; font-weight: 700;">Total Amount:</span>
                                <span style="font-size: 1.3em; font-weight: 700;"><?php echo formatCurrency($totalAmount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
