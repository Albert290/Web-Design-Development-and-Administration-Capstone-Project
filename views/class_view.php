<?php
require_once '../config/config.php';
requireRole('student');

$enrollment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

// Verify student owns this enrollment
$enrollment_query = "SELECT e.*, c.*, u.full_name as lecturer_name, u.email as lecturer_email
                     FROM enrollments e
                     JOIN classes c ON e.class_id = c.id
                     JOIN users u ON c.lecturer_id = u.id
                     WHERE e.id = $enrollment_id AND e.student_id = $student_id AND e.status = 'enrolled'";
$enrollment_result = mysqli_query($conn, $enrollment_query);

if (mysqli_num_rows($enrollment_result) === 0) {
    redirect('dashboard_student.php');
}

$enrollment = mysqli_fetch_assoc($enrollment_result);

// Get attendance records
$attendance_query = "SELECT * FROM attendance 
                     WHERE enrollment_id = $enrollment_id 
                     ORDER BY attendance_date DESC";
$attendance_result = mysqli_query($conn, $attendance_query);

// Calculate attendance statistics
$stats_query = "SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as attendance_percentage
                FROM attendance 
                WHERE enrollment_id = $enrollment_id";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class View - DCMA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>DCMA - Class Details</h1>
            <div class="user-info">
                <a href="dashboard_student.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="class-info-section">
            <h2><?php echo htmlspecialchars($enrollment['class_code'] . ' - ' . $enrollment['class_name']); ?></h2>
            
            <div class="class-details-grid">
                <div class="class-info">
                    <h3>Class Information</h3>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($enrollment['description']); ?></p>
                    <p><strong>Lecturer:</strong> <?php echo htmlspecialchars($enrollment['lecturer_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($enrollment['lecturer_email']); ?></p>
                    <p><strong>Schedule:</strong> <?php echo $enrollment['schedule_day'] . ' at ' . date('g:i A', strtotime($enrollment['schedule_time'])); ?></p>
                    <p><strong>Room:</strong> <?php echo htmlspecialchars($enrollment['room']); ?></p>
                </div>
                
                <div class="grade-info">
                    <h3>Your Grade</h3>
                    <?php if ($enrollment['grade']): ?>
                        <div class="grade-display">
                            <span class="grade-value"><?php echo $enrollment['grade']; ?>%</span>
                            <span class="grade-letter">
                                <?php 
                                $grade = $enrollment['grade'];
                                if ($grade >= 90) echo 'A';
                                elseif ($grade >= 80) echo 'B';
                                elseif ($grade >= 70) echo 'C';
                                elseif ($grade >= 60) echo 'D';
                                else echo 'F';
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <p class="no-grade">No grade assigned yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="attendance-section">
            <h3>Attendance Summary</h3>
            
            <?php if ($stats['total_sessions'] > 0): ?>
                <div class="attendance-stats">
                    <div class="stat-card">
                        <h4>Total Sessions</h4>
                        <span class="stat-value"><?php echo $stats['total_sessions']; ?></span>
                    </div>
                    
                    <div class="stat-card present">
                        <h4>Present</h4>
                        <span class="stat-value"><?php echo $stats['present_count']; ?></span>
                    </div>
                    
                    <div class="stat-card absent">
                        <h4>Absent</h4>
                        <span class="stat-value"><?php echo $stats['absent_count']; ?></span>
                    </div>
                    
                    <div class="stat-card late">
                        <h4>Late</h4>
                        <span class="stat-value"><?php echo $stats['late_count']; ?></span>
                    </div>
                    
                    <div class="stat-card percentage">
                        <h4>Attendance Rate</h4>
                        <span class="stat-value"><?php echo $stats['attendance_percentage']; ?>%</span>
                    </div>
                </div>

                <h4>Attendance History</h4>
                <div class="attendance-table-container">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attendance = mysqli_fetch_assoc($attendance_result)): ?>
                                <tr>
                                    <td><?php echo date('F j, Y', strtotime($attendance['attendance_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $attendance['status']; ?>">
                                            <?php echo ucfirst($attendance['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($attendance['notes']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">No attendance records yet.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
