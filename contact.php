<?php
include 'includes/init.php';
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Contact Us</h1>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="contact-info">
                <h2>Get In Touch</h2>
                <p>Have questions about showtimes, ticket bookings, or anything else? We're here to help! Contact us using any of the methods below or fill out the contact form.</p>
                
                <div class="contact-method">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h3>Phone</h3>
                        <p>(555) 123-4567</p>
                        <p class="text-muted">Monday to Friday, 9am to 6pm</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-details">
                        <h3>Email</h3>
                        <p><a href="mailto:info@cinexpress.com">info@cinexpress.com</a></p>
                        <p class="text-muted">We'll respond as soon as possible</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">🏢</div>
                    <div class="contact-details">
                        <h3>Address</h3>
                        <p>123 Cinema Boulevard<br>Manila, Philippines 1000</p>
                        <p class="text-muted">Come visit us!</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">🕒</div>
                    <div class="contact-details">
                        <h3>Box Office Hours</h3>
                        <p>Monday to Friday: 10am - 10pm<br>Saturday & Sunday: 9am - 11pm</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="contact-form-container">
                <h2>Send Us a Message</h2>
                
                <?php
                $message = '';
                $error = '';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $subject = trim($_POST['subject']);
                    $message_text = trim($_POST['message']);
                    
                    // Basic validation
                    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
                        $error = "All fields are required";
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = "Please enter a valid email address";
                    } else {
                        // In a real application, you'd send an email or save to database
                        // For this demo, we'll just show a success message
                        $message = "Thank you for your message! We will get back to you soon.";
                        
                        // Reset the form fields
                        $name = $email = $subject = $message_text = '';
                    }
                }
                ?>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" class="contact-form">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select class="form-control" id="subject" name="subject" required>
                            <option value="" disabled selected>Select a subject</option>
                            <option value="General Inquiry" <?php echo (isset($subject) && $subject === 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="Ticket Booking" <?php echo (isset($subject) && $subject === 'Ticket Booking') ? 'selected' : ''; ?>>Ticket Booking</option>
                            <option value="Technical Support" <?php echo (isset($subject) && $subject === 'Technical Support') ? 'selected' : ''; ?>>Technical Support</option>
                            <option value="Feedback" <?php echo (isset($subject) && $subject === 'Feedback') ? 'selected' : ''; ?>>Feedback</option>
                            <option value="Other" <?php echo (isset($subject) && $subject === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message_text) ? htmlspecialchars($message_text) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>
