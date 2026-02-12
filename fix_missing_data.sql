-- ================================================================
-- FIX MISSING DATA ISSUES
-- This script adds:
-- 1. teacher1 account for login
-- 2. Timetable data for BSc IT Semester 1
-- 3. Missing teacher-subject assignments
-- ================================================================

-- 1. Add teacher1 account (Password: teacher123)
INSERT INTO users (id, username, password, role, name) VALUES 
(1, 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'John Doe Teacher')
ON DUPLICATE KEY UPDATE username=username;

-- 2. Add teacher-subject assignments for all 5 subjects
-- Teacher 10 -> Subject 1 (already exists)
-- Teacher 11 -> Subject 2  
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES 
(11, 2, '2025-26')
ON DUPLICATE KEY UPDATE teacher_id=teacher_id;

-- Teacher 12 -> Subject 3
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES 
(12, 3, '2025-26')
ON DUPLICATE KEY UPDATE teacher_id=teacher_id;

-- Teacher 13 -> Subject 4
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES 
(13, 4, '2025-26')
ON DUPLICATE KEY UPDATE teacher_id=teacher_id;

-- Teacher 14 -> Subject 5
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES 
(14, 5, '2025-26')
ON DUPLICATE KEY UPDATE teacher_id=teacher_id;

-- 3. Add Timetable for BSc IT Semester 1 (Course ID 1, Semester 1)
-- Based on the timetable structure in the database schema

-- Monday
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(1, 1, 'Monday', 1, 1, 10, '2025-26', '09:00:00', '10:00:00'),
(1, 1, 'Monday', 2, 3, 12, '2025-26', '10:15:00', '11:15:00'),
(1, 1, 'Monday', 3, 2, 11, '2025-26', '11:15:00', '12:15:00'),
(1, 1, 'Monday', 4, 4, 13, '2025-26', '13:00:00', '14:00:00'),
(1, 1, 'Monday', 5, 5, 14, '2025-26', '14:00:00', '15:00:00'),
(1, 1, 'Monday', 6, 1, 10, '2025-26', '15:00:00', '16:00:00');

-- Tuesday
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(1, 1, 'Tuesday', 1, 2, 11, '2025-26', '09:00:00', '10:00:00'),
(1, 1, 'Tuesday', 2, 1, 10, '2025-26', '10:15:00', '11:15:00'),
(1, 1, 'Tuesday', 3, 4, 13, '2025-26', '11:15:00', '12:15:00'),
(1, 1, 'Tuesday', 4, 3, 12, '2025-26', '13:00:00', '14:00:00'),
(1, 1, 'Tuesday', 5, 2, 11, '2025-26', '14:00:00', '15:00:00'),
(1, 1, 'Tuesday', 6, 5, 14, '2025-26', '15:00:00', '16:00:00');

-- Wednesday
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(1, 1, 'Wednesday', 1, 3, 12, '2025-26', '09:00:00', '10:00:00'),
(1, 1, 'Wednesday', 2, 4, 13, '2025-26', '10:15:00', '11:15:00'),
(1, 1, 'Wednesday', 3, 5, 14, '2025-26', '11:15:00', '12:15:00'),
(1, 1, 'Wednesday', 4, 1, 10, '2025-26', '13:00:00', '14:00:00'),
(1, 1, 'Wednesday', 5, 3, 12, '2025-26', '14:00:00', '15:00:00'),
(1, 1, 'Wednesday', 6, 2, 11, '2025-26', '15:00:00', '16:00:00');

-- Thursday
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(1, 1, 'Thursday', 1, 4, 13, '2025-26', '09:00:00', '10:00:00'),
(1, 1, 'Thursday', 2, 5, 14, '2025-26', '10:15:00', '11:15:00'),
(1, 1, 'Thursday', 3, 1, 10, '2025-26', '11:15:00', '12:15:00'),
(1, 1, 'Thursday', 4, 2, 11, '2025-26', '13:00:00', '14:00:00'),
(1, 1, 'Thursday', 5, 4, 13, '2025-26', '14:00:00', '15:00:00'),
(1, 1, 'Thursday', 6, 3, 12, '2025-26', '15:00:00', '16:00:00');

-- Friday
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(1, 1, 'Friday', 1, 1, 10, '2025-26', '09:00:00', '10:00:00'),
(1, 1, 'Friday', 2, 3, 12, '2025-26', '10:15:00', '11:15:00'),
(1, 1, 'Friday', 3, 2, 11, '2025-26', '11:15:00', '12:15:00'),
(1, 1, 'Friday', 4, 5, 14, '2025-26', '13:00:00', '14:00:00'),
(1, 1, 'Friday', 5, 4, 13, '2025-26', '14:00:00', '15:00:00'),
(1, 1, 'Friday', 6, 1, 10, '2025-26', '15:00:00', '16:00:00');

-- ================================================================
-- FIX COMPLETE
-- ================================================================
