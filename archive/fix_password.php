<?php
require_once 'config.php';

echo "<h2>Fix All Passwords</h2>";

$conn = getDBConnection();

// Update password for ALL teachers
$teacher_hash = password_hash('teacher123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE role = 'teacher'");
$stmt->bind_param("s", $teacher_hash);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo "✅ Updated passwords for <strong>$affected teachers</strong><br>";
    echo "All teachers can now login with password: <strong>teacher123</strong><br><br>";
    
    // List all teachers
    echo "<h3>Teacher Accounts:</h3>";
    $teachers = $conn->query("SELECT username, name FROM users WHERE role = 'teacher' ORDER BY username");
    while ($teacher = $teachers->fetch_assoc()) {
        echo "👨‍🏫 <strong>" . htmlspecialchars($teacher['username']) . "</strong> (" . htmlspecialchars($teacher['name']) . ")<br>";
    }
} else {
    echo "❌ Error updating teacher passwords: " . $conn->error;
}
$stmt->close();

echo "<br><hr><br>";

// Update password for ALL students
$student_hash = password_hash('student123', PASSWORD_DEFAULT);
$stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE role = 'student'");
$stmt2->bind_param("s", $student_hash);

if ($stmt2->execute()) {
    $affected = $stmt2->affected_rows;
    echo "✅ Updated passwords for <strong>$affected students</strong><br>";
    echo "All students can now login with password: <strong>student123</strong><br>";
} else {
    echo "❌ Error updating student passwords: " . $conn->error;
}
$stmt2->close();

$conn->close();

echo "<br><hr><br>";
echo "<h3>✅ All Done!</h3>";
echo "<p><a href='teacher_login.php' class='btn btn-primary'>Go to Teacher Login</a></p>";
echo "<p><a href='student_login.php' class='btn btn-primary'>Go to Student Login</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
h2 { color: #333; }
h3 { color: #666; margin-top: 20px; }
.btn { 
    display: inline-block;
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 5px;
}
.btn:hover { background: #5568d3; }
hr { margin: 20px 0; border: none; border-top: 1px solid #ddd; }
</style>
