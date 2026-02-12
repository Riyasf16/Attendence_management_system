<?php
require_once 'config.php';
require_once 'session.php';

// Redirect if already logged in
if (isLoggedIn() && hasRole('teacher')) {
    header('Location: teacher_dashboard.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, password, name FROM users WHERE username = ? AND role = 'teacher'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = 'teacher';
                $_SESSION['name'] = $user['name'];
                
                header('Location: teacher_dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - Karpagam College of Arts and Science</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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

    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="Logo2.png" alt="College Logo" class="login-logo">
                <h2>Faculty Portal</h2>
                <p>Sign in to manage attendance</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 15px; text-align: center; font-size: 14px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Enter your ID">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="links" style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="font-size: 13px;">← Back to Home</a>
            </div>
        </div>
    </div>

    <footer class="college-footer">
        <strong>© 2026 Karpagam College of Arts and Science</strong>
        Department of BSc Computer Science | Attendance Management System
    </footer>

</body>
</html>
