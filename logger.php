<?php
require_once __DIR__ . '/includes/db.php';

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
?>
