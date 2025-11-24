<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : '';
$asset_tag = isset($_GET['asset_tag']) ? $conn->real_escape_string($_GET['asset_tag']) : '';

if (empty($type) || empty($asset_tag)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

// Map type to table name
$table_map = [
    'desktop' => 'desktop',
    'laptop' => 'laptops',
    'printer' => 'printers',
    'accesspoint' => 'accesspoint',
    'switch' => 'switch',
    'telephone' => 'telephone'
];

if (!isset($table_map[$type])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid equipment type']);
    exit();
}

$table = $table_map[$type];
$query = "SELECT * FROM `$table` WHERE asset_tag = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $asset_tag);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $equipment = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'equipment' => $equipment]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Equipment not found']);
}

$stmt->close();
?>



