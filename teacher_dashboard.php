<?php
require_once 'config.php';
require_once 'session.php';

// Require teacher login
requireLogin('teacher');

$conn = getDBConnection();
$teacher_id = getCurrentUserId();
$teacher_name = getCurrentUserName();

// Get today's day
$today = date('l'); // Monday, Tuesday, etc.
$current_time = date('H:i:s');

// Get today's timetable for this teacher
$timetable_query = "SELECT 
    t.id as timetable_id,
    t.period_number,
    t.start_time,
    t.end_time,
    s.id as subject_id,
    s.subject_code,
    s.subject_name,
    c.course_name,
    t.semester,
    t.course_id,
    COUNT(DISTINCT st.id) as total_students,
    COUNT(DISTINCT a.id) as marked_count
FROM timetable t
JOIN subjects s ON t.subject_id = s.id
JOIN courses c ON t.course_id = c.id
LEFT JOIN students st ON st.course_id = t.course_id AND st.semester = t.semester
LEFT JOIN attendance a ON a.subject_id = s.id AND a.date = CURDATE() AND a.period_number = t.period_number AND a.student_id = st.id
WHERE t.teacher_id = ? 
  AND t.day_of_week = ?
  AND t.academic_year = '2025-26'
GROUP BY t.id, t.period_number, t.start_time, t.end_time, s.id, s.subject_code, s.subject_name, c.course_name, t.semester, t.course_id
ORDER BY t.period_number";

$stmt = $conn->prepare($timetable_query);
$stmt->bind_param("is", $teacher_id, $today);
$stmt->execute();
$timetable = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stats_query = "SELECT 
    COUNT(DISTINCT ts.subject_id) as total_subjects,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT a.id) as total_attendance_records
FROM teacher_subjects ts
LEFT JOIN subjects subj ON ts.subject_id = subj.id
LEFT JOIN students s ON s.course_id = subj.course_id AND s.semester = subj.semester
LEFT JOIN attendance a ON a.subject_id = subj.id AND a.marked_by = ?
WHERE ts.teacher_id = ? AND ts.academic_year = '2025-26'";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ii", $teacher_id, $teacher_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Karpagam College</title>
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
            <div class="user-welcome">Welcome, <strong><?php echo htmlspecialchars($teacher_name); ?></strong> (Faculty)</div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header" style="margin-top: 20px; margin-bottom: 20px;">
            <h2>Faculty Dashboard</h2>
            <p style="color: #666;">Manage your classes and attendance</p>
        </div>

        <!-- Statistics Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3><?php echo $stats['total_subjects']; ?></h3>
                <p>Subjects Assigned</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="summary-card">
                <h3><?php echo $stats['total_attendance_records']; ?></h3>
                <p>Attendance Marked</p>
            </div>
        </div>

        <!-- Today's Timetable -->
        <div class="data-table-container">
            <div class="header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
                <h3 style="color: #003366; margin: 0;">📅 Today's Schedule (<?php echo $today; ?>, <?php echo date('d M Y'); ?>)</h3>
            </div>
            
            <?php if (count($timetable) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Period & Time</th>
                                <th>Subject & Code</th>
                                <th>Class Info</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timetable as $slot): ?>
                                <tr>
                                    <td>
                                        <span class="badge" style="background: #eef2f7; color: #333;">Period <?php echo $slot['period_number']; ?></span><br>
                                        <small style="color: #777;">
                                            <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($slot['subject_name']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($slot['subject_code']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($slot['course_name']); ?><br>
                                        <small>Semester <?php echo $slot['semester']; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($slot['marked_count'] > 0): ?>
                                            <span class="badge badge-success">✓ Marked (<?php echo $slot['marked_count']; ?>)</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mark_attendance.php?subject_id=<?php echo $slot['subject_id']; ?>&course_id=<?php echo $slot['course_id']; ?>&semester=<?php echo $slot['semester']; ?>&period=<?php echo $slot['period_number']; ?>" 
                                           class="btn btn-primary" style="padding: 5px 10px; font-size: 13px;">
                                            <?php echo ($slot['marked_count'] > 0) ? 'Update' : 'Mark Attendance'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999;">
                    <h3>No classes scheduled for today.</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="college-footer">
        <strong>© 2026 Karpagam College of Arts and Science</strong>
        Department of BSc Computer Science | Attendance Management System
    </footer>

</body>
</html>
