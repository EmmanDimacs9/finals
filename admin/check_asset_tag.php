<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

header('Content-Type: application/json');

$asset_tag = isset($_GET['asset_tag']) ? trim($_GET['asset_tag']) : '';

if (empty($asset_tag)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Function to check if asset tag already exists across all equipment tables
function checkAssetTagExists($conn, $asset_tag) {
    $tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone', 'equipment'];
    
    foreach ($tables as $table) {
        // Check if table exists first
        $check_table = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$check_table || $check_table->num_rows == 0) {
            continue;
        }
        
        $stmt = $conn->prepare("SELECT id FROM $table WHERE asset_tag = ? LIMIT 1");
        if (!$stmt) {
            continue; // Skip if prepare fails
        }
        
        $stmt->bind_param("s", $asset_tag);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return true; // Asset tag exists
        }
        $stmt->close();
    }
    return false; // Asset tag is unique
}

$exists = checkAssetTagExists($conn, $asset_tag);

echo json_encode(['exists' => $exists]);
?>

