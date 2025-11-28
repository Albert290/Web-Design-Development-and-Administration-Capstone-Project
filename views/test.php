<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test - DCMA</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <h1>Dynamic Class Management</h1>
            <nav>
                <div class="user-info">
                    <span class="user-name">Welcome, Test User</span>
                    <span class="user-role">(Lecturer)</span>
                    <a href="#" class="btn btn-secondary logout-btn">Logout</a>
                </div>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard">
            <h2>CSS Test Page</h2>
            
            <div class="success-message">This is a success message - if you see green background, CSS is working!</div>
            
            <button class="btn btn-primary">Primary Button</button>
            
            <div class="classes-grid">
                <div class="class-card">
                    <div class="class-header">
                        <h3>CS101</h3>
                        <span class="enrollment-count">15/30</span>
                    </div>
                    <h4>Introduction to Programming</h4>
                    <p class="class-description">This is a test class card. If you see styling, CSS is working!</p>
                    <div class="class-actions">
                        <a href="#" class="btn btn-primary">View Details</a>
                        <a href="#" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>