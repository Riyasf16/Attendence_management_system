<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'attendance_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Status Check</h2>";
echo "<style>table { border-collapse: collapse; margin: 10px 0; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #4CAF50; color: white; }</style>";

// Check users
echo "<h3>Users Table:</h3>";
$result = $conn->query("SELECT id, username, role, name FROM users ORDER BY id");
if ($result) {
    echo "<table><tr><th>ID</th><th>Username</th><th>Role</th><th>Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['username']}</td><td>{$row['role']}</td><td>{$row['name']}</td></tr>";
    }
    echo "</table>";
    echo "<p><strong>Total users: " . $result->num_rows . "</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check students
echo "<h3>Students Table:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Total students: " . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check classes
echo "<h3>Classes Table:</h3>";
$result = $conn->query("SELECT * FROM classes");
if ($result) {
    echo "<table><tr><th>ID</th><th>Class Name</th><th>Teacher ID</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['class_name']}</td><td>{$row['teacher_id']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check courses
echo "<h3>Courses Table:</h3>";
$result = $conn->query("SELECT * FROM courses");
if ($result) {
    echo "<table><tr><th>ID</th><th>Code</th><th>Name</th></tr>";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['course_code']}</td><td>{$row['course_name']}</td></tr>";
        $count++;
    }
    echo "</table>";
    echo "<p><strong>Total courses: $count</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check subjects
echo "<h3>Subjects Table:</h3>";
$result = $conn->query("SELECT * FROM subjects ORDER BY id");
if ($result) {
    echo "<table><tr><th>ID</th><th>Code</th><th>Name</th><th>Course ID</th><th>Semester</th></tr>";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['subject_code']}</td><td>{$row['subject_name']}</td><td>{$row['course_id']}</td><td>{$row['semester']}</td></tr>";
        $count++;
    }
    echo "</table>";
    echo "<p><strong>Total subjects: $count</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check teacher_subjects
echo "<h3>Teacher Subjects:</h3>";
$result = $conn->query("SELECT * FROM teacher_subjects");
if ($result) {
    echo "<table><tr><th>ID</th><th>Teacher ID</th><th>Subject ID</th><th>Academic Year</th></tr>";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['teacher_id']}</td><td>{$row['subject_id']}</td><td>{$row['academic_year']}</td></tr>";
        $count++;
    }
    echo "</table>";
    echo "<p><strong>Total teacher-subject assignments: $count</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check timetable
echo "<h3>Timetable Table:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM timetable");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Total timetable entries: " . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

// Check attendance
echo "<h3>Attendance Table:</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM attendance");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p><strong>Total attendance records: " . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
}

$conn->close();
?>
