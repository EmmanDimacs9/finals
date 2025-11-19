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
$stats_query = "
    SELECT 
        ( 
            SELECT COUNT(*) FROM (
                SELECT assigned_person FROM desktop WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM laptops WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM printers WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM accesspoint WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM `switch` WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM telephone WHERE assigned_person = ?
            ) AS eq
        ) AS equipment_count,
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = ?) AS task_count,
        (SELECT COUNT(*) FROM history WHERE user_id = ?) AS maintenance_count
";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ssssssii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'equipment_count' => (int)($stats['equipment_count'] ?? 0),
    'task_count' => (int)($stats['task_count'] ?? 0),
    'maintenance_count' => (int)($stats['maintenance_count'] ?? 0)
]);
?>

