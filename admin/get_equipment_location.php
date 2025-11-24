<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

$asset_tag = isset($_GET['asset_tag']) ? $conn->real_escape_string($_GET['asset_tag']) : '';

if (empty($asset_tag)) {
    echo json_encode(['success' => false, 'message' => 'Asset tag parameter is required']);
    exit;
}

// Search across all equipment tables
$tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone'];
$location = null;

foreach ($tables as $table) {
    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$check || $check->num_rows == 0) {
        continue;
    }
    
    // For desktop table, use department_office; for others, use department
    if ($table === 'desktop') {
        $query = "SELECT location, department_office as department FROM $table WHERE asset_tag = ? LIMIT 1";
    } else {
        $query = "SELECT location, department FROM $table WHERE asset_tag = ? LIMIT 1";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $asset_tag);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $location = $row['location'] ?? '';
        $department = $row['department'] ?? '';
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'location' => $location,
            'department' => $department
        ]);
        exit;
    }
    
    $stmt->close();
}

echo json_encode([
    'success' => false,
    'message' => 'Equipment not found',
    'location' => ''
]);
?>




