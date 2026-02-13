<?php
// Test script to verify recent attendance query
require_once 'config.php';

// Login as a student explicitly for testing
// We'll use student ID 20 (Suresh Kumar) who we know exists
$student_id = 20;

echo "<h3>Testing Recent Attendance Query for Student ID: $student_id</h3>";

$conn = getDBConnection();

// The query from student_dashboard.php
$sql = "SELECT 
        a.date,
        a.period_number,
        a.status,
        a.day_of_week,
        s.subject_name
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC, a.period_number DESC
    LIMIT 100";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo "Query executed successfully.<br>";
echo "Number of records found: " . count($records) . "<br><br>";

if (count($records) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Date</th><th>Period</th><th>Subject</th><th>Status</th></tr>";
    $count = 0;
    foreach ($records as $row) {
        // Show first 5 and last 5 only
        if ($count < 5 || $count >= count($records) - 5) {
            echo "<tr>";
            echo "<td>" . $row['date'] . "</td>";
            echo "<td>" . $row['period_number'] . "</td>";
            echo "<td>" . $row['subject_name'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        } else if ($count == 5) {
            echo "<tr><td colspan='4'>... (skipping " . (count($records) - 10) . " records) ...</td></tr>";
        }
        $count++;
    }
    echo "</table>";
} else {
    echo "No records found.";
}
?>
