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
    <title>Teacher Dashboard - Attendance Management System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .timetable-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .timetable-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .period-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .period-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .time-badge {
            color: #666;
            font-size: 0.9em;
        }
        .subject-info h3 {
            margin: 5px 0;
            color: #333;
        }
        .course-badge {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-right: 10px;
        }
        .student-count {
            color: #666;
            font-size: 0.9em;
            margin-top: 8px;
        }
        .attendance-status {
            margin-top: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9em;
        }
        .status-marked {
            background: #c8e6c9;
            color: #2e7d32;
        }
        .status-pending {
            background: #fff3e0;
            color: #f57c00;
        }
        .day-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .no-classes-today {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h2>Attendance Management System</h2>
            <div class="nav-right">
                <span class="user-name">Welcome, <?php echo htmlspecialchars($teacher_name); ?></span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Teacher Dashboard</h1>
            <p>Manage your timetable and mark period-wise attendance</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_subjects']; ?></h3>
                    <p>Subjects Assigned</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_students']; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✓</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_attendance_records']; ?></h3>
                    <p>Attendance Records</p>
                </div>
            </div>
        </div>

        <!-- Today's Timetable -->
        <div class="card">
            <div class="day-header">
                <h2><?php echo $today; ?>'s Schedule</h2>
                <p><?php echo date('F j, Y'); ?></p>
            </div>
            
            <?php if (count($timetable) > 0): ?>
                <?php foreach ($timetable as $slot): ?>
                    <div class="timetable-card">
                        <div class="period-header">
                            <span class="period-number">Period <?php echo $slot['period_number']; ?></span>
                            <span class="time-badge">
                                <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                            </span>
                        </div>
                        
                        <div class="subject-info">
                            <h3><?php echo htmlspecialchars($slot['subject_name']); ?></h3>
                            <div>
                                <span class="course-badge"><?php echo htmlspecialchars($slot['course_name']); ?> - Sem <?php echo $slot['semester']; ?></span>
                                <span class="course-badge">Code: <?php echo htmlspecialchars($slot['subject_code']); ?></span>
                            </div>
                            <div class="student-count">
                                👥 <?php echo $slot['total_students']; ?> students enrolled
                            </div>
                        </div>
                        
                        <?php if ($slot['marked_count'] > 0): ?>
                            <div class="attendance-status status-marked">
                                ✓ Attendance marked for <?php echo $slot['marked_count']; ?> students
                            </div>
                        <?php else: ?>
                            <div class="attendance-status status-pending">
                                ⏳ Attendance not marked yet
                            </div>
                        <?php endif; ?>
                        
                        <a href="mark_attendance.php?subject_id=<?php echo $slot['subject_id']; ?>&course_id=<?php echo $slot['course_id']; ?>&semester=<?php echo $slot['semester']; ?>&period=<?php echo $slot['period_number']; ?>" 
                           class="btn btn-primary" style="margin-top: 15px;">
                            <?php echo ($slot['marked_count'] > 0) ? 'Update Attendance' : 'Mark Attendance'; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-classes-today">
                    <h3>🎉 No classes scheduled for today!</h3>
                    <p>Enjoy your free time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
