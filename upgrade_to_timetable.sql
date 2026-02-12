-- ================================================================
-- Attendance System Upgrade: Timetable-Based System
-- ================================================================
-- This script upgrades the simple attendance system to support:
-- - Courses (BSc IT, BSc CS)
-- - Subjects per course
-- - Weekly timetables (Mon-Fri, 6 periods)
-- - Period-wise attendance tracking
-- ================================================================

USE attendance_db;

-- ================================================================
-- STEP 1: BACKUP EXISTING DATA
-- ================================================================

CREATE TABLE IF NOT EXISTS backup_classes AS SELECT * FROM classes;
CREATE TABLE IF NOT EXISTS backup_students AS SELECT * FROM students;
CREATE TABLE IF NOT EXISTS backup_attendance AS SELECT * FROM attendance;

SELECT 'Step 1: Backup completed' AS status;

-- ================================================================
-- STEP 2: CREATE NEW TABLES
-- ================================================================

-- Table: courses (BSc IT, BSc CS, etc.)
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    duration_years INT NOT NULL DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_course_code (course_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: subjects (courses/subjects offered)
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    credits INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course_semester (course_id, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: teacher_subjects (teacher assignments)
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL DEFAULT '2025-26',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject_year (teacher_id, subject_id, academic_year),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: timetable (weekly schedule)
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    semester INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    period_number INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL DEFAULT '2025-26',
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (course_id, semester, day_of_week, period_number, academic_year),
    INDEX idx_teacher_schedule (teacher_id, day_of_week, period_number),
    CHECK (period_number BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Step 2: New tables created' AS status;

-- ================================================================
-- STEP 3: MODIFY EXISTING TABLES
-- ================================================================

-- 3A: Modify students table - Add course_id and semester
ALTER TABLE students 
ADD COLUMN course_id INT NULL AFTER user_id,
ADD COLUMN semester INT DEFAULT 1 AFTER course_id;

-- 3B: Create new attendance table structure
CREATE TABLE IF NOT EXISTS attendance_new (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    period_number INT NOT NULL,
    date DATE NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, subject_id, date, period_number),
    INDEX idx_student (student_id),
    INDEX idx_date (date),
    CHECK (period_number BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Step 3: Tables modified' AS status;

-- ================================================================
-- STEP 4: INSERT SAMPLE DATA
-- ================================================================

-- Insert courses
INSERT INTO courses (course_code, course_name, duration_years) VALUES
('BSC_IT', 'BSc Information Technology', 3),
('BSC_CS', 'BSc Computer Science', 3)
ON DUPLICATE KEY UPDATE course_name = VALUES(course_name);

-- Get course IDs
SET @it_course_id = (SELECT id FROM courses WHERE course_code = 'BSC_IT');
SET @cs_course_id = (SELECT id FROM courses WHERE course_code = 'BSC_CS');

-- Insert subjects for BSc IT (Semester 1)
INSERT INTO subjects (subject_code, subject_name, course_id, semester, credits) VALUES
('IT101', 'Programming Fundamentals', @it_course_id, 1, 4),
('IT102', 'Digital Electronics', @it_course_id, 1, 3),
('IT103', 'Mathematics I', @it_course_id, 1, 4),
('IT104', 'Communication Skills', @it_course_id, 1, 3),
('IT105', 'Computer Organization', @it_course_id, 1, 3)
ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name);

-- Insert subjects for BSc CS (Semester 1)
INSERT INTO subjects (subject_code, subject_name, course_id, semester, credits) VALUES
('CS101', 'Introduction to Programming', @cs_course_id, 1, 4),
('CS102', 'Discrete Mathematics', @cs_course_id, 1, 4),
('CS103', 'Computer Fundamentals', @cs_course_id, 1, 3),
('CS104', 'English Communication', @cs_course_id, 1, 3),
('CS105', 'Physics for CS', @cs_course_id, 1, 3)
ON DUPLICATE KEY UPDATE subject_name = VALUES(subject_name);

-- Get subject IDs
SET @it101 = (SELECT id FROM subjects WHERE subject_code = 'IT101');
SET @it102 = (SELECT id FROM subjects WHERE subject_code = 'IT102');
SET @it103 = (SELECT id FROM subjects WHERE subject_code = 'IT103');
SET @it104 = (SELECT id FROM subjects WHERE subject_code = 'IT104');
SET @it105 = (SELECT id FROM subjects WHERE subject_code = 'IT105');

SET @cs101 = (SELECT id FROM subjects WHERE subject_code = 'CS101');
SET @cs102 = (SELECT id FROM subjects WHERE subject_code = 'CS102');

-- Get teacher ID
SET @teacher_id = (SELECT id FROM users WHERE username = 'teacher1' LIMIT 1);

-- Assign subjects to teacher
INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year) VALUES
(@teacher_id, @it101, '2025-26'),
(@teacher_id, @it102, '2025-26'),
(@teacher_id, @it103, '2025-26'),
(@teacher_id, @cs101, '2025-26')
ON DUPLICATE KEY UPDATE academic_year = VALUES(academic_year);

-- Create timetable for BSc IT Semester 1 (Monday)
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Monday', 1, @it101, @teacher_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course_id, 1, 'Monday', 2, @it102, @teacher_id, '2025-26', '10:00:00', '11:00:00'),
(@it_course_id, 1, 'Monday', 3, @it103, @teacher_id, '2025-26', '11:00:00', '12:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Create timetable for BSc IT Semester 1 (Tuesday)
INSERT INTO timetable (course_id, semester, day_of_week, period_number, subject_id, teacher_id, academic_year, start_time, end_time) VALUES
(@it_course_id, 1, 'Tuesday', 1, @it102, @teacher_id, '2025-26', '09:00:00', '10:00:00'),
(@it_course_id, 1, 'Tuesday', 2, @it101, @teacher_id, '2025-26', '10:00:00', '11:00:00')
ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id);

-- Update students to assign them to courses
UPDATE students SET 
    course_id = @it_course_id,
    semester = 1
WHERE id <= 3;

UPDATE students SET 
    course_id = @cs_course_id,
    semester = 1
WHERE id > 3;

-- Add foreign key constraint to students table (now that course_id is populated)
ALTER TABLE students 
ADD CONSTRAINT fk_student_course 
FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE;

SELECT 'Step 4: Sample data inserted' AS status;

-- ================================================================
-- STEP 5: CLEANUP
-- ================================================================

-- Drop old attendance table and rename new one
DROP TABLE IF EXISTS attendance;
RENAME TABLE attendance_new TO attendance;

-- Drop old classes table (data backed up)
DROP TABLE IF EXISTS classes;

SELECT 'Step 5: Cleanup completed' AS status;

-- ================================================================
-- VERIFICATION
-- ================================================================

SELECT '=== VERIFICATION ===' AS '';
SELECT CONCAT('Courses: ', COUNT(*)) AS result FROM courses;
SELECT CONCAT('Subjects: ', COUNT(*)) AS result FROM subjects;
SELECT CONCAT('Teacher Assignments: ', COUNT(*)) AS result FROM teacher_subjects;
SELECT CONCAT('Timetable Entries: ', COUNT(*)) AS result FROM timetable;
SELECT CONCAT('Students Updated: ', COUNT(*)) AS result FROM students WHERE course_id IS NOT NULL;

SELECT 'Database upgrade completed successfully!' AS status;
