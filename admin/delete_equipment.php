<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asset_tag'], $_POST['type'])) {
    $asset_tag = $conn->real_escape_string($_POST['asset_tag']);
    $type = strtolower($_POST['type']);

    // Map to actual table names
    $map = [
        'desktop' => 'desktop',
        'laptop' => 'laptops',
        'printer' => 'printers',
        'access point' => 'accesspoint',
        'switch' => 'switch',
        'telephone' => 'telephone'
    ];

    if (!array_key_exists($type, $map)) {
        die("Invalid equipment type.");
    }

    $table = $map[$type];

    $stmt = $conn->prepare("DELETE FROM `$table` WHERE asset_tag = ?");
    $stmt->bind_param("s", $asset_tag);

    if ($stmt->execute()) {
        header("Location: equipment.php?deleted=1");
        exit;
    } else {
        echo "❌ Delete failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "❌ Invalid request.";
}
?>
