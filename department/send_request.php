<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/notifications.php';
requireLogin();

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
            
            // Send notification to technicians/admins
            if ($user) {
                notifyAdminNewRequest($requestId, $form_type, $user['full_name'], $user['email']);
            }
            
            echo "✅ Service request has been submitted successfully. A technician will receive your request shortly.";
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

    // Insert into requests table with form data
    $stmt = $conn->prepare("INSERT INTO requests (user_id, form_type, form_data, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
    
    if (!$stmt) {
        echo "❌ Error preparing statement: " . $conn->error;
        exit;
    }
    
    $stmt->bind_param("iss", $user_id, $form_type, $form_data);

    if ($stmt->execute()) {
        $requestId = $conn->insert_id; // Get the ID of the inserted request
        
        // Get user details for notification
        $userQuery = "SELECT full_name, email FROM users WHERE id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("i", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();
        $userStmt->close();
        
        // Send notification to admin
        if ($user) {
            notifyAdminNewRequest($requestId, $form_type, $user['full_name'], $user['email']);
        }
        
        echo "✅ Request for '{$form_type}' has been sent and is waiting for admin approval.";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid request method.";
}
?>
