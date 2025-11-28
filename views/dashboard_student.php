<?php
// Include config and require student role
require_once '../config/config.php';

requireRole('student');

$success = '';
$error = '';

// Handle class enrollment
if (isset($_GET['enroll']) && is_numeric($_GET['enroll'])) {
    $class_id = (int)$_GET['enroll'];
    $student_id = $_SESSION['user_id'];
    
    // Check if student is already enrolled
    $check_query = "SELECT id FROM enrollments WHERE student_id = $student_id AND class_id = $class_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = 'You are already enrolled in this class';
    } else {
        // Check if class is full
        $capacity_query = "SELECT c.max_students, COUNT(e.id) as current_enrolled 
                          FROM classes c 
                          LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
                          WHERE c.id = $class_id 
                          GROUP BY c.id";
        $capacity_result = mysqli_query($conn, $capacity_query);
        $capacity_data = mysqli_fetch_assoc($capacity_result);
        
        if ($capacity_data['current_enrolled'] >= $capacity_data['max_students']) {
            $error = 'This class is full';
        } else {
            // Insert enrollment record
            $enroll_query = "INSERT INTO enrollments (student_id, class_id) VALUES ($student_id, $class_id)";
            
            if (mysqli_query($conn, $enroll_query)) {
                $success = 'Successfully enrolled in class!';
            } else {
                $error = 'Error enrolling in class: ' . mysqli_error($conn);
            }
        }
    }
}

// Handle drop class
if (isset($_GET['drop']) && is_numeric($_GET['drop'])) {
    $enrollment_id = (int)$_GET['drop'];
    $student_id = $_SESSION['user_id'];
    
    // Update enrollment status to 'dropped'
    $drop_query = "UPDATE enrollments SET status = 'dropped' 
                   WHERE id = $enrollment_id AND student_id = $student_id";
    
    if (mysqli_query($conn, $drop_query)) {
        if (mysqli_affected_rows($conn) > 0) {
            $success = 'Successfully dropped from class';
        } else {
            $error = 'Enrollment not found or you do not have permission';
        }
    } else {
        $error = 'Error dropping class: ' . mysqli_error($conn);
    }
}

// Get student's enrolled classes
$student_id = $_SESSION['user_id'];
$enrolled_query = "SELECT e.id as enrollment_id, e.grade, c.*, u.full_name as lecturer_name
                   FROM enrollments e
                   JOIN classes c ON e.class_id = c.id
                   JOIN users u ON c.lecturer_id = u.id
                   WHERE e.student_id = $student_id AND e.status = 'enrolled' AND c.status = 'active'
                   ORDER BY c.schedule_day, c.schedule_time";
$enrolled_result = mysqli_query($conn, $enrolled_query);

// Get available classes (not enrolled, not full)
$available_query = "SELECT c.*, u.full_name as lecturer_name, COUNT(e.id) as enrolled_count
                    FROM classes c
                    JOIN users u ON c.lecturer_id = u.id
                    LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
                    WHERE c.status = 'active' 
                    AND c.id NOT IN (
                        SELECT class_id FROM enrollments 
                        WHERE student_id = $student_id AND status = 'enrolled'
                    )
                    GROUP BY c.id
                    HAVING enrolled_count < c.max_students
                    ORDER BY c.class_code";
$available_result = mysqli_query($conn, $available_query);

$page_title = 'Student Dashboard';
include 'header.php';
?>

<div class="dashboard">
    <h2>My Enrolled Classes</h2>
    
    <!-- Display success/error messages -->
    <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Display enrolled classes in a grid -->
    <div class="classes-grid">
        <?php if ($enrolled_result && mysqli_num_rows($enrolled_result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($enrolled_result)): ?>
                <div class="class-card enrolled">
                    <div class="class-header">
                        <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                        <?php if ($class['grade'] !== null): ?>
                            <span class="grade">Grade: <?php echo number_format($class['grade'], 1); ?>%</span>
                        <?php else: ?>
                            <span class="grade no-grade">No Grade</span>
                        <?php endif; ?>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                    
                    <?php if (!empty($class['description'])): ?>
                        <p class="class-description"><?php echo htmlspecialchars($class['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="class-info">
                        <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                        <p><strong>Schedule:</strong> <?php echo $class['schedule_day']; ?> at <?php echo date('g:i A', strtotime($class['schedule_time'])); ?></p>
                        <?php if (!empty($class['room'])): ?>
                            <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="class-actions">
                        <a href="class_view.php?id=<?php echo $class['enrollment_id']; ?>" class="btn btn-primary">View Details</a>
                        <a href="?drop=<?php echo $class['enrollment_id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to drop this class?')">
                           Drop Class
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-classes">
                <p>You are not enrolled in any classes yet. Check out the available classes below!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <h2>Available Classes</h2>
    
    <!-- Display available classes in a grid -->
    <div class="classes-grid">
        <?php if ($available_result && mysqli_num_rows($available_result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($available_result)): ?>
                <div class="class-card available">
                    <div class="class-header">
                        <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                        <span class="enrollment-count"><?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></span>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                    
                    <?php if (!empty($class['description'])): ?>
                        <p class="class-description"><?php echo htmlspecialchars($class['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="class-info">
                        <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($class['lecturer_name']); ?></p>
                        <p><strong>Schedule:</strong> <?php echo $class['schedule_day']; ?> at <?php echo date('g:i A', strtotime($class['schedule_time'])); ?></p>
                        <?php if (!empty($class['room'])): ?>
                            <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="class-actions">
                        <a href="?enroll=<?php echo $class['id']; ?>" class="btn btn-primary" 
                           onclick="return confirm('Are you sure you want to enroll in this class?')">
                           Enroll
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-classes">
                <p>No available classes at the moment. All classes are either full or you're already enrolled.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
