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
        
        $success_message = "Attendance marked for Period $period on " . date('F j, Y', strtotime($date));
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
    <title>Mark Attendance - Karpagam College</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .meta-tags {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .meta-tag {
            background: rgba(0, 51, 102, 0.1);
            color: #003366;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
        }
        .action-bar {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .quick-actions button {
            margin-right: 10px;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <header class="college-header">
        <div class="header-content">
            <div class="brand-details">
                <span class="college-name">Karpagam College of Arts and Science</span>
                <span class="dept-name">Department of BSc Computer Science</span>
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
        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1 style="color: #003366; font-size: 24px; margin-bottom: 5px;">Mark Attendance</h1>
                <p style="color: #666; font-size: 14px;">Period <?php echo $period; ?> • <?php echo htmlspecialchars($subject_name); ?></p>
                
                <div class="meta-tags">
                    <span class="meta-tag">📚 <?php echo htmlspecialchars($subject_code); ?></span>
                    <span class="meta-tag">🎓 <?php echo htmlspecialchars($course_name); ?></span>
                    <span class="meta-tag">📖 Sem <?php echo $semester; ?></span>
                    <span class="meta-tag">👥 <?php echo count($students); ?> Students</span>
                </div>
            </div>
            <a href="teacher_dashboard.php" class="btn btn-secondary" style="width: auto;">← Back to Dashboard</a>
        </div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$can_mark_attendance && $time_restriction_message): ?>
            <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeeba;">
                <strong>⏰ Time Restriction:</strong> <?php echo htmlspecialchars($time_restriction_message); ?>
            </div>
        <?php endif; ?>

        <div class="data-table-container">
            <form method="POST" action="">
                <div class="action-bar">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="date" style="margin-right: 10px; display: inline-block;">Select Date:</label>
                        <input type="date" id="date" name="date" class="form-control" style="width: auto; display: inline-block;" value="<?php echo $today; ?>" max="<?php echo $today; ?>" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?> required>
                    </div>

                    <div class="quick-actions">
                        <button type="button" class="btn btn-secondary" onclick="markAll('present')" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>✓ All Present</button>
                        <button type="button" class="btn btn-secondary" onclick="markAll('absent')" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>✗ All Absent</button>
                    </div>
                </div>

                <?php if (count($students) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Roll No</th>
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
                                        <td><strong><?php echo htmlspecialchars($student['roll_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 15px;">
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                                    <input type="radio" 
                                                           name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="present" 
                                                           <?php echo $current_status === 'present' ? 'checked' : ''; ?>
                                                           <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                                                    <span style="color: #28a745; font-weight: 500;">Present</span>
                                                </label>
                                                <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                                    <input type="radio" 
                                                           name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="absent"
                                                           <?php echo $current_status === 'absent' ? 'checked' : ''; ?>
                                                           <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                                                    <span style="color: #dc3545; font-weight: 500;">Absent</span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn btn-primary" style="width: auto; padding: 12px 30px;" <?php echo !$can_mark_attendance ? 'disabled' : ''; ?>>
                            <?php echo $can_mark_attendance ? 'Submit Attendance Record' : 'Attendance Locked'; ?>
                        </button>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; padding: 20px; color: #999;">No students enrolled.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <footer class="college-footer">
        <strong>© 2026 Karpagam College of Arts and Science</strong>
        Department of BSc Computer Science | Attendance Management System
    </footer>

    <script>
        function markAll(status) {
            const radios = document.querySelectorAll(`input[type="radio"][value="${status}"]`);
            radios.forEach(radio => radio.checked = true);
        }
    </script>

</body>
</html>
