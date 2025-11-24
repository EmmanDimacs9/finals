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

// Function to get the correct path to landing.php based on current directory
function getLandingPagePath() {
    // Use PHP_SELF which gives the script path relative to document root
    $script_path = $_SERVER['PHP_SELF'];
    $script_path = str_replace('\\', '/', $script_path);
    
    // Remove leading slash and get directory parts
    $script_path = ltrim($script_path, '/');
    $parts = explode('/', $script_path);
    
    // Count directory depth (subtract 1 for the filename itself)
    $depth = count($parts) - 1;
    
    // Build path to landing.php
    if ($depth > 0) {
        return str_repeat('../', $depth) . 'landing.php';
    } else {
        return 'landing.php';
    }
}

// Set session timeout (30 minutes)
$session_timeout = 1800; // 30 minutes in seconds

// Check if session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired, destroy it
    session_unset();
    session_destroy();
    session_start();
    
    // Redirect to landing page when session expires
    $landing_path = getLandingPagePath();
    header('Location: ' . $landing_path);
    exit();
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
        $landing_path = getLandingPagePath();
        header('Location: ' . $landing_path);
        exit();
    }
}

// Function to require admin access
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        // Redirect non-admins to landing page
        $landing_path = getLandingPagePath();
        header('Location: ' . $landing_path);
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