<?php
// Include config and require lecturer role
require_once '../config/config.php';

requireRole('lecturer');

$success = '';
$error = '';

// Handle class creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $class_code = sanitize($_POST['class_code']);
    $class_name = sanitize($_POST['class_name']);
    $description = sanitize($_POST['description']);
    $max_students = (int)$_POST['max_students'];
    $schedule_day = sanitize($_POST['schedule_day']);
    $schedule_time = sanitize($_POST['schedule_time']);
    $room = sanitize($_POST['room']);
    $lecturer_id = $_SESSION['user_id'];
    
    // Validate input
    if (empty($class_code) || empty($class_name) || empty($schedule_day) || empty($schedule_time)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if class code already exists
        $check_query = "SELECT id FROM classes WHERE class_code = '$class_code'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Class code already exists';
        } else {
            // Insert new class
            $query = "INSERT INTO classes (class_code, class_name, description, lecturer_id, max_students, schedule_day, schedule_time, room) 
                      VALUES ('$class_code', '$class_name', '$description', $lecturer_id, $max_students, '$schedule_day', '$schedule_time', '$room')";
            
            if (mysqli_query($conn, $query)) {
                $success = 'Class created successfully!';
            } else {
                $error = 'Error creating class: ' . mysqli_error($conn);
            }
        }
    }
}

// Handle class deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $class_id = (int)$_GET['delete'];
    $lecturer_id = $_SESSION['user_id'];
    
    // Verify lecturer owns this class
    $delete_query = "DELETE FROM classes WHERE id = $class_id AND lecturer_id = $lecturer_id";
    
    if (mysqli_query($conn, $delete_query)) {
        if (mysqli_affected_rows($conn) > 0) {
            $success = 'Class deleted successfully!';
        } else {
            $error = 'Class not found or you do not have permission to delete it';
        }
    } else {
        $error = 'Error deleting class: ' . mysqli_error($conn);
    }
}

// Get lecturer's classes from database
$lecturer_id = $_SESSION['user_id'];
$classes_query = "SELECT c.*, COUNT(e.id) as enrolled_count 
                  FROM classes c 
                  LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
                  WHERE c.lecturer_id = $lecturer_id AND c.status = 'active'
                  GROUP BY c.id
                  ORDER BY c.created_at DESC";
$classes_result = mysqli_query($conn, $classes_query);

$page_title = 'Lecturer Dashboard';
include 'header.php';
?>

<div class="dashboard">
    <h2>My Classes</h2>
    
    <!-- Display success/error messages -->
    <?php if (!empty($success)): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Create New Class Button -->
    <button onclick="toggleCreateForm()" class="btn btn-primary" style="margin-bottom: 20px;">
        Create New Class
    </button>
    
    <!-- Create Class Form (hidden by default) -->
    <div id="createClassForm" class="create-class-form" style="display: none;">
        <h3>Create New Class</h3>
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="class_code">Class Code *</label>
                    <input type="text" id="class_code" name="class_code" required placeholder="e.g., CS101">
                </div>
                
                <div class="form-group">
                    <label for="class_name">Class Name *</label>
                    <input type="text" id="class_name" name="class_name" required placeholder="e.g., Introduction to Programming">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief description of the class"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="max_students">Max Students</label>
                    <input type="number" id="max_students" name="max_students" value="30" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label for="schedule_day">Schedule Day *</label>
                    <select id="schedule_day" name="schedule_day" required>
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="schedule_time">Schedule Time *</label>
                    <input type="time" id="schedule_time" name="schedule_time" required>
                </div>
                
                <div class="form-group">
                    <label for="room">Room</label>
                    <input type="text" id="room" name="room" placeholder="e.g., Room A101">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="create_class" class="btn btn-primary">Create Class</button>
                <button type="button" onclick="toggleCreateForm()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Display lecturer's classes in a grid -->
    <div class="classes-grid">
        <?php if ($classes_result && mysqli_num_rows($classes_result) > 0): ?>
            <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                <div class="class-card">
                    <div class="class-header">
                        <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                        <span class="enrollment-count"><?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></span>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                    
                    <?php if (!empty($class['description'])): ?>
                        <p class="class-description"><?php echo htmlspecialchars($class['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="class-schedule">
                        <strong>Schedule:</strong> <?php echo $class['schedule_day']; ?> at <?php echo date('g:i A', strtotime($class['schedule_time'])); ?>
                        <?php if (!empty($class['room'])): ?>
                            <br><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="class-actions">
                        <a href="class_details.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">View Details</a>
                        <a href="?delete=<?php echo $class['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this class? This will remove all enrollments and attendance records.')">
                           Delete
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-classes">
                <p>You haven't created any classes yet. Click "Create New Class" to get started!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for toggling create class form -->
<script>
function toggleCreateForm() {
    const form = document.getElementById('createClassForm');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}
</script>

</body>
</html>
