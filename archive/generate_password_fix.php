<?php
// Generate correct password hashes for teacher123 and student123
$teacher_password = 'teacher123';
$student_password = 'student123';

$teacher_hash = password_hash($teacher_password, PASSWORD_DEFAULT);
$student_hash = password_hash($student_password, PASSWORD_DEFAULT);

echo "-- SQL to fix password hashes\n";
echo "-- This updates all teacher passwords to 'teacher123'\n";
echo "-- And all student passwords to 'student123'\n\n";

echo "-- Teacher password hash for 'teacher123':\n";
echo "UPDATE users SET password = '$teacher_hash' WHERE role = 'teacher';\n\n";

echo "-- Student password hash for 'student123':\n";
echo "UPDATE users SET password = '$student_hash' WHERE role = 'student';\n\n";

echo "-- Done! All passwords have been updated.\n";
?>
