-- ================================================================
-- Sample Data for Attendance Management System
-- 20 Students + 1 Teacher + Attendance Records (Jan 1 - Feb 12, 2026)
-- ================================================================

-- Teacher: teacher_fine (Password: teacher123)
INSERT INTO users (id, username, password, role, name) VALUES 
(10, 'teacher_fine', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Fine Teacher');

-- 20 Students (Password for all: student123)
INSERT INTO users (id, username, password, role, name) VALUES 
(20, 'suresh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Suresh Kumar'),
(21, 'ramesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Ramesh Patel'),
(22, 'mahesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Mahesh Singh'),
(23, 'ganesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Ganesh Sharma'),
(24, 'naresh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Naresh Verma'),
(25, 'rajesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Rajesh Gupta'),
(26, 'dinesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Dinesh Kumar'),
(27, 'mukesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Mukesh Jain'),
(28, 'lokesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Lokesh Yadav'),
(29, 'hitesh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Hitesh Reddy'),
(30, 'amit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Amit Shah'),
(31, 'sumit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Sumit Mehta'),
(32, 'rohit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Rohit Chopra'),
(33, 'mohit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Mohit Agarwal'),
(34, 'vikas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Vikas Malhotra'),
(35, 'ankit', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Ankit Kapoor'),
(36, 'sachin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Sachin Desai'),
(37, 'pankaj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Pankaj Nair'),
(38, 'kiran', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Kiran Pillai'),
(39, 'nitin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Nitin Bhatt');

-- Enroll students in BSc IT Semester 1 (course_id=1, semester=1)
INSERT INTO students (id, user_id, name, roll_number, course_id, semester, academic_year) VALUES 
(1, 20, 'Suresh Kumar', 'IT2026001', 1, 1, '2025-26'),
(2, 21, 'Ramesh Patel', 'IT2026002', 1, 1, '2025-26'),
(3, 22, 'Mahesh Singh', 'IT2026003', 1, 1, '2025-26'),
(4, 23, 'Ganesh Sharma', 'IT2026004', 1, 1, '2025-26'),
(5, 24, 'Naresh Verma', 'IT2026005', 1, 1, '2025-26'),
(6, 25, 'Rajesh Gupta', 'IT2026006', 1, 1, '2025-26'),
(7, 26, 'Dinesh Kumar', 'IT2026007', 1, 1, '2025-26'),
(8, 27, 'Mukesh Jain', 'IT2026008', 1, 1, '2025-26'),
(9, 28, 'Lokesh Yadav', 'IT2026009', 1, 1, '2025-26'),
(10, 29, 'Hitesh Reddy', 'IT2026010', 1, 1, '2025-26'),
(11, 30, 'Amit Shah', 'IT2026011', 1, 1, '2025-26'),
(12, 31, 'Sumit Mehta', 'IT2026012', 1, 1, '2025-26'),
(13, 32, 'Rohit Chopra', 'IT2026013', 1, 1, '2025-26'),
(14, 33, 'Mohit Agarwal', 'IT2026014', 1, 1, '2025-26'),
(15, 34, 'Vikas Malhotra', 'IT2026015', 1, 1, '2025-26'),
(16, 35, 'Ankit Kapoor', 'IT2026016', 1, 1, '2025-26'),
(17, 36, 'Sachin Desai', 'IT2026017', 1, 1, '2025-26'),
(18, 37, 'Pankaj Nair', 'IT2026018', 1, 1, '2025-26'),
(19, 38, 'Kiran Pillai', 'IT2026019', 1, 1, '2025-26'),
(20, 39, 'Nitin Bhatt', 'IT2026020', 1, 1, '2025-26');

-- Assign teacher to subject (Programming in C - subject_id=1)
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES (10, 1, '2025-26');

-- ================================================================
-- Attendance Records (January 1 - February 12, 2026)
-- Subject: Programming in C (subject_id=1), Period 1
-- Pattern: Students 5, 10, 15, 20 are frequently absent
-- ================================================================
