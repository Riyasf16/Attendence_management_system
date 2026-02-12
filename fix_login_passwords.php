<?php
// fix_login_passwords.php
// This script updates all passwords in the database to match the documentation

require_once 'config.php';

// Generate safe hashes
$teacher_pass = 'teacher123';
$student_pass = 'student123';

$teacher_hash = password_hash($teacher_pass, PASSWORD_DEFAULT);
$student_hash = password_hash($student_pass, PASSWORD_DEFAULT);

echo "=== FIXING LOGIN PASSWORDS ===\n";

$conn = getDBConnection();

// 1. Update Teachers
$stmt1 = $conn->prepare("UPDATE users SET password = ? WHERE role = 'teacher'");
$stmt1->bind_param("s", $teacher_hash);

if ($stmt1->execute()) {
    echo "✅ Success: Updated " . $stmt1->affected_rows . " teacher accounts.\n";
    echo "   New Password: '$teacher_pass'\n";
} else {
    echo "❌ Error updating teachers: " . $conn->error . "\n";
}

// 2. Update Students
$stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE role = 'student'");
$stmt2->bind_param("s", $student_hash);

if ($stmt2->execute()) {
    echo "✅ Success: Updated " . $stmt2->affected_rows . " student accounts.\n";
    echo "   New Password: '$student_pass'\n";
} else {
    echo "❌ Error updating students: " . $conn->error . "\n";
}

// Verify one result
$result = $conn->query("SELECT username, password FROM users WHERE username = 'teacher1'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $verify = password_verify($teacher_pass, $row['password']);
    echo "\nVerification Test (teacher1):\n";
    echo "   Login with '$teacher_pass' -> " . ($verify ? "SUCCESS ✅" : "FAILED ❌") . "\n";
}

$conn->close();
?>
