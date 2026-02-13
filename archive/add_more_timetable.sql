-- Add More Timetable Data
-- This adds classes for Wednesday, Thursday, and Friday

USE attendance_db;

-- Get IDs we need
SET @teacher_id = (SELECT id FROM users WHERE username = 'teacher1' LIMIT 1);
SET @it_course_id = (SELECT id FROM courses WHERE course_code = 'BSC_IT');
SET @cs_course_id = (SELECT id FROM courses WHERE course_code = 'BSC_CS');

SET @it101 = (SELECT id FROM subjects WHERE subject_code = 'IT101');
SET @it102 = (SELECT id FROM subjects WHERE subject_code = 'IT102');
SET @it103 = (SELECT id FROM subjects WHERE subject_code = 'IT103');
SET @it104 = (SELECT id FROM subjects WHERE subject_code = 'IT104');
SET @it105 = (SELECT id FROM subjects WHERE subject_code = 'IT105');

SET @cs101 = (SELECT id FROM subjects WHERE subject_code = 'CS101');
SET @cs102 = (SELECT id FROM subjects WHERE subject_code = 'CS102');
SET @cs103 = (SELECT id FROM subjects WHERE subject_code = 'CS103');

-- Wednesday Schedule (BSc IT Semester 1)
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Wednesday', 1, @it103, @teacher_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course_id, 1, 'Wednesday', 2, @it104, @teacher_id, '2025-26', '10:00:00', '11:00:00'),
(@it_course_id, 1, 'Wednesday', 3, @it105, @teacher_id, '2025-26', '11:00:00', '12:00:00'),
(@it_course_id, 1, 'Wednesday', 4, @it101, @teacher_id, '2025-26', '12:00:00', '13:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Thursday Schedule (BSc IT Semester 1)
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Thursday', 1, @it104, @teacher_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course_id, 1, 'Thursday', 2, @it105, @teacher_id, '2025-26', '10:00:00', '11:00:00'),
(@it_course_id, 1, 'Thursday', 3, @it102, @teacher_id, '2025-26', '11:00:00', '12:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Friday Schedule (BSc IT Semester 1)
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Friday', 1, @it101, @teacher_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course_id, 1, 'Friday', 2, @it103, @teacher_id, '2025-26', '10:00:00', '11:00:00'),
(@it_course_id, 1, 'Friday', 3, @it102, @teacher_id, '2025-26', '11:00:00', '12:00:00'),
(@it_course_id, 1, 'Friday', 4, @it104, @teacher_id, '2025-26', '12:00:00', '13:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Add more periods to Monday and Tuesday as well
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Monday', 4, @it104, @teacher_id, '2025-26', '12:00:00', '13:00:00'),
(@it_course_id, 1, 'Monday', 5, @it105, @teacher_id, '2025-26', '14:00:00', '15:00:00'),
(@it_course_id, 1, 'Tuesday', 3, @it103, @teacher_id, '2025-26', '11:00:00', '12:00:00'),
(@it_course_id, 1, 'Tuesday', 4, @it104, @teacher_id, '2025-26', '12:00:00', '13:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Verify
SELECT 'Timetable data added successfully!' AS status;
SELECT day_of_week, COUNT(*) as periods FROM timetable GROUP BY day_of_week ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
