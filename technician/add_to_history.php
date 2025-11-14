<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Only accept JSON POST
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['equipment_id'], $input['table_name'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$equipment_id = intval($input['equipment_id']);
$table_name   = $conn->real_escape_string($input['table_name']);
$user_id      = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Make sure table exists in allowed list
$allowed_tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone'];
if (!in_array($table_name, $allowed_tables)) {
    echo json_encode(['success' => false, 'error' => 'Invalid table']);
    exit;
}

// Insert into history table
$stmt = $conn->prepare("INSERT INTO history (user_id, equipment_id, table_name, action, timestamp) VALUES (?, ?, ?, ?, NOW())");
$action = $input['action'] ?? 'qr_scan';
$stmt->bind_param("iiss", $user_id, $equipment_id, $table_name, $action);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
