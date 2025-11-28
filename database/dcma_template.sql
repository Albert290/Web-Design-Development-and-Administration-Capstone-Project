-- Dynamic Class Management Application Database

-- Create the database
CREATE DATABASE IF NOT EXISTS dcma;
USE dcma;

-- Create users table (students and lecturers)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'lecturer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create classes table
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

-- Create enrollments table (links students to classes)
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

-- Create attendance table
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

-- Insert sample data for testing

-- Insert lecturers (passwords are hashed version of 'password123')
INSERT INTO users (username, password, full_name, email, role) VALUES
('john_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'john@university.edu', 'lecturer'),
('mary_lecturer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Mary Johnson', 'mary@university.edu', 'lecturer');

-- Insert students (passwords are hashed version of 'password123')
INSERT INTO users (username, password, full_name, email, role) VALUES
('alice_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 'alice@student.edu', 'student'),
('bob_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Wilson', 'bob@student.edu', 'student'),
('charlie_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Charlie Brown', 'charlie@student.edu', 'student'),
('diana_student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Diana Prince', 'diana@student.edu', 'student');

-- Insert sample classes
INSERT INTO classes (class_code, class_name, description, lecturer_id, max_students, schedule_day, schedule_time, room) VALUES
('CS101', 'Introduction to Programming', 'Learn the basics of programming using Python', 1, 25, 'Monday', '09:00:00', 'Room A101'),
('CS201', 'Data Structures', 'Advanced programming concepts and data structures', 1, 20, 'Wednesday', '14:00:00', 'Room B202'),
('MATH101', 'Calculus I', 'Introduction to differential and integral calculus', 2, 30, 'Tuesday', '10:00:00', 'Room C301'),
('MATH201', 'Linear Algebra', 'Vectors, matrices, and linear transformations', 2, 25, 'Thursday', '11:00:00', 'Room C302');

-- Insert sample enrollments
INSERT INTO enrollments (student_id, class_id, grade) VALUES
(3, 1, 85.5),  -- Alice in CS101
(3, 3, 92.0),  -- Alice in MATH101
(4, 1, 78.0),  -- Bob in CS101
(4, 2, NULL),  -- Bob in CS201 (no grade yet)
(5, 1, 88.5),  -- Charlie in CS101
(5, 4, 91.0),  -- Charlie in MATH201
(6, 3, 87.5);  -- Diana in MATH101

-- Insert sample attendance records
INSERT INTO attendance (enrollment_id, attendance_date, status, notes) VALUES
(1, '2024-01-15', 'present', NULL),
(1, '2024-01-22', 'present', NULL),
(1, '2024-01-29', 'late', 'Arrived 10 minutes late'),
(2, '2024-01-16', 'present', NULL),
(2, '2024-01-23', 'absent', 'Sick'),
(3, '2024-01-15', 'present', NULL),
(3, '2024-01-22', 'present', NULL),
(4, '2024-01-17', 'present', NULL),
(5, '2024-01-15', 'present', NULL),
(6, '2024-01-18', 'present', NULL),
(7, '2024-01-16', 'present', NULL);
