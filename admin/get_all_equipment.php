<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category parameter is required']);
    exit;
}

// Map category to database tables
$table_map = [
    'desktop' => ['desktop', 'laptops'],  // Desktop Computers
    'network' => ['accesspoint', 'switch']  // Network Devices
];

if (!isset($table_map[$category])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category']);
    exit;
}

$tables = $table_map[$category];
$equipment = [];

foreach ($tables as $table) {
    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$check || $check->num_rows == 0) {
        continue;
    }
    
    // Check if status column exists
    $status_check = $conn->query("SHOW COLUMNS FROM $table LIKE 'status'");
    $has_status = $status_check && $status_check->num_rows > 0;
    
    // Fetch equipment asset tags
    // For desktop table, use department_office; for others, use department
    if ($table === 'desktop') {
        $where_clause = "WHERE asset_tag IS NOT NULL AND asset_tag != ''";
        if ($has_status) {
            $where_clause .= " AND status = 'active'";
        }
        $query = "SELECT DISTINCT asset_tag, department_office as department, location 
                  FROM $table 
                  $where_clause
                  ORDER BY asset_tag ASC";
    } else {
        $where_clause = "WHERE asset_tag IS NOT NULL AND asset_tag != ''";
        if ($has_status) {
            $where_clause .= " AND status = 'active'";
        }
        $query = "SELECT DISTINCT asset_tag, department, location 
                  FROM $table 
                  $where_clause
                  ORDER BY asset_tag ASC";
    }
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $equipment[] = [
                'asset_tag' => $row['asset_tag'],
                'department' => $row['department'] ?? '',
                'location' => $row['location'] ?? ''
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'equipment' => $equipment
]);
?>




