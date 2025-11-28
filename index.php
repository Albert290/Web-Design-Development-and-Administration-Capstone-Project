<?php
// Include config file
require_once 'config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

// User is logged in, redirect to appropriate dashboard based on role
if (hasRole('lecturer')) {
    header('Location: views/dashboard_lecturer.php');
    exit();
} elseif (hasRole('student')) {
    header('Location: views/dashboard_student.php');
    exit();
} else {
    // Unknown role, logout and redirect to login
    session_destroy();
    header('Location: login.php');
    exit();
}

?>
