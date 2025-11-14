<?php
session_start();
require_once '../includes/session.php';

// Log the logout activity if user was logged in
if (isset($_SESSION['user_id'])) {
    require_once '../includes/db.php';
    
    $logout_time = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Insert logout record into main BSU system
    $log_stmt = $conn->prepare("
        INSERT INTO user_logins (user_id, login_time, ip_address, user_agent, source) 
        VALUES (?, ?, ?, ?, 'users_folder_logout')
    ");
    $log_stmt->bind_param("isss", $_SESSION['user_id'], $logout_time, $ip_address, $user_agent);
    $log_stmt->execute();
}

// Destroy the session
session_destroy();

// Redirect to main BSU landing page
header('Location: ../landing.php');
exit();
?> 