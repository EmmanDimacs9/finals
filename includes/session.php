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

// Function to get an absolute path to landing.php regardless of current directory depth
function getLandingPagePath() {
    // Resolve the absolute path of the project root (one level above /includes)
    $project_root = realpath(__DIR__ . '/..');
    $document_root = isset($_SERVER['DOCUMENT_ROOT'])
        ? realpath($_SERVER['DOCUMENT_ROOT'])
        : null;

    if ($project_root && $document_root) {
        $project_root = str_replace('\\', '/', $project_root);
        $document_root = str_replace('\\', '/', $document_root);

        // If the project lives under the document root, compute the URL base path
        if (strpos($project_root, $document_root) === 0) {
            $base_path = substr($project_root, strlen($document_root));
            $base_path = '/' . ltrim($base_path, '/');
            return rtrim($base_path, '/') . '/landing.php';
        }
    }

    // Fallback to root-level landing.php
    return '/landing.php';
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