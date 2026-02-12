<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System - Karpagam College of Arts and Science</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">

    <header class="college-header">
        <div class="header-content">
            <div class="brand-section">
                <img src="Logo2.png" alt="College Logo" class="college-logo">
                <div class="brand-details">
                    <span class="college-name">Karpagam College of Arts and Science</span>
                    <span class="dept-name">Department of BSc Computer Science</span>
                </div>
            </div>
            <div class="system-title">Attendance Management System</div>
        </div>
    </header>

    <div class="container landing-wrapper" style="flex-direction: column; justify-content: center; padding-top: 40px;">
        
        <div class="portal-grid">
            <a href="teacher_login.php" class="portal-card">
                <div class="portal-title" style="font-size: 40px;">👨‍🏫</div>
                <h2 class="portal-title">Teacher Portal</h2>
                <div class="btn btn-primary">Login as Faculty</div>
            </a>

            <a href="student_login.php" class="portal-card">
                <div class="portal-title" style="font-size: 40px;">🎓</div>
                <h2 class="portal-title">Student Portal</h2>
                <div class="btn btn-primary">Login as Student</div>
            </a>
        </div>
    </div>

    <footer class="college-footer">
        <strong>© 2026 Karpagam College of Arts and Science</strong>
        Department of BSc Computer Science | Attendance Management System
    </footer>

</body>
</html>
