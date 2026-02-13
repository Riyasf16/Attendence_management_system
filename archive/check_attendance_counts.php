<?php
// Check attendance counts per student
require_once 'config.php';

$conn = getDBConnection();

echo "<h3>Attendance Records Per Student</h3>";
echo "<table border='1'><tr><th>Student ID</th><th>Name</th><th>Record Count</th></tr>";

$sql = "SELECT s.id, s.name, COUNT(a.id) as count 
        FROM students s 
        LEFT JOIN attendance a ON s.id = a.student_id 
        GROUP BY s.id 
        ORDER BY count DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td><strong>" . $row['count'] . "</strong></td>";
        echo "</tr>";
    }
} else {
    echo "Error: " . $conn->error;
}
echo "</table>";

$conn->close();
?>
