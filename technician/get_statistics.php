<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user statistics
$equipment_count = 0;
$equipmentStmt = $conn->prepare("
    SELECT COUNT(DISTINCT equipment_type) AS cnt
    FROM maintenance_records
    WHERE technician_id = ?
");
if ($equipmentStmt) {
    $equipmentStmt->bind_param("i", $user_id);
    $equipmentStmt->execute();
    $result = $equipmentStmt->get_result()->fetch_assoc();
    $equipment_count = (int)($result['cnt'] ?? 0);
    $equipmentStmt->close();
}

$task_count = 0;
$taskStmt = $conn->prepare("
    SELECT 
        (
            (SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'pending') +
            (SELECT COUNT(*) FROM service_requests WHERE technician_id = ? AND status = 'Pending') +
            (SELECT COUNT(*) FROM system_requests WHERE technician_id = ? AND status = 'Pending')
        ) AS total_tasks
");
if ($taskStmt) {
    $taskStmt->bind_param("iii", $user_id, $user_id, $user_id);
    $taskStmt->execute();
    $result = $taskStmt->get_result()->fetch_assoc();
    $task_count = (int)($result['total_tasks'] ?? 0);
    $taskStmt->close();
}

$maintenance_count = 0;
$maintenanceStmt = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM maintenance_records
    WHERE technician_id = ? AND status = 'scheduled'
");
if ($maintenanceStmt) {
    $maintenanceStmt->bind_param("i", $user_id);
    $maintenanceStmt->execute();
    $result = $maintenanceStmt->get_result()->fetch_assoc();
    $maintenance_count = (int)($result['cnt'] ?? 0);
    $maintenanceStmt->close();
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'equipment_count' => $equipment_count,
    'task_count' => $task_count,
    'maintenance_count' => $maintenance_count
]);
?>


