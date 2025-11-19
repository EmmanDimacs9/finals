<?php
require('db.php');

/**
 * Log an action for admin/technician users
 * @param int $adminId The user ID
 * @param string $adminName The user's name
 * @param string $action The action performed
 * @param string $description Additional description/details
 */
function logAdminAction($adminId, $adminName, $action, $description = '') {
    global $conn;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, admin_name, action, description, ip_address, user_agent) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $adminId, $adminName, $action, $description, $ip, $agent);
    $stmt->execute();
    $stmt->close();
}

/**
 * Log a technician activity (wrapper function for easier use)
 * @param int $technicianId The technician's user ID
 * @param string $technicianName The technician's name
 * @param string $action The action performed (e.g., "Updated Task", "Completed Service Request")
 * @param string $description Additional description/details
 */
function logTechnicianActivity($technicianId, $technicianName, $action, $description = '') {
    logAdminAction($technicianId, $technicianName, $action, $description);
}

/**
 * Log activity using session data (convenience function)
 * Requires $_SESSION['user_id'] and $_SESSION['user_name'] to be set
 * @param string $action The action performed
 * @param string $description Additional description/details
 */
function logActivity($action, $description = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], $action, $description);
    }
}
?>
