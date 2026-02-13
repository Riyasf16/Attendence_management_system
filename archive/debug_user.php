<?php
require_once 'config.php';

echo "<h2>Debug User Login</h2>";

$conn = getDBConnection();

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    die("❌ Error: 'users' table does not exist. Please import setup_database.sql");
}
echo "✅ Table 'users' exists.<br>";

// Check if teacher1 exists
$username = 'teacher1';
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✅ User '$username' found.<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "Stored Hash: " . $user['password'] . "<br>";
    
    // Test password
    $test_pass = 'teacher123';
    if (password_verify($test_pass, $user['password'])) {
        echo "✅ Password '$test_pass' matches the stored hash.<br>";
    } else {
        echo "❌ Password '$test_pass' does NOT match the stored hash.<br>";
        echo "Generating new hash for '$test_pass': " . password_hash($test_pass, PASSWORD_DEFAULT) . "<br>";
    }
} else {
    echo "❌ User '$username' NOT found in database.<br>";
    
    // List all users
    echo "<h3>Listing all users in DB:</h3>";
    $all = $conn->query("SELECT id, username, role FROM users");
    if ($all->num_rows > 0) {
        while ($row = $all->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | User: " . $row['username'] . " | Role: " . $row['role'] . "<br>";
        }
    } else {
        echo "Table is empty.";
    }
}
?>
