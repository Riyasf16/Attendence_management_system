<?php
require_once 'config.php';
require_once 'session.php';

// Require teacher login
requireLogin('teacher');

$conn = getDBConnection();
$teacher_id = getCurrentUserId();
$teacher_name = getCurrentUserName();

// Get teacher's classes
$stmt = $conn->prepare("SELECT id, class_name FROM classes WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes_result = $stmt->get_result();
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stats_query = "SELECT 
    COUNT(DISTINCT c.id) as total_classes,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT a.id) as total_attendance_records
FROM classes c
LEFT JOIN students s ON c.id = s.class_id
LEFT JOIN attendance a ON s.id = a.student_id
WHERE c.teacher_id = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $teacher_id);
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
            <p>Manage your classes and mark attendance</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_classes']; ?></h3>
                    <p>Total Classes</p>
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

        <!-- Classes List -->
        <div class="card">
            <h2>Your Classes</h2>
            <?php if (count($classes) > 0): ?>
                <div class="classes-grid">
                    <?php foreach ($classes as $class): ?>
                        <div class="class-card">
                            <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                            <a href="mark_attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary">
                                Mark Attendance
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No classes assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
