<?php
// Enhanced session management for BSU Inventory System
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    // Start session with custom name
    session_name('BSU_INVENTORY_SESSION');
    session_start();
}

// Set session timeout (30 minutes)
$session_timeout = 1800; // 30 minutes in seconds

// Check if session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired, destroy it
    session_unset();
    session_destroy();
    session_start();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to check if user is technician
function isTechnician() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technician';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: landing.php');
        exit();
    }
}

// Function to require admin access
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: admin/dashboard.php');
        exit();
    }
}

// Function to get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}
?> 