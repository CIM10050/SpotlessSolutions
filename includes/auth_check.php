<?php
// includes/auth_check.php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Optional: Page-level access control
// Example usage in admin-only pages:
if (isset($requireAdmin) && $requireAdmin === true) {
    if ($_SESSION['user_role'] !== 'admin') {
        // Redirect normal user trying to access admin page
        header("Location: /index.php");
        exit();
    }
}
?>
