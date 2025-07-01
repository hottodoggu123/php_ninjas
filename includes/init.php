<!-- just to initialize the session and avoid errors -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';