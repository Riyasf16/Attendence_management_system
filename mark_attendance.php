<?php
require_once 'config.php';
require_once 'session.php';

// Require teacher login
requireLogin('teacher');

$conn = getDBConnection();
$teacher_id = getCurrentUserId();
$teacher_name = getCurrentUserName();

$class_id = $_GET['class_id'] ?? null;
$success_message = '';
$error_message = '';

// Validate class belongs to teacher
if ($class_id) {
    $stmt = $conn->prepare("SELECT class_name FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    $class_result = $stmt->get_result();
    
    if ($class_result->num_rows === 0) {
        header('Location: teacher_dashboard.php');
        exit();
    }
    
    $class_data = $class_result->fetch_assoc();
    $class_name = $class_data['class_name'];
    $stmt->close();
} else {
    header('Location: teacher_dashboard.php');
    exit();
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = $_POST['date'] ?? date('Y-m-d');
    $attendance_data = $_POST['attendance'];
    
    $conn->begin_transaction();
    
    try {
        // Delete existing attendance for this date and class
        $delete_stmt = $conn->prepare("DELETE FROM attendance WHERE class_id = ? AND date = ?");
        $delete_stmt->bind_param("is", $class_id, $date);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Insert new attendance records
        $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($attendance_data as $student_id => $status) {
            $insert_stmt->bind_param("iissi", $student_id, $class_id, $date, $status, $teacher_id);
            $insert_stmt->execute();
        }
        
        $insert_stmt->close();
        $conn->commit();
        
        $success_message = "Attendance marked successfully for " . date('F j, Y', strtotime($date));
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error marking attendance: " . $e->getMessage();
    }
}

// Get students in this class
$stmt = $conn->prepare("SELECT id, roll_number, name FROM students WHERE class_id = ? ORDER BY roll_number");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students_result = $stmt->get_result();
$students = $students_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get today's attendance if exists
$today = date('Y-m-d');
$existing_attendance = [];

$stmt = $conn->prepare("SELECT student_id, status FROM attendance WHERE class_id = ? AND date = ?");
$stmt->bind_param("is", $class_id, $today);
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
    <title>Mark Attendance - <?php echo htmlspecialchars($class_name); ?></title>
    <link rel="stylesheet" href="styles.css">
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
                <p><?php echo htmlspecialchars($class_name); ?></p>
            </div>
            <a href="teacher_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" class="attendance-form">
                <div class="form-group">
                    <label for="date">Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo $today; ?>" max="<?php echo $today; ?>" required>
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
                                                           <?php echo $current_status === 'present' ? 'checked' : ''; ?>>
                                                    <span class="status-present">Present</span>
                                                </label>
                                                <label class="radio-label">
                                                    <input type="radio" 
                                                           name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="absent"
                                                           <?php echo $current_status === 'absent' ? 'checked' : ''; ?>>
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
                        <button type="submit" class="btn btn-primary btn-large">Submit Attendance</button>
                    </div>
                <?php else: ?>
                    <p class="no-data">No students enrolled in this class.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
