-- ================================================================
-- Complete Semester 1 Timetable Setup
-- ================================================================
-- This script creates:
-- - 5 teachers with different subjects
-- - Complete Mon-Fri timetable (6 periods per day)
-- - Lunch break (after period 3)
-- - Short break (after period 1)
-- ================================================================

USE attendance_db;

-- ================================================================
-- STEP 1: CREATE MORE TEACHERS
-- ================================================================

-- Create 4 more teacher accounts
INSERT INTO users (username, password, role, name) VALUES
('teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Sarah Williams'),
('teacher3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Prof. Michael Chen'),
('teacher4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Dr. Emily Rodriguez'),
('teacher5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Mr. David Kumar')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Get teacher IDs
SET @teacher1_id = (SELECT id FROM users WHERE username = 'teacher1');
SET @teacher2_id = (SELECT id FROM users WHERE username = 'teacher2');
SET @teacher3_id = (SELECT id FROM users WHERE username = 'teacher3');
SET @teacher4_id = (SELECT id FROM users WHERE username = 'teacher4');
SET @teacher5_id = (SELECT id FROM users WHERE username = 'teacher5');

-- Get course IDs
SET @it_course = (SELECT id FROM courses WHERE course_code = 'BSC_IT');
SET @cs_course = (SELECT id FROM courses WHERE course_code = 'BSC_CS');

-- Get subject IDs for BSc IT
SET @it101 = (SELECT id FROM subjects WHERE subject_code = 'IT101'); -- Programming Fundamentals
SET @it102 = (SELECT id FROM subjects WHERE subject_code = 'IT102'); -- Digital Electronics
SET @it103 = (SELECT id FROM subjects WHERE subject_code = 'IT103'); -- Mathematics I
SET @it104 = (SELECT id FROM subjects WHERE subject_code = 'IT104'); -- Communication Skills
SET @it105 = (SELECT id FROM subjects WHERE subject_code = 'IT105'); -- Computer Organization

SELECT 'Teachers created successfully!' AS status;

-- ================================================================
-- STEP 2: ASSIGN SUBJECTS TO TEACHERS
-- ================================================================

-- Clear existing assignments
DELETE FROM teacher_subjects WHERE academic_year = '2025-26';

-- Assign subjects to teachers
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES
-- Teacher 1: Programming Fundamentals (John Doe)
(@teacher1_id, @it101, '2025-26'),
-- Teacher 2: Digital Electronics (Dr. Sarah Williams)
(@teacher2_id, @it102, '2025-26'),
-- Teacher 3: Mathematics I (Prof. Michael Chen)
(@teacher3_id, @it103, '2025-26'),
-- Teacher 4: Communication Skills (Dr. Emily Rodriguez)
(@teacher4_id, @it104, '2025-26'),
-- Teacher 5: Computer Organization (Mr. David Kumar)
(@teacher5_id, @it105, '2025-26')
ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year);

SELECT 'Subject assignments completed!' AS status;

-- ================================================================
-- STEP 3: CREATE COMPLETE TIMETABLE (MON-FRI, 6 PERIODS)
-- ================================================================

-- Clear existing timetable
DELETE FROM timetable WHERE academic_year = '2025-26';

-- Time slots:
-- Period 1: 09:00-10:00
-- Break: 10:00-10:15 (15 min)
-- Period 2: 10:15-11:15
-- Period 3: 11:15-12:15
-- Lunch: 12:15-13:00 (45 min)
-- Period 4: 13:00-14:00
-- Period 5: 14:00-15:00
-- Period 6: 15:00-16:00

-- ================================================================
-- MONDAY TIMETABLE (BSc IT Semester 1)
-- ================================================================
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course, 1, 'Monday', 1, @it101, @teacher1_id, '2025-26', '09:00:00', '10:00:00'),
-- Break 10:00-10:15
(@it_course, 1, 'Monday', 2, @it103, @teacher3_id, '2025-26', '10:15:00', '11:15:00'),
(@it_course, 1, 'Monday', 3, @it102, @teacher2_id, '2025-26', '11:15:00', '12:15:00'),
-- Lunch 12:15-13:00
(@it_course, 1, 'Monday', 4, @it104, @teacher4_id, '2025-26', '13:00:00', '14:00:00'),
(@it_course, 1, 'Monday', 5, @it105, @teacher5_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course, 1, 'Monday', 6, @it101, @teacher1_id, '2025-26', '15:00:00', '16:00:00');

