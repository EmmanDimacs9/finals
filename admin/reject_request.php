<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';
requireLogin();

// ✅ Check admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access. Only administrators can reject requests.");
}

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id <= 0) {
    die("Invalid request ID.");
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

// Get request details before updating
$requestQuery = "SELECT r.*, u.full_name, u.email FROM requests r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.id = ?";
$requestStmt = $conn->prepare($requestQuery);
$requestStmt->bind_param("i", $id);
$requestStmt->execute();
$requestResult = $requestStmt->get_result();
$request = $requestResult->fetch_assoc();
$requestStmt->close();

if (!$request) {
    die("Request not found.");
}

// Get admin details
$currentUser = getCurrentUser();
$adminName = $currentUser ? $currentUser['name'] : 'Admin';

$stmt = $conn->prepare("UPDATE requests SET status = 'Rejected' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Send notification to department admin
    if ($request['email']) {
        notifyDeptAdminRequestStatus($id, $request['form_type'], 'Rejected', $request['email'], $adminName);
    }
    
    if (function_exists('addLog')) {
        addLog($conn, $_SESSION['user_id'], "Admin rejected request ID: $id");
    }
    header("Location: request.php?msg=Request+Rejected+Successfully");
    exit;
} else {
    die("Database Error: " . $conn->error);
}

$stmt->close();
$conn->close();
?>
