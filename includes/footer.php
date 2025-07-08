<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>PHP Ninjas Cinema</h3>
            <p>Your premier destination for the best cinematic experiences. Enjoy the latest blockbusters in ultimate comfort.</p>
        </div>
        
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                <li><a href="<?php echo $base_url; ?>user/movies.php">Movies</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo $base_url; ?>user/profile.php">My Profile</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>login.php">Login</a></li>
                    <li><a href="<?php echo $base_url; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Customer Service</h3>
            <ul class="footer-links">
                <li><a href="<?php echo $base_url; ?>about.php">About Us</a></li>
                <li><a href="<?php echo $base_url; ?>contact.php">Contact Us</a></li>
                <li><a href="<?php echo $base_url; ?>privacyPolicy.php">Privacy Policy</a></li>
                <li><a href="<?php echo $base_url; ?>user/advancedSearch.php">Advanced Search</a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> PHP Ninjas Cinema. All rights reserved.</p>
    </div>
</footer>

</body>
</html>