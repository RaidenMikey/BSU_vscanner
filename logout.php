<?php
session_start();

$redirect = 'login.php';
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $redirect = 'adminlogin.php';
}

// Destroy all session data
session_unset();
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to appropriate login page
header('Location: ' . $redirect);
exit();
?>
