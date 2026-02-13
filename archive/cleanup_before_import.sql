-- ================================================================
-- CLEANUP SCRIPT: Run this FIRST before importing complete_full_attendance.sql
-- This removes any existing sample data to prevent duplicate key errors
-- ================================================================

-- Remove existing attendance records for January-February 2026
DELETE FROM attendance WHERE date BETWEEN '2026-01-01' AND '2026-02-12';

-- Remove existing student records with roll numbers IT2026001-IT2026020
DELETE FROM students WHERE roll_number LIKE 'IT2026%';

-- Remove existing teacher-subject assignments for sample teachers
DELETE FROM teacher_subjects WHERE teacher_id IN (10, 11, 12, 13, 14);

-- Remove existing user accounts (teachers and students)
DELETE FROM users WHERE id >= 10 AND id <= 39;

-- ================================================================
-- Success! Now you can import complete_full_attendance.sql
-- ================================================================

SELECT 'Cleanup completed! Ready to import new data.' AS status;
