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
$price = $bookingData['price'] ?? $result['price'];
$totalAmount = $price * count($bookingData['seats']);

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

<div class="container">
    <div class="page-header">
        <h2>Payment</h2>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo e($error); ?>
            <div style="margin-top: 15px;">
                <?php if (isset($bookingData) && isset($bookingData['movie_id']) && isset($bookingData['showtime_id'])): ?>
                    <a href="bookTicket.php?movie_id=<?php echo e($bookingData['movie_id']); ?>&showtime_id=<?php echo e($bookingData['showtime_id']); ?>" class="button">Return to Seat Selection</a>
                <?php else: ?>
                    <a href="../index.php" class="button">Return to Home</a>
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
                        <div class="payment-methods">
                            <h4>Select Payment Method</h4>
                            <div class="payment-method-options">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                    <label class="form-check-label payment-label" for="credit_card">
                                        <span class="payment-icon">💳</span>
                                        <span>Credit Card</span>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="debit_card" value="debit_card">
                                    <label class="form-check-label payment-label" for="debit_card">
                                        <span class="payment-icon">💳</span>
                                        <span>Debit Card</span>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label payment-label" for="paypal">
                                        <span class="payment-icon">🅿️</span>
                                        <span>PayPal</span>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mobile_payment" value="mobile_payment">
                                    <label class="form-check-label payment-label" for="mobile_payment">
                                        <span class="payment-icon">📱</span>
                                        <span>Mobile Payment</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="payment-form" id="credit-card-form">
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="XXXX XXXX XXXX XXXX">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="XXX">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="card_holder">Cardholder Name</label>
                                <input type="text" class="form-control" id="card_holder" placeholder="Name as appears on card">
                            </div>
                        </div>
                        
                        <div class="payment-form" id="paypal-form" style="display: none;">
                            <div class="paypal-info">
                                <p>You'll be redirected to PayPal to complete your payment securely.</p>
                            </div>
                        </div>
                        
                        <div class="payment-form" id="mobile-payment-form" style="display: none;">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" placeholder="Enter your mobile number">
                            </div>
                            <div class="mobile-payment-info">
                                <p>You'll receive a payment confirmation code on your mobile.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Pay Now</button>
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
                    <div class="booking-summary">
                        <div class="movie-title">
                            <h4><?php echo e($result['title']); ?></h4>
                        </div>
                        
                        <div class="booking-details">
                            <p><strong>Date:</strong> <?php echo formatDate($result['show_date']); ?></p>
                            <p><strong>Time:</strong> <?php echo formatTime($result['show_time']); ?></p>
                            <p><strong>Seats:</strong> <?php echo e(implode(', ', $bookingData['seats'])); ?></p>
                        </div>
                        
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Ticket Price:</span>
                                <span><?php echo formatCurrency($result['price']); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of Seats:</span>
                                <span><?php echo count($bookingData['seats']); ?></span>
                            </div>
                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span><?php echo formatCurrency($totalAmount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardForm = document.getElementById('credit-card-form');
        const paypalForm = document.getElementById('paypal-form');
        const mobilePaymentForm = document.getElementById('mobile-payment-form');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all forms
                creditCardForm.style.display = 'none';
                paypalForm.style.display = 'none';
                mobilePaymentForm.style.display = 'none';
                
                // Show the selected form
                if (this.value === 'credit_card' || this.value === 'debit_card') {
                    creditCardForm.style.display = 'block';
                } else if (this.value === 'paypal') {
                    paypalForm.style.display = 'block';
                } else if (this.value === 'mobile_payment') {
                    mobilePaymentForm.style.display = 'block';
                }
            });
        });
        
        // For demonstration purposes only - would be replaced with actual validation
        const paymentForm = document.getElementById('payment-form');
        paymentForm.addEventListener('submit', function(e) {
            // In a real application, we would validate the form and process the payment
            // For now, we'll just let the form submit
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cvv">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="XXX">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="card_holder">Cardholder Name</label>
                                <input type="text" class="form-control" id="card_holder" placeholder="Name as appears on card">
                            </div>
                        </div>
                        
                        <div class="payment-form" id="paypal-form" style="display: none;">
                            <div class="paypal-info">
                                <p>You'll be redirected to PayPal to complete your payment securely.</p>
                            </div>
                        </div>
                        
                        <div class="payment-form" id="mobile-payment-form" style="display: none;">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" placeholder="Enter your mobile number">
                            </div>
                            <div class="mobile-payment-info">
                                <p>You'll receive a payment confirmation code on your mobile.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Pay Now</button>
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
                    <div class="booking-summary">
                        <div class="movie-title">
                            <h4><?php echo htmlspecialchars($result['title']); ?></h4>
                        </div>
                        
                        <div class="booking-details">
                            <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($result['show_date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($result['show_time'])); ?></p>
                            <p><strong>Seats:</strong> <?php echo htmlspecialchars(implode(', ', $bookingData['seats'])); ?></p>
                        </div>
                        
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Ticket Price:</span>
                                <span>₱<?php echo number_format($result['price'], 2); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of Seats:</span>
                                <span><?php echo count($bookingData['seats']); ?></span>
                            </div>
                            <div class="price-row total">
                                <span>Total Amount:</span>
                                <span>₱<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardForm = document.getElementById('credit-card-form');
        const paypalForm = document.getElementById('paypal-form');
        const mobilePaymentForm = document.getElementById('mobile-payment-form');
        
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                // Hide all forms
                creditCardForm.style.display = 'none';
                paypalForm.style.display = 'none';
                mobilePaymentForm.style.display = 'none';
                
                // Show the selected form
                if (this.value === 'credit_card' || this.value === 'debit_card') {
                    creditCardForm.style.display = 'block';
                } else if (this.value === 'paypal') {
                    paypalForm.style.display = 'block';
                } else if (this.value === 'mobile_payment') {
                    mobilePaymentForm.style.display = 'block';
                }
            });
        });
        
        // For demonstration purposes only - would be replaced with actual validation
        const paymentForm = document.getElementById('payment-form');
        paymentForm.addEventListener('submit', function(e) {
            // In a real application, we would validate the form and process the payment
            // For now, we'll just let the form submit
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