-- ================================================================
-- TUESDAY TIMETABLE (BSc IT Semester 1)
-- ================================================================
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course, 1, 'Tuesday', 1, @it102, @teacher2_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course, 1, 'Tuesday', 2, @it101, @teacher1_id, '2025-26', '10:15:00', '11:15:00'),
(@it_course, 1, 'Tuesday', 3, @it104, @teacher4_id, '2025-26', '11:15:00', '12:15:00'),
(@it_course, 1, 'Tuesday', 4, @it103, @teacher3_id, '2025-26', '13:00:00', '14:00:00'),
(@it_course, 1, 'Tuesday', 5, @it102, @teacher2_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course, 1, 'Tuesday', 6, @it105, @teacher5_id, '2025-26', '15:00:00', '16:00:00');

-- ================================================================
-- WEDNESDAY TIMETABLE (BSc IT Semester 1)
-- ================================================================
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course, 1, 'Wednesday', 1, @it103, @teacher3_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course, 1, 'Wednesday', 2, @it104, @teacher4_id, '2025-26', '10:15:00', '11:15:00'),
(@it_course, 1, 'Wednesday', 3, @it105, @teacher5_id, '2025-26', '11:15:00', '12:15:00'),
(@it_course, 1, 'Wednesday', 4, @it101, @teacher1_id, '2025-26', '13:00:00', '14:00:00'),
(@it_course, 1, 'Wednesday', 5, @it103, @teacher3_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course, 1, 'Wednesday', 6, @it102, @teacher2_id, '2025-26', '15:00:00', '16:00:00');

-- ================================================================
-- THURSDAY TIMETABLE (BSc IT Semester 1)
-- ================================================================
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course, 1, 'Thursday', 1, @it104, @teacher4_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course, 1, 'Thursday', 2, @it105, @teacher5_id, '2025-26', '10:15:00', '11:15:00'),
(@it_course, 1, 'Thursday', 3, @it101, @teacher1_id, '2025-26', '11:15:00', '12:15:00'),
(@it_course, 1, 'Thursday', 4, @it102, @teacher2_id, '2025-26', '13:00:00', '14:00:00'),
(@it_course, 1, 'Thursday', 5, @it104, @teacher4_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course, 1, 'Thursday', 6, @it103, @teacher3_id, '2025-26', '15:00:00', '16:00:00');

-- ================================================================
-- FRIDAY TIMETABLE (BSc IT Semester 1)
-- ================================================================
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course, 1, 'Friday', 1, @it101, @teacher1_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course, 1, 'Friday', 2, @it103, @teacher3_id, '2025-26', '10:15:00', '11:15:00'),
(@it_course, 1, 'Friday', 3, @it102, @teacher2_id, '2025-26', '11:15:00', '12:15:00'),
(@it_course, 1, 'Friday', 4, @it105, @teacher5_id, '2025-26', '13:00:00', '14:00:00'),
(@it_course, 1, 'Friday', 5, @it104, @teacher4_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course, 1, 'Friday', 6, @it101, @teacher1_id, '2025-26', '15:00:00', '16:00:00');

SELECT 'Complete timetable created successfully!' AS status;

-- ================================================================
-- VERIFICATION
-- ================================================================

SELECT '=== TIMETABLE SUMMARY ===' AS '';
SELECT 
    day_of_week, 
    COUNT(*) as total_periods,
    GROUP_CONCAT(CONCAT('P', period_number) ORDER BY period_number) as periods
FROM timetable 
WHERE academic_year = '2025-26'
GROUP BY day_of_week 
ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');

SELECT '=== TEACHER WORKLOAD ===' AS '';
SELECT 
    u.name as teacher_name,
    COUNT(DISTINCT t.id) as total_periods_per_week,
    GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') as subjects
FROM users u
JOIN timetable t ON u.id = t.teacher_id
JOIN subjects s ON t.subject_id = s.id
WHERE u.role = 'teacher' AND t.academic_year = '2025-26'
GROUP BY u.id, u.name
ORDER BY u.name;

SELECT '=== SUBJECT DISTRIBUTION ===' AS '';
SELECT 
    s.subject_name,
    COUNT(*) as periods_per_week,
    u.name as teacher
FROM timetable t
JOIN subjects s ON t.subject_id = s.id
JOIN users u ON t.teacher_id = u.id
WHERE t.academic_year = '2025-26'
GROUP BY s.id, s.subject_name, u.name
ORDER BY s.subject_name;

SELECT 'Setup complete! All 5 teachers assigned, complete timetable created.' AS final_status;
