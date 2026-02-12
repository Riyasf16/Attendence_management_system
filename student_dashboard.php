<?php
require_once 'config.php';
require_once 'session.php';

// Require student login
requireLogin('student');

$conn = getDBConnection();
$user_id = getCurrentUserId();
$student_name = getCurrentUserName();

// Get student information
$stmt = $conn->prepare("SELECT s.id as student_id, s.roll_number, s.course_id, s.semester, c.course_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.user_id = ?
    LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student_data) {
    // Student not enrolled yet
    $student_id = null;
    $roll_number = 'N/A';
    $course_name = 'Not Enrolled';
    $semester = 0;
    $subject_stats = [];
    $overall_stats = ['total' => 0, 'present' => 0, 'percentage' => 0];
    $recent_records = [];
} else {
    $student_id = $student_data['student_id'];
    $roll_number = $student_data['roll_number'];
    $course_name = $student_data['course_name'];
    $semester = $student_data['semester'];
    
    // Get subject-wise attendance statistics
    $stmt = $conn->prepare("SELECT 
        s.subject_name,
        s.subject_code,
        COUNT(*) as total_classes,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        ROUND(SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as percentage
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    GROUP BY s.id, s.subject_name, s.subject_code
    ORDER BY s.subject_name");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $subject_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get overall attendance statistics
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        ROUND(SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as percentage
    FROM attendance
    WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $overall_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Set defaults if no attendance yet
    if ($overall_stats['total'] == 0) {
        $overall_stats['percentage'] = 0;
    }
    
    // Get recent attendance records
    $stmt = $conn->prepare("SELECT 
        a.date,
        a.period_number,
        a.status,
        a.day_of_week,
        s.subject_name,
        s.subject_code
    FROM attendance a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC, a.period_number DESC
    LIMIT 100");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $recent_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance Management System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">

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
            <p>Track your attendance and performance</p>
        </div>

        <?php if ($student_id): ?>
            <div class="student-header">
                <h2><?php echo htmlspecialchars($student_name); ?></h2>
                <div class="student-info">
                    <span class="info-badge">📝 Roll: <?php echo htmlspecialchars($roll_number); ?></span>
                    <span class="info-badge">🎓 <?php echo htmlspecialchars($course_name); ?></span>
                    <span class="info-badge">📖 Semester <?php echo $semester; ?></span>
                </div>
            </div>

            <!-- Overall Attendance -->
            <div class="overall-section">
                <h2>Overall Attendance</h2>
                <div class="overall-percentage"><?php echo $overall_stats['percentage']; ?>%</div>
                <div><?php echo $overall_stats['present']; ?> / <?php echo $overall_stats['total']; ?> classes attended</div>
                <?php if ($overall_stats['percentage'] >= 75): ?>
                    <div style="margin-top: 15px; font-size: 1.1em;">✓ You meet the minimum 75% requirement</div>
                <?php else: ?>
                    <div style="margin-top: 15px; font-size: 1.1em;">⚠️ Below minimum 75% requirement</div>
                <?php endif; ?>
            </div>

            <!-- Subject-wise Attendance -->
            <div class="card">
                <h2>Subject-wise Attendance</h2>
                <?php if (count($subject_stats) > 0): ?>
                    <?php foreach ($subject_stats as $subject): ?>
                        <?php 
                        $perc_class = $subject['percentage'] >= 75 ? 'percentage-good' : 
                                     ($subject['percentage'] >= 60 ? 'percentage-medium' : 'percentage-low');
                        ?>
                        <div class="subject-card">
                            <div class="subject-header">
                                <div>
                                    <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                    <div style="color: #666; margin-top: 5px;">Code: <?php echo htmlspecialchars($subject['subject_code']); ?></div>
                                </div>
                                <div class="percentage-circle <?php echo $perc_class; ?>">
                                    <?php echo $subject['percentage']; ?>%
                                </div>
                            </div>
                            <div class="attendance-detail">
                                <span>📊 <?php echo $subject['present_count']; ?> / <?php echo $subject['total_classes']; ?> classes</span>
                                <span>✓ <?php echo $subject['present_count']; ?> Present</span>
                                <span>✗ <?php echo ($subject['total_classes'] - $subject['present_count']); ?> Absent</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No attendance records yet. Attendance will appear once your teachers mark it.</p>
                <?php endif; ?>
            </div>

            <!-- Recent Attendance -->
            <?php if (count($recent_records) > 0): ?>
                <div class="card">
                    <h2>Recent Attendance Records</h2>
                    <div id="scrollable-attendance-table" class="attendance-table-wrapper">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Period</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_records as $record): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                        <td><?php echo $record['day_of_week']; ?></td>
                                        <td><span class="period-badge">Period <?php echo $record['period_number']; ?></span></td>
                                        <td><?php echo htmlspecialchars($record['subject_name']); ?></td>
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
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="card">
                <p class="no-data">You are not enrolled in any course yet. Please contact the administrator.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
