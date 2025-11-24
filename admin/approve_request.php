<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';
requireLogin();

// ✅ Check admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access. Only administrators can approve requests.");
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

// Check if this is a service request that needs technician assignment
$technician_id = $_GET['technician_id'] ?? null;
$isServiceRequest = ($request['form_type'] === 'ICT Service Request Form');

// If it's a service request, technician assignment is required
if ($isServiceRequest && !$technician_id) {
    // Redirect back with error message - technician must be assigned
    header("Location: request.php?error=Please+assign+a+technician+when+approving+service+requests");
    exit;
}

$stmt = $conn->prepare("UPDATE requests SET status = 'Approved' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // If this is a service request, also update the service_requests table with technician assignment
    if ($isServiceRequest && $technician_id) {
        // Find the corresponding service_requests record
        // First try to match by user_id and form data (equipment, requirements)
        $formData = json_decode($request['form_data'] ?? '{}', true);
        $equipment = $formData['equipment'] ?? '';
        $requirements = $formData['requirements'] ?? '';
        
        // Try multiple matching strategies for reliability
        $serviceRequestUpdate = null;
        $rowsAffected = 0;
        
        // Strategy 1: Match by user_id, equipment, requirements, and created within 7 days
        if (!empty($equipment) && !empty($requirements)) {
            // First, find the ID of the matching service request
            $findServiceRequest = $conn->prepare("
                SELECT id FROM service_requests 
                WHERE user_id = ? 
                AND status = 'Pending' 
                AND technician_id IS NULL
                AND TRIM(equipment) = TRIM(?)
                AND TRIM(requirements) = TRIM(?)
                AND created_at >= DATE_SUB(?, INTERVAL 7 DAY)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            if ($findServiceRequest) {
                $requestCreatedAt = $request['created_at'];
                $findServiceRequest->bind_param("isss", $request['user_id'], $equipment, $requirements, $requestCreatedAt);
                $findServiceRequest->execute();
                $serviceRequestResult = $findServiceRequest->get_result();
                
                if ($serviceRequestRow = $serviceRequestResult->fetch_assoc()) {
                    $serviceRequestId = $serviceRequestRow['id'];
                    $findServiceRequest->close();
                    
                    // Now update the specific service request
                    $serviceRequestUpdate = $conn->prepare("
                        UPDATE service_requests 
                        SET technician_id = ?, status = 'Assigned', updated_at = NOW() 
                        WHERE id = ?
                    ");
                    
                    if ($serviceRequestUpdate) {
                        $serviceRequestUpdate->bind_param("ii", $technician_id, $serviceRequestId);
                        $serviceRequestUpdate->execute();
                        $rowsAffected = $serviceRequestUpdate->affected_rows;
                        $serviceRequestUpdate->close();
                    }
                } else {
                    $findServiceRequest->close();
                }
            }
        }
        
        // Strategy 2: If no match found, use the most recent unassigned pending request for this user
        if ($rowsAffected === 0) {
            // First, find the ID of the most recent unassigned service request
            $findServiceRequest = $conn->prepare("
                SELECT id FROM service_requests 
                WHERE user_id = ? 
                AND status = 'Pending' 
                AND technician_id IS NULL
                AND created_at >= DATE_SUB(?, INTERVAL 7 DAY)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            if ($findServiceRequest) {
                $requestCreatedAt = $request['created_at'];
                $findServiceRequest->bind_param("is", $request['user_id'], $requestCreatedAt);
                $findServiceRequest->execute();
                $serviceRequestResult = $findServiceRequest->get_result();
                
                if ($serviceRequestRow = $serviceRequestResult->fetch_assoc()) {
                    $serviceRequestId = $serviceRequestRow['id'];
                    $findServiceRequest->close();
                    
                    // Now update the specific service request
                    $serviceRequestUpdate = $conn->prepare("
                        UPDATE service_requests 
                        SET technician_id = ?, status = 'Assigned', updated_at = NOW() 
                        WHERE id = ?
                    ");
                    
                    if ($serviceRequestUpdate) {
                        $serviceRequestUpdate->bind_param("ii", $technician_id, $serviceRequestId);
                        $serviceRequestUpdate->execute();
                        $rowsAffected = $serviceRequestUpdate->affected_rows;
                        $serviceRequestUpdate->close();
                    }
                } else {
                    $findServiceRequest->close();
                }
            }
        }
        
        // Log if assignment failed
        if ($rowsAffected === 0) {
            error_log("Warning: Failed to assign technician_id $technician_id to service request for user_id {$request['user_id']} (request_id: $id)");
        }
    }
    
    // Send notification to department admin
    if ($request['email']) {
        notifyDeptAdminRequestStatus($id, $request['form_type'], 'Approved', $request['email'], $adminName);
    }
    
    if (function_exists('addLog')) {
        addLog($conn, $_SESSION['user_id'], "Admin approved request ID: $id" . ($technician_id ? " and assigned technician ID: $technician_id" : ""));
    }
    header("Location: request.php?msg=Request+Approved+Successfully" . ($technician_id ? "+and+Technician+Assigned" : ""));
    exit;
} else {
    die("Database Error: " . $conn->error);
}

$stmt->close();
$conn->close();
?>