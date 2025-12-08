-- Dynamic Class Management Application Database
-- Complete Schema with Sample Data

DROP DATABASE IF EXISTS dcma;
CREATE DATABASE dcma;
USE dcma;

-- Users table (students and lecturers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'lecturer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_code VARCHAR(20) UNIQUE NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    description TEXT,
    lecturer_id INT NOT NULL,
    max_students INT DEFAULT 30,
    schedule_day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    schedule_time TIME NOT NULL,
    room VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Enrollments table
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'dropped') DEFAULT 'enrolled',
    grade DECIMAL(5,2) NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, class_id)
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    notes TEXT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (enrollment_id, attendance_date)
);

-- Insert sample users (password: password123)
INSERT INTO users (username, password, full_name, email, role) VALUES
('john_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'john@university.edu', 'lecturer'),
('mary_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Mary Johnson', 'mary@university.edu', 'lecturer'),
('alice_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Brown', 'alice@student.edu', 'student'),
('bob_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'bob@student.edu', 'student'),
('carol_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carol Davis', 'carol@student.edu', 'student');

-- Insert sample classes
INSERT INTO classes (class_code, class_name, description, lecturer_id, max_students, schedule_day, schedule_time, room) VALUES
('CS101', 'Introduction to Programming', 'Basic programming concepts using PHP and JavaScript', 1, 25, 'Monday', '09:00:00', 'Room A101'),
('WEB201', 'Web Development', 'HTML, CSS, JavaScript and PHP web development', 1, 20, 'Wednesday', '14:00:00', 'Lab B202'),
('DB301', 'Database Systems', 'MySQL database design and administration', 2, 30, 'Friday', '10:00:00', 'Room C301');

-- Insert sample enrollments
INSERT INTO enrollments (student_id, class_id, grade) VALUES
(3, 1, 85.5),
(4, 1, 92.0),
(5, 1, 78.5),
(3, 2, 88.0),
(4, 3, 90.5);

-- Insert sample attendance
INSERT INTO attendance (enrollment_id, attendance_date, status, notes) VALUES
(1, '2024-12-01', 'present', 'Active participation'),
(1, '2024-12-03', 'late', 'Arrived 10 minutes late'),
(2, '2024-12-01', 'present', 'Excellent work'),
(2, '2024-12-03', 'present', 'Good questions'),
(3, '2024-12-01', 'absent', 'Sick leave'),
(3, '2024-12-03', 'present', 'Caught up well');
