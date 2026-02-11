-- Attendance Management System Database Setup
-- Create database and tables

CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;

-- Users table: stores login credentials for teachers and students
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table: stores class/subject information
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    teacher_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Students table: stores student details
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT NOT NULL,
    roll_number VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_roll_class (roll_number, class_id)
);

-- Attendance table: stores daily attendance records
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_date (student_id, date)
);

-- Insert sample teacher
INSERT INTO users (username, password, role, name) VALUES
('teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'John Doe Teacher');
-- Password is 'teacher123' hashed with bcrypt

-- Get the teacher ID
SET @teacher_id = LAST_INSERT_ID();

-- Insert sample class
INSERT INTO classes (class_name, teacher_id) VALUES
('Computer Science 101', @teacher_id),
('Mathematics 101', @teacher_id);

-- Get class IDs
SET @cs_class_id = (SELECT id FROM classes WHERE class_name = 'Computer Science 101');
SET @math_class_id = (SELECT id FROM classes WHERE class_name = 'Mathematics 101');

-- Insert sample students
INSERT INTO users (username, password, role, name) VALUES
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alice Smith'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Bob Johnson'),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Charlie Brown'),
('student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Diana Prince');
-- Password is 'student123' for all students

-- Link students to classes
INSERT INTO students (user_id, class_id, roll_number, name) VALUES
((SELECT id FROM users WHERE username = 'student1'), @cs_class_id, 'CS001', 'Alice Smith'),
((SELECT id FROM users WHERE username = 'student2'), @cs_class_id, 'CS002', 'Bob Johnson'),
((SELECT id FROM users WHERE username = 'student3'), @cs_class_id, 'CS003', 'Charlie Brown'),
((SELECT id FROM users WHERE username = 'student4'), @math_class_id, 'MATH001', 'Diana Prince'),
((SELECT id FROM users WHERE username = 'student1'), @math_class_id, 'MATH002', 'Alice Smith');

-- Insert sample attendance records (for testing)
INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES
(1, @cs_class_id, CURDATE() - INTERVAL 5 DAY, 'present', @teacher_id),
(1, @cs_class_id, CURDATE() - INTERVAL 4 DAY, 'present', @teacher_id),
(1, @cs_class_id, CURDATE() - INTERVAL 3 DAY, 'absent', @teacher_id),
(1, @cs_class_id, CURDATE() - INTERVAL 2 DAY, 'present', @teacher_id),
(1, @cs_class_id, CURDATE() - INTERVAL 1 DAY, 'present', @teacher_id),
(2, @cs_class_id, CURDATE() - INTERVAL 5 DAY, 'present', @teacher_id),
(2, @cs_class_id, CURDATE() - INTERVAL 4 DAY, 'absent', @teacher_id),
(2, @cs_class_id, CURDATE() - INTERVAL 3 DAY, 'absent', @teacher_id),
(2, @cs_class_id, CURDATE() - INTERVAL 2 DAY, 'present', @teacher_id),
(2, @cs_class_id, CURDATE() - INTERVAL 1 DAY, 'present', @teacher_id);

SELECT 'Database setup completed successfully!' AS message;
