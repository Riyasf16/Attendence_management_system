# Database Export Guide

## Method 1: Export Using phpMyAdmin (Recommended)

### Steps:
1. Open **phpMyAdmin**: `http://localhost/phpmyadmin/`

2. Click on **`attendance_db`** in the left sidebar

3. Click the **Export** tab at the top

4. Select export method:
   - **Quick** - for simple export with default settings
   - **Custom** - for advanced options (recommended)

5. If you chose **Custom**:
   - Format: **SQL**
   - Tables: Select all (or choose specific tables)
   - Check these options:
     - ✅ Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER
     - ✅ Add CREATE TABLE
     - ✅ Enclose table and column names with backticks
     - ✅ Complete inserts (better for readability)
     - ✅ Add CREATE DATABASE / USE statement

6. Click **Go** button at the bottom

7. File will download as: `attendance_db.sql`

---

## Method 2: Export Using Command Line

If you have MySQL command line access:

```bash
# Navigate to MySQL bin directory
cd C:\xampp\mysql\bin

# Export database
mysqldump -u root -p attendance_db > C:\Users\Pratyush\Attendenece_management_system\Attendence_management_system\database_backup.sql

# Enter password when prompted (press Enter if no password)
```

---

## Method 3: Use the Backup Script

I've created `backup_database.php` for you. Run it:

`http://localhost/Attendenece_management_system/Attendence_management_system/backup_database.php`

This will create a backup file with timestamp in the same directory.

---

## What Will Be Exported

The export will include:

### Tables Structure + Data:
- ✅ `users` (all teachers and students)
- ✅ `courses` (BSc IT, BSc CS)
- ✅ `subjects` (all 10 subjects)
- ✅ `teacher_subjects` (teacher-subject assignments)
- ✅ `students` (all enrolled students)
- ✅ `timetable` (complete Mon-Fri schedule)
- ✅ `attendance` (all attendance records)

### What's Exported:
- All 5 teachers with correct passwords
- Complete timetable (Mon-Fri, 6 periods/day)
- All students in the system
- All attendance records marked so far

---

## Recommended Export Settings for Future Use

If you want a clean SQL file to share or deploy:

1. Use **Custom** export
2. Enable:
   - ✅ DROP TABLE (to replace existing tables)
   - ✅ CREATE DATABASE (so it creates the DB if not exists)
   - ✅ Complete inserts (easier to read)
   - ✅ Add comments (helpful for understanding)

This creates a **complete, portable SQL file** that can be imported on any MySQL server!
