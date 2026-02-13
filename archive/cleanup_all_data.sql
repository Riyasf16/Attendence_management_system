-- ================================================================
-- CLEANUP SCRIPT - Run this BEFORE importing complete_full_attendance_CORRECTED.sql
-- WARNING: This will delete ALL existing data from the database!
-- ================================================================

-- Disable foreign key checks temporarily to allow deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Delete ALL existing attendance records
DELETE FROM attendance;

-- Delete ALL existing students
DELETE FROM students;

-- Delete ALL existing classes
DELETE FROM classes;

-- Delete ALL existing teacher subject assignments
DELETE FROM teacher_subjects;

-- Delete ALL existing users (teachers and students)
DELETE FROM users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto-increment counters for clean IDs
ALTER TABLE attendance AUTO_INCREMENT = 1;
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE teacher_subjects AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

-- ================================================================
-- Cleanup complete! Now you can import complete_full_attendance_CORRECTED.sql
-- ================================================================
