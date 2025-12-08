<?php
require_once '../config/config.php';
requireRole('student');

$success = '';
$error = '';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $class_id = (int)$_POST['class_id'];
    $student_id = $_SESSION['user_id'];
    
    // Check if already enrolled
    $check_query = "SELECT id FROM enrollments WHERE student_id = $student_id AND class_id = $class_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = 'You are already enrolled in this class';
    } else {
        // Check if class is full
        $capacity_query = "SELECT c.max_students, COUNT(e.id) as enrolled 
                          FROM classes c 
                          LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
                          WHERE c.id = $class_id 
                          GROUP BY c.id";
        $capacity_result = mysqli_query($conn, $capacity_query);
        $capacity = mysqli_fetch_assoc($capacity_result);
        
        if ($capacity['enrolled'] >= $capacity['max_students']) {
            $error = 'This class is full';
        } else {
            $enroll_query = "INSERT INTO enrollments (student_id, class_id) VALUES ($student_id, $class_id)";
            if (mysqli_query($conn, $enroll_query)) {
                $success = 'Successfully enrolled in class!';
            } else {
                $error = 'Error enrolling in class';
            }
        }
    }
}

// Handle drop class
if (isset($_GET['drop'])) {
    $enrollment_id = (int)$_GET['drop'];
    $student_id = $_SESSION['user_id'];
    
    $query = "UPDATE enrollments SET status = 'dropped' WHERE id = $enrollment_id AND student_id = $student_id";
    if (mysqli_query($conn, $query)) {
        $success = 'Successfully dropped from class';
    } else {
        $error = 'Error dropping class';
    }
}

// Get enrolled classes
$student_id = $_SESSION['user_id'];
$enrolled_query = "SELECT c.*, e.id as enrollment_id, e.grade, u.full_name as lecturer_name
                   FROM enrollments e
                   JOIN classes c ON e.class_id = c.id
                   JOIN users u ON c.lecturer_id = u.id
                   WHERE e.student_id = $student_id AND e.status = 'enrolled'
                   ORDER BY c.schedule_day, c.schedule_time";
$enrolled_result = mysqli_query($conn, $enrolled_query);

// Get available classes (not enrolled, not full)
$available_query = "SELECT c.*, u.full_name as lecturer_name, COUNT(e.id) as enrolled_count
                    FROM classes c
                    JOIN users u ON c.lecturer_id = u.id
                    LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
                    WHERE c.id NOT IN (
                        SELECT class_id FROM enrollments 
                        WHERE student_id = $student_id AND status = 'enrolled'
                    )
                    AND c.status = 'active'
                    GROUP BY c.id
                    HAVING enrolled_count < c.max_students
                    ORDER BY c.class_code";
$available_result = mysqli_query($conn, $available_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - DCMA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>DCMA - Student Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="enrolled-classes-section">
            <h2>My Enrolled Classes</h2>
            
            <?php if (mysqli_num_rows($enrolled_result) > 0): ?>
                <div class="classes-grid">
                    <?php while ($class = mysqli_fetch_assoc($enrolled_result)): ?>
                        <div class="class-card enrolled">
                            <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                            <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                            <p><?php echo htmlspecialchars($class['description']); ?></p>
                            
                            <div class="class-info">
                                <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                                <p><strong>Schedule:</strong> <?php echo $class['schedule_day'] . ' at ' . date('g:i A', strtotime($class['schedule_time'])); ?></p>
                                <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                                <?php if ($class['grade']): ?>
                                    <p><strong>Grade:</strong> <span class="grade"><?php echo $class['grade']; ?>%</span></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="class-actions">
                                <a href="class_view.php?id=<?php echo $class['enrollment_id']; ?>" class="btn btn-primary">View Details</a>
                                <a href="?drop=<?php echo $class['enrollment_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to drop this class?')">Drop Class</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">You are not enrolled in any classes yet.</p>
            <?php endif; ?>
        </section>

        <section class="available-classes-section">
            <h2>Available Classes</h2>
            
            <?php if (mysqli_num_rows($available_result) > 0): ?>
                <div class="classes-grid">
                    <?php while ($class = mysqli_fetch_assoc($available_result)): ?>
                        <div class="class-card available">
                            <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                            <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                            <p><?php echo htmlspecialchars($class['description']); ?></p>
                            
                            <div class="class-info">
                                <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                                <p><strong>Schedule:</strong> <?php echo $class['schedule_day'] . ' at ' . date('g:i A', strtotime($class['schedule_time'])); ?></p>
                                <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                                <p><strong>Capacity:</strong> <?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></p>
                            </div>
                            
                            <div class="class-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                    <button type="submit" name="enroll" class="btn btn-primary">Enroll</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No available classes at the moment.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
