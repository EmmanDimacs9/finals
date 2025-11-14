<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Only accept JSON POST
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['equipment_id'], $input['table_name'], $input['remarks'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$equipment_id = intval($input['equipment_id']);
$table_name = $conn->real_escape_string($input['table_name']);
$remarks = $conn->real_escape_string($input['remarks']);
$user_id = $_SESSION['user_id'];

// Validate table name
$allowed_tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone'];
if (!in_array($table_name, $allowed_tables)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table name']);
    exit;
}

// Update the remarks in the appropriate table
$stmt = $conn->prepare("UPDATE $table_name SET remarks = ? WHERE id = ?");
$stmt->bind_param("si", $remarks, $equipment_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log the update action
        $log_stmt = $conn->prepare("INSERT INTO history (user_id, equipment_id, table_name, action, timestamp) VALUES (?, ?, ?, 'remarks_updated', NOW())");
        $log_stmt->bind_param("iis", $user_id, $equipment_id, $table_name);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Remarks updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No equipment found with that ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
