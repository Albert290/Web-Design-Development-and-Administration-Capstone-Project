<?php
require_once '../config/config.php';
requireRole('lecturer');

$success = '';
$error = '';

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $class_code = sanitize($_POST['class_code']);
    $class_name = sanitize($_POST['class_name']);
    $description = sanitize($_POST['description']);
    $max_students = (int)$_POST['max_students'];
    $schedule_day = sanitize($_POST['schedule_day']);
    $schedule_time = sanitize($_POST['schedule_time']);
    $room = sanitize($_POST['room']);
    
    if (empty($class_code) || empty($class_name)) {
        $error = 'Class code and name are required';
    } else {
        $lecturer_id = $_SESSION['user_id'];
        $query = "INSERT INTO classes (class_code, class_name, description, lecturer_id, max_students, schedule_day, schedule_time, room) 
                  VALUES ('$class_code', '$class_name', '$description', $lecturer_id, $max_students, '$schedule_day', '$schedule_time', '$room')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Class created successfully!';
        } else {
            $error = 'Error creating class: ' . mysqli_error($conn);
        }
    }
}

// Handle class deletion
if (isset($_GET['delete'])) {
    $class_id = (int)$_GET['delete'];
    $lecturer_id = $_SESSION['user_id'];
    
    $query = "DELETE FROM classes WHERE id = $class_id AND lecturer_id = $lecturer_id";
    if (mysqli_query($conn, $query)) {
        $success = 'Class deleted successfully!';
    } else {
        $error = 'Error deleting class';
    }
}

// Get lecturer's classes
$lecturer_id = $_SESSION['user_id'];
$query = "SELECT c.*, COUNT(e.id) as enrolled_count 
          FROM classes c 
          LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'enrolled'
          WHERE c.lecturer_id = $lecturer_id 
          GROUP BY c.id 
          ORDER BY c.created_at DESC";
$classes_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard - DCMA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>DCMA - Lecturer Dashboard</h1>
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

        <section class="create-class-section">
            <button onclick="toggleForm()" class="btn btn-primary">Create New Class</button>
            
            <form id="createClassForm" method="POST" style="display: none;" class="class-form">
                <input type="hidden" name="create_class" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="class_code">Class Code:</label>
                        <input type="text" id="class_code" name="class_code" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_name">Class Name:</label>
                        <input type="text" id="class_name" name="class_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="max_students">Max Students:</label>
                        <input type="number" id="max_students" name="max_students" value="30" min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule_day">Day:</label>
                        <select id="schedule_day" name="schedule_day" required>
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
                        <label for="schedule_time">Time:</label>
                        <input type="time" id="schedule_time" name="schedule_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="room">Room:</label>
                        <input type="text" id="room" name="room">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Class</button>
                    <button type="button" onclick="toggleForm()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </section>

        <section class="classes-section">
            <h2>My Classes</h2>
            
            <?php if (mysqli_num_rows($classes_result) > 0): ?>
                <div class="classes-grid">
                    <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                        <div class="class-card">
                            <h3><?php echo htmlspecialchars($class['class_code']); ?></h3>
                            <h4><?php echo htmlspecialchars($class['class_name']); ?></h4>
                            <p><?php echo htmlspecialchars($class['description']); ?></p>
                            
                            <div class="class-info">
                                <p><strong>Schedule:</strong> <?php echo $class['schedule_day'] . ' at ' . date('g:i A', strtotime($class['schedule_time'])); ?></p>
                                <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                                <p><strong>Enrolled:</strong> <?php echo $class['enrolled_count']; ?>/<?php echo $class['max_students']; ?></p>
                            </div>
                            
                            <div class="class-actions">
                                <a href="class_details.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">View Details</a>
                                <a href="?delete=<?php echo $class['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this class?')">Delete</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No classes created yet. Create your first class above!</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function toggleForm() {
            const form = document.getElementById('createClassForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
