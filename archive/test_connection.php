<?php
// Simple test to check MySQL connection without password
echo "<h2>Testing MySQL Connection</h2>";

$conn = new mysqli('localhost', 'root', '');

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error . "<br>");
}

echo "✅ Connected to MySQL successfully!<br>";
echo "Server info: " . $conn->server_info . "<br>";

// Check if database exists
$result = $conn->query("SHOW DATABASES LIKE 'attendance_db'");
if ($result->num_rows > 0) {
    echo "✅ Database 'attendance_db' exists<br>";
} else {
    echo "❌ Database 'attendance_db' NOT found<br>";
    echo "Please import setup_database.sql first!<br>";
}

$conn->close();
?>
