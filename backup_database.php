<?php
/**
 * Database Backup Script
 * This script exports the entire attendance_db database to a SQL file
 */

require_once 'config.php';

// Set headers for download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="attendance_db_backup_' . date('Y-m-d_H-i-s') . '.sql"');

$conn = getDBConnection();

echo "-- ================================================================\n";
echo "-- Attendance Management System - Database Backup\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- ================================================================\n\n";

echo "CREATE DATABASE IF NOT EXISTS `attendance_db`;\n";
echo "USE `attendance_db`;\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Export each table
foreach ($tables as $table) {
    echo "-- ================================================================\n";
    echo "-- Table: $table\n";
    echo "-- ================================================================\n\n";
    
    // Drop table statement
    echo "DROP TABLE IF EXISTS `$table`;\n\n";
    
    // Get CREATE TABLE statement
    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create_result->fetch_array();
    echo $create_row[1] . ";\n\n";
    
    // Get table data
    $data_result = $conn->query("SELECT * FROM `$table`");
    
    if ($data_result->num_rows > 0) {
        echo "-- Dumping data for table `$table`\n\n";
        
        while ($row = $data_result->fetch_assoc()) {
            $columns = array_keys($row);
            $values = array_values($row);
            
            // Escape values
            $escaped_values = array_map(function($value) use ($conn) {
                if ($value === null) {
                    return 'NULL';
                }
                return "'" . $conn->real_escape_string($value) . "'";
            }, $values);
            
            $columns_str = '`' . implode('`, `', $columns) . '`';
            $values_str = implode(', ', $escaped_values);
            
            echo "INSERT INTO `$table` ($columns_str) VALUES ($values_str);\n";
        }
        echo "\n";
    } else {
        echo "-- No data for table `$table`\n\n";
    }
}

echo "-- ================================================================\n";
echo "-- Backup completed successfully!\n";
echo "-- ================================================================\n";

$conn->close();
?>
