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

function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    return $result && $result->num_rows > 0;
}

$stats = [
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0
];

// Tasks
$taskStmt = $conn->prepare("
    SELECT status, COUNT(*) AS cnt
    FROM tasks
    WHERE assigned_to = ?
    GROUP BY status
");
if ($taskStmt) {
    $taskStmt->bind_param("i", $user_id);
    $taskStmt->execute();
    $result = $taskStmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status'] ?? '');
        if (isset($stats[$status])) {
            $stats[$status] += (int)$row['cnt'];
        }
    }
    $taskStmt->close();
}

// Maintenance
if (tableExists($conn, 'maintenance_records')) {
    $maintenanceStmt = $conn->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM maintenance_records
        WHERE technician_id = ?
        GROUP BY status
    ");
    if ($maintenanceStmt) {
        $maintenanceStmt->bind_param("i", $user_id);
        $maintenanceStmt->execute();
        $result = $maintenanceStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $status = strtolower($row['status'] ?? '');
            if ($status === 'scheduled') {
                $stats['pending'] += (int)$row['cnt'];
            } elseif ($status === 'in_progress') {
                $stats['in_progress'] += (int)$row['cnt'];
            } elseif ($status === 'completed') {
                $stats['completed'] += (int)$row['cnt'];
            }
        }
        $maintenanceStmt->close();
    }
}

// Service Requests
if (tableExists($conn, 'service_requests')) {
    $serviceStmt = $conn->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM service_requests
        WHERE technician_id = ?
          AND status IN ('Pending','In Progress','Assigned','Completed')
        GROUP BY status
    ");
    if ($serviceStmt) {
        $serviceStmt->bind_param("i", $user_id);
        $serviceStmt->execute();
        $result = $serviceStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'] ?? '';
            if ($status === 'Pending') {
                $stats['pending'] += (int)$row['cnt'];
            } elseif ($status === 'In Progress' || $status === 'Assigned') {
                $stats['in_progress'] += (int)$row['cnt'];
            } elseif ($status === 'Completed') {
                $stats['completed'] += (int)$row['cnt'];
            }
        }
        $serviceStmt->close();
    }
}

// System Requests
if (tableExists($conn, 'system_requests')) {
    // Pending system requests (visible to all technicians)
    $systemPending = $conn->query("SELECT COUNT(*) AS cnt FROM system_requests WHERE status = 'Pending'");
    if ($systemPending) {
        $row = $systemPending->fetch_assoc();
        $stats['pending'] += (int)($row['cnt'] ?? 0);
    }

    // In progress / completed system requests assigned to this technician
    $systemStmt = $conn->prepare("
        SELECT status, COUNT(*) AS cnt
        FROM system_requests
        WHERE technician_id = ?
          AND status IN ('In Progress','Completed')
        GROUP BY status
    ");
    if ($systemStmt) {
        $systemStmt->bind_param("i", $user_id);
        $systemStmt->execute();
        $result = $systemStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'] ?? '';
            if ($status === 'In Progress') {
                $stats['in_progress'] += (int)$row['cnt'];
            } elseif ($status === 'Completed') {
                $stats['completed'] += (int)$row['cnt'];
            }
        }
        $systemStmt->close();
    }
}

$total = $stats['pending'] + $stats['in_progress'] + $stats['completed'];

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'pending_count' => $stats['pending'],
    'in_progress_count' => $stats['in_progress'],
    'completed_count' => $stats['completed'],
    'total_items' => $total
]);
?>


