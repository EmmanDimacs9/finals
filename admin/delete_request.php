<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

requireLogin();

// Check if user is admin
if (!isAdmin()) {
    die("❌ Access denied. Admin privileges required.");
}

// Get the request ID
$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id <= 0) {
    die("❌ Invalid Request ID.");
}

// First, ensure the requests table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS `requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `form_type` varchar(255) NOT NULL,
    `form_data` longtext DEFAULT NULL,
    `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `form_type` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$conn->query($createTableQuery);

// Check if form_data column exists, if not add it
$checkColumnQuery = "SHOW COLUMNS FROM `requests` LIKE 'form_data'";
$columnResult = $conn->query($checkColumnQuery);

if ($columnResult->num_rows == 0) {
    $addColumnQuery = "ALTER TABLE `requests` ADD COLUMN `form_data` longtext DEFAULT NULL AFTER `form_type`";
    $conn->query($addColumnQuery);
}

// Fetch request details before deletion for logging
$query = "SELECT r.*, u.full_name FROM requests r 
          LEFT JOIN users u ON r.user_id = u.id 
          WHERE r.id = $id LIMIT 1";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("❌ Request not found.");
}

$request = $result->fetch_assoc();

// Delete the request
$deleteQuery = "DELETE FROM requests WHERE id = $id";
$deleteResult = $conn->query($deleteQuery);

if ($deleteResult) {
    // Log the deletion
    $logMessage = "Request #{$id} ({$request['form_type']}) submitted by {$request['full_name']} was deleted by admin";
    $currentUser = getCurrentUser();
    $username = $currentUser ? $currentUser['name'] : 'Unknown';
    logAction($username, $logMessage);
    
    // Redirect back to requests page with success message
    header("Location: request.php?deleted=1");
    exit();
} else {
    die("❌ Failed to delete request: " . $conn->error);
}
?>
