<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - DCMA' : 'DCMA'; ?></title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <h1>Dynamic Class Management</h1>
            <nav>
                <?php if (isLoggedIn()): ?>
                    <div class="user-info">
                        <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <span class="user-role">(<?php echo ucfirst($_SESSION['role']); ?>)</span>
                        <a href="../logout.php" class="btn btn-secondary logout-btn">Logout</a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="container">
