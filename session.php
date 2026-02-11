<?php
// Session management utilities

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect to login if not authenticated
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ' . $role . '_login.php');
        exit();
    }
    
    if ($role !== null && !hasRole($role)) {
        header('Location: index.php');
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user name
function getCurrentUserName() {
    return $_SESSION['name'] ?? 'User';
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}
?>
