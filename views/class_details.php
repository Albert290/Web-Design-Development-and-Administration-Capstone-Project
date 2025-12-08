<?php
require_once '../config/config.php';
requireRole('lecturer');

$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lecturer_id = $_SESSION['user_id'];

// Verify lecturer owns this class
$class_query = "SELECT * FROM classes WHERE id = $class_id AND lecturer_id = $lecturer_id";
$class_result = mysqli_query($conn, $class_query);

if (mysqli_num_rows($class_result) === 0) {
    redirect('dashboard_lecturer.php');
}

$class = mysqli_fetch_assoc($class_result);
$success = '';
$error = '';

// Handle grade update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_grade'])) {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $grade = (float)$_POST['grade'];
    
    $update_query = "UPDATE enrollments SET grade = $grade WHERE id = $enrollment_id";
    if (mysqli_query($conn, $update_query)) {
        $success = 'Grade updated successfully!';
    } else {
        $error = 'Error updating grade';
    }
}

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $attendance_date = sanitize($_POST['attendance_date']);
    $status = sanitize($_POST['status']);
    $notes = sanitize($_POST['notes']);
    
    $attendance_query = "INSERT INTO attendance (enrollment_id, attendance_date, status, notes) 
                        VALUES ($enrollment_id, '$attendance_date', '$status', '$notes')
                        ON DUPLICATE KEY UPDATE status = '$status', notes = '$notes'";
    
    if (mysqli_query($conn, $attendance_query)) {
        $success = 'Attendance marked successfully!';
    } else {
        $error = 'Error marking attendance';
    }
}

// Get enrolled students with their attendance stats
$students_query = "SELECT e.id as enrollment_id, e.grade, u.full_name, u.email,
                   COUNT(a.id) as total_sessions,
                   SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                   ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as attendance_rate
                   FROM enrollments e
                   JOIN users u ON e.student_id = u.id
                   LEFT JOIN attendance a ON e.id = a.enrollment_id
                   WHERE e.class_id = $class_id AND e.status = 'enrolled'
                   GROUP BY e.id
                   ORDER BY u.full_name";
$students_result = mysqli_query($conn, $students_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details - DCMA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>DCMA - Class Details</h1>
            <div class="user-info">
                <a href="dashboard_lecturer.php" class="btn btn-secondary">Back to Dashboard</a>
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

        <section class="class-info-section">
            <h2><?php echo htmlspecialchars($class['class_code'] . ' - ' . $class['class_name']); ?></h2>
            <div class="class-details">
                <p><strong>Description:</strong> <?php echo htmlspecialchars($class['description']); ?></p>
                <p><strong>Schedule:</strong> <?php echo $class['schedule_day'] . ' at ' . date('g:i A', strtotime($class['schedule_time'])); ?></p>
                <p><strong>Room:</strong> <?php echo htmlspecialchars($class['room']); ?></p>
                <p><strong>Max Students:</strong> <?php echo $class['max_students']; ?></p>
            </div>
        </section>

        <section class="students-section">
            <h3>Enrolled Students</h3>
            
            <?php if (mysqli_num_rows($students_result) > 0): ?>
                <div class="students-table-container">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Current Grade</th>
                                <th>Attendance Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="enrollment_id" value="<?php echo $student['enrollment_id']; ?>">
                                            <input type="number" name="grade" value="<?php echo $student['grade']; ?>" 
                                                   min="0" max="100" step="0.1" class="grade-input">
                                            <button type="submit" name="update_grade" class="btn btn-small">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ($student['total_sessions'] > 0): ?>
                                            <?php echo $student['attendance_rate']; ?>% 
                                            (<?php echo $student['present_count']; ?>/<?php echo $student['total_sessions']; ?>)
                                        <?php else: ?>
                                            No records
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button onclick="showAttendanceModal(<?php echo $student['enrollment_id']; ?>, '<?php echo htmlspecialchars($student['full_name']); ?>')" 
                                                class="btn btn-primary btn-small">Mark Attendance</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No students enrolled in this class yet.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Attendance Modal -->
    <div id="attendanceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Mark Attendance</h3>
            <p id="studentName"></p>
            
            <form method="POST">
                <input type="hidden" id="modalEnrollmentId" name="enrollment_id">
                
                <div class="form-group">
                    <label for="attendance_date">Date:</label>
                    <input type="date" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="mark_attendance" class="btn btn-primary">Mark Attendance</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAttendanceModal(enrollmentId, studentName) {
            document.getElementById('attendanceModal').style.display = 'block';
            document.getElementById('modalEnrollmentId').value = enrollmentId;
            document.getElementById('studentName').textContent = 'Student: ' + studentName;
        }

        function closeModal() {
            document.getElementById('attendanceModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('attendanceModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
