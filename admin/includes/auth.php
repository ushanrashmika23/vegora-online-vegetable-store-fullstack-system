<?php
// admin/includes/auth.php

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is authenticated and holds the 'admin' role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // If not authenticated or not an admin, redirect them out
    $_SESSION['auth_error'] = "Access Denied: You must be an administrator to view this area.";
    header('Location: ../login.php');
    exit;
}
?>
