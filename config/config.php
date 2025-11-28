<?php
// Start session
session_start();

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'dcma';

// Create database connection
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');

// Helper Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if user doesn't have required role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: index.php');
        exit();
    }
}

// Sanitize user input to prevent SQL injection
function sanitize($data) {
    global $conn;
    if ($conn) {
        return mysqli_real_escape_string($conn, trim($data));
    }
    return trim($data); // Basic sanitization when DB not available
}

?>
