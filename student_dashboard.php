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
    // Get overall attendance statistics (Day-based)
    // Logic: 
    // - Absent <= 1 period: Full Day (1)
    // - Absent == 2 periods: Half Day (0.5)
    // - Absent > 2 periods: Absent (0)
    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total,
        SUM(daily_score) as present,
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
    ) as daily_stats");
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
    <title>Student Dashboard - Karpagam College</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="college-header">
        <div class="header-content">
            <div class="brand-section">
                <img src="Logo.png" alt="College Logo" class="college-logo">
                <div class="brand-details">
                    <span class="college-name">Karpagam College of Arts and Science</span>
                    <span class="dept-name">Department of BSc Computer Science</span>
                </div>
            </div>
            <div class="system-title">Attendance Management System</div>
        </div>
    </header>

    <nav class="navbar">
        <div class="nav-container">
            <div class="user-welcome">Welcome, <strong><?php echo htmlspecialchars($student_name); ?></strong> (Student)</div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header" style="margin-top: 20px; margin-bottom: 20px;">
            <h2>Student Dashboard</h2>
            <p style="color: #666;">Track your academic attendance and performance</p>
        </div>

        <?php if ($student_id): ?>
            <!-- Student Profile Header -->
            <div class="student-header">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <h2 style="color: white; margin-bottom: 10px; font-size: 28px;"><?php echo htmlspecialchars($student_name); ?></h2>
                        <div class="student-info">
                            <span class="info-badge">📝 Roll No: <?php echo htmlspecialchars($roll_number); ?></span>
                            <span class="info-badge">🎓 <?php echo htmlspecialchars($course_name); ?></span>
                            <span class="info-badge">📖 Semester <?php echo $semester; ?></span>
                        </div>
                    </div>
                    <div class="overall-percentage" style="background: rgba(255,255,255,0.2); border-radius: 50%; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; font-weight: bold; border: 4px solid rgba(255,255,255,0.4);">
                        <?php echo $overall_stats['percentage']; ?>%
                    </div>
                </div>
            </div>

            <!-- Overall Stats Summary -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3><?php echo floatval($overall_stats['present']); ?> / <?php echo $overall_stats['total']; ?></h3>
                    <p>Days Attended</p>
                </div>
                <div class="summary-card">
                    <h3><?php echo count($subject_stats); ?></h3>
                    <p>Total Subjects</p>
                </div>
                <div class="summary-card">
                    <h3 style="color: <?php echo ($overall_stats['percentage'] >= 75 ? '#28a745' : '#dc3545'); ?>">
                        <?php echo $overall_stats['percentage']; ?>%
                    </h3>
                    <p>Total Percentage</p>
                </div>
                <!-- Exam Eligibility Card -->
                <div class="summary-card">
                    <?php if ($overall_stats['percentage'] >= 75): ?>
                        <h3 style="color: #28a745;">Eligible</h3>
                        <p>Exam Status</p>
                    <?php else: ?>
                        <h3 style="color: #dc3545;">Shortage</h3>
                        <p>Exam Status</p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Recent Attendance Table -->
                <div class="data-table-container">
                    <h3 style="color: #003366; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Recent Attendance Records</h3>
                    
                    <?php if (count($recent_records) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day/Period</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_records as $record): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                            <td>
                                                <small style="color: #666;"><?php echo $record['day_of_week']; ?></small><br>
                                                <span class="badge" style="background: #eef2f7; color: #333;">Period <?php echo $record['period_number']; ?></span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($record['subject_name']); ?></strong><br>
                                                <small style="color: #999;"><?php echo htmlspecialchars($record['subject_code']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo ($record['status'] == 'present' ? 'success' : 'danger'); ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="no-data">No attendance records found.</p>
                    <?php endif; ?>
                </div>

                <!-- Subject Wise Short List -->
                <div class="data-table-container">
                     <h3 style="color: #003366; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Subject Summary</h3>
                     <?php foreach ($subject_stats as $subject): ?>
                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <strong style="font-size: 14px;"><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                <span style="font-weight: bold; color: <?php echo ($subject['percentage'] >= 75 ? '#28a745' : '#dc3545'); ?>"><?php echo $subject['percentage']; ?>%</span>
                            </div>
                            <div style="background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: <?php echo ($subject['percentage'] >= 75 ? '#28a745' : '#dc3545'); ?>; width: <?php echo $subject['percentage']; ?>%; height: 100%;"></div>
                            </div>
                            <div style="font-size: 12px; color: #777; margin-top: 3px;">
                                <?php echo $subject['present_count']; ?> / <?php echo $subject['total_classes']; ?> classes
                            </div>
                        </div>
                     <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="card">
                <p class="no-data">You are not enrolled in any course yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="college-footer">
        <strong>© 2026 Karpagam College of Arts and Science</strong>
        Department of BSc Computer Science | Attendance Management System
    </footer>

</body>
</html>
