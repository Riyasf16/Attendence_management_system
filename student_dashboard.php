<?php
require_once 'config.php';
require_once 'session.php';

// Require student login
requireLogin('student');

$conn = getDBConnection();
$user_id = getCurrentUserId();
$student_name = getCurrentUserName();

// Get student's enrollment information
$stmt = $conn->prepare("
    SELECT s.id as student_id, s.roll_number, c.id as class_id, c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.id
    WHERE s.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get attendance statistics for each enrollment
$attendance_stats = [];
foreach ($enrollments as $enrollment) {
    $student_id = $enrollment['student_id'];
    
    // Get total attendance records
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
        FROM attendance
        WHERE student_id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $percentage = $stats['total_days'] > 0 
        ? round(($stats['present_days'] / $stats['total_days']) * 100, 2) 
        : 0;
    
    $attendance_stats[$student_id] = [
        'total_days' => $stats['total_days'],
        'present_days' => $stats['present_days'],
        'absent_days' => $stats['absent_days'],
        'percentage' => $percentage
    ];
}

// Get recent attendance records (last 10 days)
$attendance_records = [];
foreach ($enrollments as $enrollment) {
    $student_id = $enrollment['student_id'];
    
    $stmt = $conn->prepare("
        SELECT date, status
        FROM attendance
        WHERE student_id = ?
        ORDER BY date DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $attendance_records[$student_id] = $records;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance Management System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Attendance Management System</h2>
            <div class="nav-right">
                <span class="user-name">Welcome, <?php echo htmlspecialchars($student_name); ?></span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Student Dashboard</h1>
            <p>View your attendance records and statistics</p>
        </div>

        <?php if (count($enrollments) > 0): ?>
            <?php foreach ($enrollments as $enrollment): ?>
                <?php 
                $student_id = $enrollment['student_id'];
                $stats = $attendance_stats[$student_id];
                $records = $attendance_records[$student_id];
                ?>
                
                <div class="card class-section">
                    <div class="class-header">
                        <h2><?php echo htmlspecialchars($enrollment['class_name']); ?></h2>
                        <span class="roll-badge">Roll: <?php echo htmlspecialchars($enrollment['roll_number']); ?></span>
                    </div>

                    <!-- Attendance Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['percentage']; ?>%</h3>
                                <p>Attendance Percentage</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✓</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['present_days']; ?></h3>
                                <p>Days Present</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">✗</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['absent_days']; ?></h3>
                                <p>Days Absent</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📅</div>
                            <div class="stat-content">
                                <h3><?php echo $stats['total_days']; ?></h3>
                                <p>Total Days</p>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Percentage Bar -->
                    <div class="percentage-bar-container">
                        <div class="percentage-label">Overall Attendance</div>
                        <div class="percentage-bar">
                            <div class="percentage-fill" style="width: <?php echo $stats['percentage']; ?>%;">
                                <span class="percentage-text"><?php echo $stats['percentage']; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance Records -->
                    <?php if (count($records) > 0): ?>
                        <h3 class="section-title">Recent Attendance Records</h3>
                        <div class="attendance-table-wrapper">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?php echo date('F j, Y', strtotime($record['date'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $record['status']; ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="no-data">No attendance records yet.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <p class="no-data">You are not enrolled in any classes.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
