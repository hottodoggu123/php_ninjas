<?php
/**
 * Helper functions for the cinema booking system
 */

/**
 * Get the correct path for assets based on current location
 */
function getAssetPath($asset) {
    $isSubfolder = strpos($_SERVER['PHP_SELF'], '/user/') !== false || 
                   strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
    return $isSubfolder ? '../' . $asset : $asset;
}

/**
 * Get the correct path for links based on current location
 */
function getLinkPath($path) {
    $isSubfolder = strpos($_SERVER['PHP_SELF'], '/user/') !== false || 
                   strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
    return $isSubfolder ? '../' . $path : $path;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . getLinkPath('login.php'));
        exit;
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . getLinkPath('login.php'));
        exit;
    }
}

/**
 * Sanitize output for HTML
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a secure payment reference
 */
function generatePaymentReference() {
    return 'PAY' . date('YmdHis') . rand(1000, 9999);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('F d, Y', strtotime($date));
}

/**
 * Format time for display
 */
function formatTime($time) {
    return date('h:i A', strtotime($time));
}
?>
