<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';
require_once '../includes/logs.php';
requireLogin();

if (!function_exists('createRequestRecord')) {
    function createRequestRecord($conn, $user_id, $form_type, $form_data) {
        $stmt = $conn->prepare("INSERT INTO requests (user_id, form_type, form_data, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
        if (!$stmt) {
            error_log("❌ Failed to prepare requests insert: " . $conn->error);
            return false;
        }

        $stmt->bind_param("iss", $user_id, $form_type, $form_data);
        $result = $stmt->execute();
        if (!$result) {
            error_log("❌ Failed to execute requests insert: " . $stmt->error);
            $stmt->close();
            return false;
        }

        $insertId = $stmt->insert_id;
        $stmt->close();

        // Ensure the form type is approved by default so department heads see pending status in new modal
        $statusUpdate = $conn->prepare("UPDATE requests SET status = 'Pending' WHERE id = ?");
        if ($statusUpdate) {
            $statusUpdate->bind_param("i", $insertId);
            $statusUpdate->execute();
            $statusUpdate->close();
        }

        return $insertId;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $form_type = $_POST['form_type'] ?? '';
    $form_data = json_encode($_POST); // Store all form data as JSON

    if ($form_type === '' || $user_id === 0) {
        echo "⚠️ Missing data.";
        exit;
    }

    // Handle ICT Service Request Form specifically - save to service_requests table
    if ($form_type === 'ICT Service Request Form') {
        $campus = $_POST['campus'] ?? '';
        $client_name = $_POST['client_name'] ?? '';
        $office = $_POST['office'] ?? '';
        $equipment = $_POST['equipment'] ?? '';
        $requirements = $_POST['requirements'] ?? '';
        $location = $_POST['location'] ?? '';
        $date_time_call = $_POST['date_time_call'] ?? date('Y-m-d H:i:s');

        if (empty($client_name) || empty($equipment) || empty($requirements)) {
            echo "⚠️ Please fill in all required fields.";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO service_requests (user_id, campus, client_name, office, equipment, requirements, location, date_time_call, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        
        if (!$stmt) {
            echo "❌ Error preparing statement: " . $conn->error;
            exit;
        }
        
        $stmt->bind_param("isssssss", $user_id, $campus, $client_name, $office, $equipment, $requirements, $location, $date_time_call);

        if ($stmt->execute()) {
            $requestId = $conn->insert_id;
            
            // Get user details for notification
            $userQuery = "SELECT full_name, email FROM users WHERE id = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("i", $user_id);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            $userStmt->close();
            
            // Log the service request creation
            $userName = $_SESSION['user_name'] ?? $client_name;
            logAction($userName, "Created Service Request: {$equipment} - " . substr($requirements, 0, 50));
            
            // Create department head-facing request entry and notify department head
            $adminRequestId = createRequestRecord($conn, $user_id, $form_type, $form_data);
            if ($user && $adminRequestId) {
                notifyAdminNewRequest($adminRequestId, $form_type, $user['full_name'], $user['email']);
            }
            
            echo "✅ Service request has been submitted successfully. The department head will review your request shortly.";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
        $stmt->close();
        exit;
    }

    // First, ensure the requests table exists with proper structure
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
        if (!$conn->query($addColumnQuery)) {
            echo "❌ Error adding form_data column: " . $conn->error;
            exit;
        }
    }

    // Handle System Request specifically - save to system_requests table for technicians
    if ($form_type === 'System Request') {
        $office = $_POST['office'] ?? '';
        $sysType = isset($_POST['sysType']) ? implode(', ', $_POST['sysType']) : '';
        $urgency = isset($_POST['urgency']) ? implode(', ', $_POST['urgency']) : '';
        $nameSystem = $_POST['nameSystem'] ?? '';
        $descRequest = $_POST['descRequest'] ?? '';
        $remarks = $_POST['remarks'] ?? '';

        if (empty($office) || empty($nameSystem) || empty($descRequest)) {
            echo "⚠️ Please fill in all required fields.";
            exit;
        }

        // Ensure system_requests table exists with proper structure
        $createSystemRequestsTable = "CREATE TABLE IF NOT EXISTS `system_requests` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `requesting_office` varchar(255) NOT NULL,
            `type_of_request` varchar(255) NOT NULL,
            `urgency` varchar(255) DEFAULT NULL,
            `system_name` varchar(255) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `remarks` text DEFAULT NULL,
            `technician_id` int(11) DEFAULT NULL,
            `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `technician_id` (`technician_id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $conn->query($createSystemRequestsTable);

        // Check and add columns if they don't exist (using safe approach without AFTER clauses)
        $existingColumns = [];
        $colsResult = $conn->query("SHOW COLUMNS FROM `system_requests`");
        while ($col = $colsResult->fetch_assoc()) {
            $existingColumns[] = $col['Field'];
        }
        
        // Add columns one by one, checking existence first
        // Use simple ADD COLUMN without AFTER to avoid dependency issues
        if (!in_array('description', $existingColumns)) {
            if (!$conn->query("ALTER TABLE `system_requests` ADD COLUMN `description` text DEFAULT NULL")) {
                error_log("Failed to add description column: " . $conn->error);
            } else {
                $existingColumns[] = 'description';
            }
        }
        
        if (!in_array('user_id', $existingColumns)) {
            if (!$conn->query("ALTER TABLE `system_requests` ADD COLUMN `user_id` int(11) NOT NULL")) {
                error_log("Failed to add user_id column: " . $conn->error);
            } else {
                $existingColumns[] = 'user_id';
            }
        }
        
        if (!in_array('remarks', $existingColumns)) {
            if (!$conn->query("ALTER TABLE `system_requests` ADD COLUMN `remarks` text DEFAULT NULL")) {
                error_log("Failed to add remarks column: " . $conn->error);
            } else {
                $existingColumns[] = 'remarks';
            }
        }
        
        if (!in_array('technician_id', $existingColumns)) {
            if (!$conn->query("ALTER TABLE `system_requests` ADD COLUMN `technician_id` int(11) DEFAULT NULL")) {
                error_log("Failed to add technician_id column: " . $conn->error);
            } else {
                $existingColumns[] = 'technician_id';
            }
        }
        
        if (!in_array('updated_at', $existingColumns)) {
            if (!$conn->query("ALTER TABLE `system_requests` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()")) {
                error_log("Failed to add updated_at column: " . $conn->error);
            }
        }

        $stmt = $conn->prepare("INSERT INTO system_requests (user_id, requesting_office, type_of_request, urgency, system_name, description, remarks, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        
        if (!$stmt) {
            echo "❌ Error preparing statement: " . $conn->error;
            exit;
        }
        
        $stmt->bind_param("issssss", $user_id, $office, $sysType, $urgency, $nameSystem, $descRequest, $remarks);

        if ($stmt->execute()) {
            $requestId = $conn->insert_id;
            
            // Get user details for notification
            $userQuery = "SELECT full_name, email FROM users WHERE id = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->bind_param("i", $user_id);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            $userStmt->close();
            
            // Log the system request creation
            $userName = $_SESSION['user_name'] ?? 'Unknown';
            logAction($userName, "Created System Request: {$nameSystem} - " . substr($descRequest, 0, 50));
            
            // Create department head-facing request entry and notify department head
            $adminRequestId = createRequestRecord($conn, $user_id, $form_type, $form_data);
            if ($user && $adminRequestId) {
                notifyAdminNewRequest($adminRequestId, $form_type, $user['full_name'], $user['email']);
            }
            
            echo "✅ System request has been submitted successfully. The department head will review your request shortly.";
        } else {
            echo "❌ Error: " . $stmt->error;
        }
        $stmt->close();
        exit;
    }

    // Insert into requests table with form data (for other form types)
    $requestId = createRequestRecord($conn, $user_id, $form_type, $form_data);
    if (!$requestId) {
        echo "❌ Error: Unable to submit your request at this time.";
        exit;
    }
    
    // Get user details for notification
    $userQuery = "SELECT full_name, email FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    $userStmt->close();
    
    // Log the request creation
    $userName = $_SESSION['user_name'] ?? 'Unknown';
    logAction($userName, "Created Request: {$form_type}");
    
    // Send notification to department head
    if ($user) {
        notifyAdminNewRequest($requestId, $form_type, $user['full_name'], $user['email']);
    }
    
    echo "✅ Request for '{$form_type}' has been sent and is waiting for department head approval.";
} else {
    echo "Invalid request method.";
}
?>
