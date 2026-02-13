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

echo "=== DATABASE STATUS CHECK ===\n\n";

// Check users
echo "--- USERS TABLE ---\n";
$result = $conn->query("SELECT id, username, role, name FROM users ORDER BY id");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        printf("ID: %-3s | Username: %-15s | Role: %-10s | Name: %s\n", 
            $row['id'], $row['username'], $row['role'], $row['name']);
    }
    echo "Total users: " . $result->num_rows . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check students
echo "--- STUDENTS TABLE ---\n";
$result = $conn->query("SELECT COUNT(*) as count FROM students");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total students: " . $row['count'] . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check classes
echo "--- CLASSES TABLE ---\n";
$result = $conn->query("SELECT * FROM classes");
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            printf("ID: %s | Name: %s | Teacher ID: %s\n", 
                $row['id'], $row['class_name'], $row['teacher_id']);
        }
    } else {
        echo "NO DATA\n";
    }
    echo "Total classes: " . $result->num_rows . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check courses
echo "--- COURSES TABLE ---\n";
$result = $conn->query("SELECT * FROM courses");
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            printf("ID: %s | Code: %s | Name: %s\n", 
                $row['id'], $row['course_code'], $row['course_name']);
        }
    } else {
        echo "NO DATA\n";
    }
    echo "Total courses: " . $result->num_rows . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check subjects
echo "--- SUBJECTS TABLE ---\n";
$result = $conn->query("SELECT * FROM subjects ORDER BY id");
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            printf("ID: %s | Code: %s | Name: %-30s | Course: %s | Semester: %s\n", 
                $row['id'], $row['subject_code'], $row['subject_name'], 
                $row['course_id'], $row['semester']);
        }
    } else {
        echo "NO DATA\n";
    }
    echo "Total subjects: " . $result->num_rows . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check teacher_subjects
echo "--- TEACHER_SUBJECTS TABLE ---\n";
$result = $conn->query("SELECT * FROM teacher_subjects");
if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            printf("ID: %s | Teacher: %s | Subject: %s | Year: %s\n", 
                $row['id'], $row['teacher_id'], $row['subject_id'], $row['academic_year']);
        }
    } else {
        echo "NO DATA\n";
    }
    echo "Total teacher-subject assignments: " . $result->num_rows . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check timetable
echo "--- TIMETABLE TABLE ---\n";
$result = $conn->query("SELECT COUNT(*) as count FROM timetable");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total timetable entries: " . $row['count'] . "\n";
    if ($row['count'] == 0) {
        echo "NO DATA\n";
    }
    echo "\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

// Check attendance
echo "--- ATTENDANCE TABLE ---\n";
$result = $conn->query("SELECT COUNT(*) as count FROM attendance");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total attendance records: " . $row['count'] . "\n\n";
} else {
    echo "Error: " . $conn->error . "\n\n";
}

$conn->close();
?>
