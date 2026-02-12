<?php
require_once 'config.php';
require_once 'session.php';

// Require teacher login
requireLogin('teacher');

$conn = getDBConnection();
$teacher_id = getCurrentUserId();
$teacher_name = getCurrentUserName();

$subject_id = $_GET['subject_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;
$semester = $_GET['semester'] ?? null;
$period = $_GET['period'] ?? null;

$success_message = '';
$error_message = '';

// Validate parameters
if (!$subject_id || !$course_id || !$semester || !$period) {
    header('Location: teacher_dashboard.php');
    exit();
}

// Verify this subject is assigned to the teacher
$verify_stmt = $conn->prepare("SELECT s.subject_name, s.subject_code, c.course_name 
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.id
    JOIN courses c ON s.course_id = c.id
    WHERE ts.teacher_id = ? AND ts.subject_id = ? AND ts.academic_year = '2025-26'");
$verify_stmt->bind_param("ii", $teacher_id, $subject_id);
$verify_stmt->execute();
$subject_result = $verify_stmt->get_result();

if ($subject_result->num_rows === 0) {
    header('Location: teacher_dashboard.php');
    exit();
}

$subject_data = $subject_result->fetch_assoc();
$subject_name = $subject_data['subject_name'];
$subject_code = $subject_data['subject_code'];
$course_name = $subject_data['course_name'];
$verify_stmt->close();

// Get the timetable entry for today to check time restrictions
$today = date('l'); // Day name (Monday, Tuesday, etc.)
$time_check_stmt = $conn->prepare("SELECT start_time, end_time 
    FROM timetable 
    WHERE teacher_id = ? 
    AND subject_id = ? 
    AND course_id = ? 
    AND semester = ? 
    AND period_number = ? 
    AND day_of_week = ?
    AND academic_year = '2025-26'
    LIMIT 1");
$time_check_stmt->bind_param("iiiiss", $teacher_id, $subject_id, $course_id, $semester, $period, $today);
$time_check_stmt->execute();
$timetable_result = $time_check_stmt->get_result();

$can_mark_attendance = false;
$time_restriction_message = '';

if ($timetable_result->num_rows > 0) {
    $timetable = $timetable_result->fetch_assoc();
    $start_time = $timetable['start_time'];
    $end_time = $timetable['end_time'];
    
    // Calculate allowed marking window (class time + 15 minutes grace)
    $end_timestamp = strtotime($end_time);
    $grace_end_time = date('H:i:s', strtotime('+15 minutes', $end_timestamp));
    
    // Get current time
    $current_time = date('H:i:s');
    
    // Check if current time is within allowed window
    if ($current_time >= $start_time && $current_time <= $grace_end_time) {
        $can_mark_attendance = true;
    } else {
        $can_mark_attendance = false;
        $start_12hr = date('g:i A', strtotime($start_time));
        $grace_end_12hr = date('g:i A', strtotime($grace_end_time));
        $time_restriction_message = "⏰ You can only mark attendance for this period between $start_12hr and $grace_end_12hr (class time + 15 min grace period).";
    }
} else {
    // This period is not scheduled for today
    $can_mark_attendance = false;
    $time_restriction_message = "This period is not scheduled for $today.";
}
$time_check_stmt->close();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    // Check if attendance can be marked (time restriction)
    if (!$can_mark_attendance) {
        $error_message = $time_restriction_message;
    } else {
        $date = $_POST['date'] ?? date('Y-m-d');
        $attendance_data = $_POST['attendance'];
        $day_of_week = date('l', strtotime($date)); // Get day name
        
        $conn->begin_transaction();
        
        try {
        // Insert/Update attendance records (ON DUPLICATE KEY UPDATE handles updates)
        $insert_stmt = $conn->prepare("INSERT INTO attendance 
            (student_id, subject_id, period_number, date, day_of_week, status, marked_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by)");
        
        foreach ($attendance_data as $student_id => $status) {
            $insert_stmt->bind_param("iiisssi", 
                $student_id, 
                $subject_id, 
                $period, 
                $date, 
                $day_of_week, 
                $status, 
                $teacher_id
            );
            $insert_stmt->execute();
        }
        
        $insert_stmt->close();
        $conn->commit();
        
        $success_message = "Attendance marked successfully for Period $period on " . date('F j, Y', strtotime($date));
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error marking attendance: " . $e->getMessage();
    }
    } // End of can_mark_attendance check
}

// Get students enrolled in this course and semester
$stmt = $conn->prepare("SELECT s.id, s.roll_number, s.name, u.username
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.course_id = ? AND s.semester = ?
    ORDER BY s.roll_number");
$stmt->bind_param("ii", $course_id, $semester);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get today's attendance if exists
$today = date('Y-m-d');
$existing_attendance = [];

$stmt = $conn->prepare("SELECT student_id, status 
    FROM attendance 
    WHERE subject_id = ? AND date = ? AND period_number = ?");
$stmt->bind_param("isi", $subject_id, $today, $period);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existing_attendance[$row['student_id']] = $row['status'];
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?php echo htmlspecialchars($subject_name); ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .subject-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .subject-header h2 {
            margin: 0 0 10px 0;
        }
        .subject-meta {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .meta-item {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .quick-actions {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .quick-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            background: #e3f2fd;
            color: #1976d2;
        }
        .quick-btn:hover {
            background: #bbdefb;
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
        <div class="page-header">
            <div>
                <h1>Mark Attendance</h1>
                <p>Period <?php echo $period; ?> - <?php echo htmlspecialchars($subject_name); ?></p>
            </div>
            <a href="teacher_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!$can_mark_attendance && $time_restriction_message): ?>
            <div class="error-message" style="background: #fff3cd; color: #856404; border-left: 4px solid #ffc107;">
                <strong>⏰ Time Restriction</strong><br>
                <?php echo htmlspecialchars($time_restriction_message); ?><br>
                <small>Current time: <?php echo date('g:i A'); ?></small>
            </div>
        <?php endif; ?>

        <div class="subject-header">
            <h2><?php echo htmlspecialchars($subject_name); ?></h2>
            <div class="subject-meta">
                <span class="meta-item">📚 Code: <?php echo htmlspecialchars($subject_code); ?></span>
                <span class="meta-item">🎓 <?php echo htmlspecialchars($course_name); ?></span>
                <span class="meta-item">📖 Semester <?php echo $semester; ?></span>
                <span class="meta-item">🕐 Period <?php echo $period; ?></span>
                <span class="meta-item">👥 <?php echo count($students); ?> Students</span>
            </div>
        </div>

        <div class="card">
            <form method="POST" action="" class="attendance-form">
                <div class="form-group">
                    <label for="date">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo $today; ?>" max="<?php echo $today; ?>" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?> required>
                </div>

                <div class="quick-actions">
                    <button type="button" class="quick-btn" onclick="markAll('present')" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>✓ Mark All Present</button>
                    <button type="button" class="quick-btn" onclick="markAll('absent')" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>✗ Mark All Absent</button>
                </div>

                <?php if (count($students) > 0): ?>
                    <div class="attendance-table-wrapper">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Roll Number</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <?php 
                                    $current_status = $existing_attendance[$student['id']] ?? 'present';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td>
                                            <div class="radio-group">
                                                <label class="radio-label">
                                                    <input type="radio" 
                                                           name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="present" 
                                                           <?php echo $current_status === 'present' ? 'checked' : ''; ?>
                                                           <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                                                    <span class="status-present">Present</span>
                                                </label>
                                                <label class="radio-label">
                                                    <input type="radio" 
                                                           name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="absent"
                                                           <?php echo $current_status === 'absent' ? 'checked' : ''; ?>
                                                           <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                                                    <span class="status-absent">Absent</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                            <?php echo $can_mark_attendance ? 'Submit Attendance' : '🔒 Attendance Locked (Time Restriction)'; ?>
                        </button>
                    </div>
                <?php else: ?>
                    <p class="no-data">No students enrolled in this course and semester.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        function markAll(status) {
            const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
            radios.forEach(radio => radio.checked = true);
        }
    </script>
</body>
</html>
