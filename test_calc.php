<?php
// Test script to verify the new attendance calculation logic
require_once 'config.php';

// Create a connection
$conn = getDBConnection();

// Cleanup previous test data if exists
$conn->query("DELETE FROM attendance WHERE student_id IN (SELECT id FROM students WHERE roll_number = 'TS001')");
$conn->query("DELETE FROM students WHERE roll_number = 'TS001'");
$conn->query("DELETE FROM subjects WHERE subject_code = 'TS101'");
$conn->query("DELETE FROM courses WHERE course_code = 'TC101'");
$conn->query("DELETE FROM users WHERE username = 'test_student'");

// Create a dummy user and student for testing
$conn->query("INSERT INTO users (username, password, role) VALUES ('test_student', 'password', 'student')");
$user_id = $conn->insert_id;
$conn->query("INSERT INTO courses (course_name, course_code) VALUES ('Test Course', 'TC101')");
$course_id = $conn->insert_id;
$conn->query("INSERT INTO students (user_id, name, roll_number, course_id, semester) VALUES ($user_id, 'Test Student', 'TS001', $course_id, 1)");
if ($conn->error) echo "Error inserting student: " . $conn->error . "\n";
$student_id = $conn->insert_id;
// Create a subject
$conn->query("INSERT INTO subjects (subject_name, subject_code, course_id, semester) VALUES ('Test Subject', 'TS101', $course_id, 1)");
if ($conn->error) echo "Error inserting subject: " . $conn->error . "\n";
$subject_id = $conn->insert_id;

echo "Created test student ID: $student_id\n";

// Helper function to insert attendance
function addAttendance($conn, $student_id, $subject_id, $date, $period, $status) {
    $stmt = $conn->prepare("INSERT INTO attendance (student_id, subject_id, date, period_number, status, marked_by) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("iisis", $student_id, $subject_id, $date, $period, $status);
    $stmt->execute();
}

// Case 1: Full Day (Absent 0)
// Date 2025-01-01: 5 periods, 5 present
$d1 = '2025-01-01';
addAttendance($conn, $student_id, $subject_id, $d1, 1, 'present');
addAttendance($conn, $student_id, $subject_id, $d1, 2, 'present');
addAttendance($conn, $student_id, $subject_id, $d1, 3, 'present');
addAttendance($conn, $student_id, $subject_id, $d1, 4, 'present');
addAttendance($conn, $student_id, $subject_id, $d1, 5, 'present');

// Case 2: Full Day (Absent 1)
// Date 2025-01-02: 5 periods, 4 present, 1 absent
$d2 = '2025-01-02';
addAttendance($conn, $student_id, $subject_id, $d2, 1, 'present');
addAttendance($conn, $student_id, $subject_id, $d2, 2, 'present');
addAttendance($conn, $student_id, $subject_id, $d2, 3, 'absent');
addAttendance($conn, $student_id, $subject_id, $d2, 4, 'present');
addAttendance($conn, $student_id, $subject_id, $d2, 5, 'present');

// Case 3: Half Day (Absent 2)
// Date 2025-01-03: 5 periods, 3 present, 2 absent
$d3 = '2025-01-03';
addAttendance($conn, $student_id, $subject_id, $d3, 1, 'present');
addAttendance($conn, $student_id, $subject_id, $d3, 2, 'absent');
addAttendance($conn, $student_id, $subject_id, $d3, 3, 'absent');
addAttendance($conn, $student_id, $subject_id, $d3, 4, 'present');
addAttendance($conn, $student_id, $subject_id, $d3, 5, 'present');

// Case 4: Absent (Absent > 2)
// Date 2025-01-04: 5 periods, 2 present, 3 absent
$d4 = '2025-01-04';
addAttendance($conn, $student_id, $subject_id, $d4, 1, 'present');
addAttendance($conn, $student_id, $subject_id, $d4, 2, 'absent');
addAttendance($conn, $student_id, $subject_id, $d4, 3, 'absent');
addAttendance($conn, $student_id, $subject_id, $d4, 4, 'absent');
addAttendance($conn, $student_id, $subject_id, $d4, 5, 'present');

// Calculate Stats using the new logic
$sql = "SELECT 
        COUNT(*) as total_days,
        SUM(daily_score) as present_days,
        ROUND(SUM(daily_score) * 100.0 / COUNT(*), 2) as percentage
    FROM (
        SELECT 
            date,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as periods_present,
            COUNT(*) as total_periods,
            (COUNT(*) - SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END)) as periods_absent,
            CASE 
                WHEN (COUNT(*) - SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END)) <= 1 THEN 1.0
                WHEN (COUNT(*) - SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END)) = 2 THEN 0.5
                ELSE 0.0
            END as daily_score
        FROM attendance
        WHERE student_id = ?
        GROUP BY date
    ) as daily_stats";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo "\nResults:\n";
echo "Total Days: " . $result['total_days'] . " (Expected: 4)\n";
echo "Present Days: " . $result['present_days'] . " (Expected: 1 + 1 + 0.5 + 0 = 2.5)\n";
echo "Percentage: " . $result['percentage'] . "% (Expected: 62.5%)\n";

// Cleanup
$conn->query("DELETE FROM attendance WHERE student_id = $student_id");
$conn->query("DELETE FROM students WHERE id = $student_id");
$conn->query("DELETE FROM courses WHERE id = $course_id");
$conn->query("DELETE FROM users WHERE id = $user_id");
$conn->query("DELETE FROM subjects WHERE id = $subject_id");

$conn->close();
?>
