<?php
// Include config file
require_once 'config/config.php';

// Test database connection
$test_query = "SELECT COUNT(*) as count FROM users";
$test_result = mysqli_query($conn, $test_query);
if ($test_result) {
    $count = mysqli_fetch_assoc($test_result);
    echo "Database connected! Found " . $count['count'] . " users.<br>";
} else {
    echo "Database connection failed: " . mysqli_error($conn) . "<br>";
}


// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Query database for user
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        // Debug: Check if query worked
        if (!$result) {
            $error = 'Database error: ' . mysqli_error($conn);
        } else if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Debug: Show what we found (remove this in production)
            // $error = 'Found user: ' . $user['username'] . ', stored hash: ' . substr($user['password'], 0, 20) . '...';
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect to dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'Password verification failed';
            }
        } else {
            $error = 'User not found in database';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DCMA</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Dynamic Class Management</h1>
            <h2>Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <p><strong>Lecturer:</strong> john_lecturer / password123</p>
                <p><strong>Student:</strong> alice_student / password123</p>
            </div>
        </div>
    </div>
</body>
</html>
