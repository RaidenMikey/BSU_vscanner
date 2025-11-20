<?php
/**
 * Session Timeout Helper
 * 
 * This script handles session timeout logic. It should be included in all protected pages
 * after session_start().
 */

// Set timeout duration (5 hours = 18000 seconds)
$timeout_duration = 18000;

// Check if last activity is set
if (isset($_SESSION['last_activity'])) {
    // Calculate time difference
    $duration = time() - $_SESSION['last_activity'];
    
    // If timeout expired
    if ($duration > $timeout_duration) {
        // Determine redirect URL based on role
        $redirect = '../auth/login.php';
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $redirect = '../auth/admin_login.php';
        }
        
        // Unset all session variables
        session_unset();
        
        // Destroy the session
        session_destroy();
        
        // Redirect with timeout parameter
        header("Location: $redirect?timeout=1");
        exit();
    }
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();
?>
