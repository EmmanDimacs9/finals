<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false, 'message' => 'Invalid action'];

function ensureMaintenanceColumns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $tableExists = $conn->query("SHOW TABLES LIKE 'maintenance_records'");
    if (!$tableExists || $tableExists->num_rows === 0) {
        $checked = true;
        return;
    }

    $columns = [
        'updated_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()",
        'support_level' => "ALTER TABLE `maintenance_records` ADD COLUMN `support_level` varchar(10) DEFAULT NULL",
        'processing_time' => "ALTER TABLE `maintenance_records` ADD COLUMN `processing_time` varchar(255) DEFAULT NULL",
        'processing_deadline' => "ALTER TABLE `maintenance_records` ADD COLUMN `processing_deadline` datetime DEFAULT NULL",
        'completed_within_sla' => "ALTER TABLE `maintenance_records` ADD COLUMN `completed_within_sla` tinyint(1) DEFAULT NULL",
        'request_id' => "ALTER TABLE `maintenance_records` ADD COLUMN `request_id` int(11) DEFAULT NULL",
        'started_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `started_at` datetime DEFAULT NULL",
        'completed_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `completed_at` datetime DEFAULT NULL"
    ];

    foreach ($columns as $column => $query) {
        $columnExists = $conn->query("SHOW COLUMNS FROM `maintenance_records` LIKE '{$column}'");
        if ($columnExists && $columnExists->num_rows === 0) {
            $conn->query($query);
        }
    }

    $checked = true;
}

function ensureServiceRequestSlaColumns($conn) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $tableExists = $conn->query("SHOW TABLES LIKE 'service_requests'");
    if (!$tableExists || $tableExists->num_rows === 0) {
        $checked = true;
        return;
    }

    $deadlineColumn = $conn->query("SHOW COLUMNS FROM `service_requests` LIKE 'processing_deadline'");
    if ($deadlineColumn && $deadlineColumn->num_rows === 0) {
        $conn->query("ALTER TABLE `service_requests` ADD COLUMN `processing_deadline` DATETIME DEFAULT NULL");
    }

    $slaColumn = $conn->query("SHOW COLUMNS FROM `service_requests` LIKE 'completed_within_sla'");
    if ($slaColumn && $slaColumn->num_rows === 0) {
        $conn->query("ALTER TABLE `service_requests` ADD COLUMN `completed_within_sla` TINYINT(1) DEFAULT NULL");
    }

    $checked = true;
}

function getSupportLevelDurationMinutes($level) {
    return match (strtoupper($level)) {
        'L1' => 65,                    // 1 hour 5 minutes
        'L2' => 125,                   // 2 hours 5 minutes
        'L3' => (2 * 24 * 60) + 5,     // 2 days 5 minutes
        'L4' => (5 * 24 * 60) + 5,     // 5 days 5 minutes
        default => null,
    };
}

try {
    if (!isset($input['action'])) {
        throw new Exception('Action is required');
    }

    switch ($input['action']) {
        /* ================= TASKS ================= */
        case 'get_tasks':
            $status = $input['status'] ?? null;
            $user_id = $input['user_id'] ?? 0;

            $query = "
                SELECT t.*, u.full_name as assigned_to_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.assigned_to = ? AND t.status = ?
                ORDER BY t.created_at DESC
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user_id, $status);
            $stmt->execute();
            $result = $stmt->get_result();

            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $tasks;
            break;

        case 'update_status':
            if (!isset($input['task_id'], $input['new_status'])) {
                throw new Exception('Task ID and new status are required');
            }

            $task_id = intval($input['task_id']);
            $new_status = $input['new_status'];
            $remarks = $input['remarks'] ?? '';

            if ($new_status === 'completed') {
                $stmt = $conn->prepare("UPDATE tasks SET status = ?, remarks = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $new_status, $remarks, $task_id);
            } else {
                $stmt = $conn->prepare("UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_status, $task_id);
            }

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Task updated successfully';
            } else {
                throw new Exception('Failed to update task: ' . $stmt->error);
            }
            break;

        case 'delete_task':
            if (!isset($input['task_id'])) {
                throw new Exception('Task ID is required');
            }

            $task_id = intval($input['task_id']);
            $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND status = 'completed'");
            $stmt->bind_param("i", $task_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Task removed from board.';
            } else {
                throw new Exception('Task not found or not yet completed.');
            }
            break;

        /* ================= MAINTENANCE ================= */
        case 'get_maintenance':
            $user_id = $input['user_id'] ?? 0;
            ensureMaintenanceColumns($conn);

            $query = "
                SELECT mr.*, u.full_name as assigned_to_name
                FROM maintenance_records mr
                LEFT JOIN users u ON mr.technician_id = u.id
                WHERE mr.technician_id = ?
                ORDER BY mr.created_at DESC
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $records = [];
            while ($row = $result->fetch_assoc()) {
                // Map status to match kanban columns
                if ($row['status'] === 'scheduled') {
                    $row['status'] = 'pending';
                } elseif ($row['status'] === 'in_progress') {
                    $row['status'] = 'in_progress';
                } elseif ($row['status'] === 'completed') {
                    $row['status'] = 'completed';
                } else {
                    $row['status'] = 'pending';
                }
                $records[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $records;
            break;

        case 'get_maintenance_details':
            if (!isset($input['maintenance_id'])) {
                throw new Exception('Maintenance ID is required');
            }
            ensureMaintenanceColumns($conn);
            $maintenance_id = intval($input['maintenance_id']);

            $stmt = $conn->prepare("
                SELECT 
                    mr.*, 
                    u.full_name as assigned_to_name,
                    r.form_type as request_form_type,
                    r.form_data as request_form_data,
                    r.created_at as request_created_at
                FROM maintenance_records mr
                LEFT JOIN users u ON mr.technician_id = u.id
                LEFT JOIN requests r ON mr.request_id = r.id
                WHERE mr.id = ?
            ");
            $stmt->bind_param("i", $maintenance_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();

            if (!$record) {
                throw new Exception('Maintenance record not found');
            }

            if ($record['status'] === 'scheduled') {
                $record['status'] = 'pending';
            } elseif ($record['status'] === 'in_progress') {
                $record['status'] = 'in_progress';
            } elseif ($record['status'] === 'completed') {
                $record['status'] = 'completed';
            } else {
                $record['status'] = 'pending';
            }

            if (isset($record['request_form_data']) && $record['request_form_data']) {
                $decoded = json_decode($record['request_form_data'], true);
                $record['request_form_data'] = is_array($decoded) ? $decoded : null;
            } else {
                $record['request_form_data'] = null;
            }

            $response['success'] = true;
            $response['record'] = $record;
            break;

        case 'assign_maintenance_support_level':
            ensureMaintenanceColumns($conn);
            if (!isset($input['maintenance_id'], $input['support_level'], $input['processing_time'])) {
                throw new Exception('Maintenance ID, support level, and processing time are required');
            }

            $maintenance_id = intval($input['maintenance_id']);
            $support_level = $input['support_level'];
            $processing_time = $input['processing_time'];
            $notes = trim($input['notes'] ?? '');

            $minutes = getSupportLevelDurationMinutes($support_level);
            if ($minutes === null) {
                throw new Exception('Invalid support level');
            }
            $deadline = date('Y-m-d H:i:s', time() + ($minutes * 60));

            $update_fields = [
                "support_level = ?",
                "processing_time = ?",
                "processing_deadline = ?",
                "status = 'in_progress'",
                "updated_at = NOW()"
            ];
            $params = [$support_level, $processing_time, $deadline];
            $types = "sss";

            // Add start_time if provided, otherwise set to NOW()
            $start_time = $input['start_time'] ?? null;
            if ($start_time) {
                // Convert to MySQL datetime format if needed
                $start_time_formatted = date('Y-m-d H:i:s', strtotime($start_time));
                $update_fields[] = "started_at = ?";
                $params[] = $start_time_formatted;
                $types .= "s";
            } else {
                // If no start_time provided, set it to current time (no parameter needed)
                $update_fields[] = "started_at = NOW()";
            }

            if ($notes !== '') {
                $update_fields[] = "description = ?";
                $params[] = $notes;
                $types .= "s";
            }

            $setClause = implode(', ', $update_fields);
            $stmt = $conn->prepare("UPDATE maintenance_records SET $setClause WHERE id = ?");
            $params[] = $maintenance_id;
            $types .= "i";
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Support level assigned. Deadline created based on SLA.';
            } else {
                throw new Exception('Failed to assign support level: ' . $stmt->error);
            }
            break;

        case 'accept_maintenance_request':
            if (!isset($input['maintenance_id'])) {
                throw new Exception('Maintenance ID is required');
            }

            ensureMaintenanceColumns($conn);
            $maintenance_id = intval($input['maintenance_id']);

            $stmt = $conn->prepare("
                UPDATE maintenance_records 
                SET status = 'in_progress', updated_at = NOW() 
                WHERE id = ? AND (status = 'scheduled' OR status = 'pending')
            ");
            $stmt->bind_param("i", $maintenance_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Maintenance request accepted and moved to In Progress.';
                } else {
                    throw new Exception('Maintenance request not found or already accepted.');
                }
            } else {
                throw new Exception('Failed to accept maintenance request: ' . $stmt->error);
            }
            break;

        case 'delete_maintenance':
            if (!isset($input['maintenance_id'])) {
                throw new Exception('Maintenance ID is required');
            }

            ensureMaintenanceColumns($conn);
            $maintenance_id = intval($input['maintenance_id']);
            $stmt = $conn->prepare("DELETE FROM maintenance_records WHERE id = ? AND status = 'completed'");
            $stmt->bind_param("i", $maintenance_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Maintenance record removed from board.';
            } else {
                throw new Exception('Maintenance record not found or not yet completed.');
            }
            break;

        case 'update_maintenance_status':
            if (!isset($input['maintenance_id'], $input['new_status'])) {
                throw new Exception('Maintenance ID and new status are required');
            }

            ensureMaintenanceColumns($conn);
            $maintenance_id = intval($input['maintenance_id']);
            $new_status = $input['new_status'];
            $remarks = $input['remarks'] ?? '';
            $support_level = $input['support_level'] ?? null;
            $processing_time = $input['processing_time'] ?? null;

            $db_status = $new_status === 'pending' ? 'scheduled' : ($new_status === 'in_progress' ? 'in_progress' : 'completed');

            $update_fields = ["status = ?"];
            $params = [$db_status];
            $types = "s";

            if ($new_status === 'completed') {
                $update_fields[] = "remarks = ?";
                $params[] = $remarks;
                $types .= "s";
                
                // Add end_time (completed_at) if provided
                $end_time = $input['end_time'] ?? null;
                if ($end_time) {
                    // Convert to MySQL datetime format if needed
                    $end_time_formatted = date('Y-m-d H:i:s', strtotime($end_time));
                    $update_fields[] = "completed_at = ?";
                    $params[] = $end_time_formatted;
                    $types .= "s";
                } else {
                    // If no end_time provided, set it to current time
                    $update_fields[] = "completed_at = NOW()";
                }
            } elseif ($remarks !== '') {
                $update_fields[] = "remarks = ?";
                $params[] = $remarks;
                $types .= "s";
            }

            if ($support_level !== null) {
                $update_fields[] = "support_level = ?";
                $params[] = $support_level;
                $types .= "s";
            }
            if ($processing_time !== null) {
                $update_fields[] = "processing_time = ?";
                $params[] = $processing_time;
                $types .= "s";
            }

            if ($db_status === 'completed') {
                $update_fields[] = "completed_within_sla = CASE WHEN processing_deadline IS NULL THEN NULL WHEN NOW() <= processing_deadline THEN 1 ELSE 0 END";
            }

            $update_fields[] = "updated_at = NOW()";
            $setClause = implode(', ', $update_fields);

            $stmt = $conn->prepare("UPDATE maintenance_records SET $setClause WHERE id = ?");
            $params[] = $maintenance_id;
            $types .= "i";
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Maintenance updated successfully';
            } else {
                throw new Exception('Failed to update maintenance: ' . $stmt->error);
            }
            break;

        /* ================= SERVICE REQUESTS ================= */
        case 'get_service_requests':
            ensureServiceRequestSlaColumns($conn);
            $status = $input['status'] ?? null;
            $technician_id = $input['technician_id'] ?? null;

            $where = [];
            $params = [];
            $types = '';

            if ($status && $technician_id) {
                if ($status === 'Pending' || $status === 'pending') {
                    $where[] = "sr.status = 'Pending' AND sr.technician_id = ?";
                    $params[] = $technician_id;
                    $types = 'i';
                } elseif ($status === 'In Progress' || $status === 'in_progress') {
                    // Include both 'Assigned' and 'In Progress' statuses since both map to 'in_progress' column
                    $where[] = "(sr.status = 'In Progress' OR sr.status = 'Assigned') AND sr.technician_id = ?";
                    $params[] = $technician_id;
                    $types = 'i';
                } else {
                    $where[] = "sr.status = ? AND sr.technician_id = ?";
                    $params[] = $status;
                    $params[] = $technician_id;
                    $types = 'si';
                }
            } elseif ($status) {
                // When no technician context is provided (e.g., admin view), fall back to status-only filtering
                if ($status === 'Pending' || $status === 'pending') {
                    $where[] = "sr.status = 'Pending'";
                } elseif ($status === 'In Progress' || $status === 'in_progress') {
                    $where[] = "(sr.status = 'In Progress' OR sr.status = 'Assigned')";
                } else {
                    $where[] = "sr.status = ?";
                    $params[] = $status;
                    $types = 's';
                }
            } elseif ($technician_id) {
                $where[] = "sr.technician_id = ?";
                $params[] = $technician_id;
                $types = 'i';
            }

            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            $query = "
                SELECT 
                    sr.*, 
                    u.full_name AS client_name_full,
                    (SELECT COUNT(*) FROM service_surveys ss WHERE ss.service_request_id = sr.id) AS survey_count,
                    (SELECT AVG((ss.eval_response + ss.eval_quality + ss.eval_courtesy + ss.eval_overall)/4) FROM service_surveys ss WHERE ss.service_request_id = sr.id) AS survey_average,
                    (SELECT ss.comments FROM service_surveys ss WHERE ss.service_request_id = sr.id AND ss.comments IS NOT NULL AND ss.comments != '' ORDER BY ss.submitted_at DESC LIMIT 1) AS survey_latest_comment,
                    (SELECT ss.submitted_at FROM service_surveys ss WHERE ss.service_request_id = sr.id ORDER BY ss.submitted_at DESC LIMIT 1) AS survey_latest_at
                FROM service_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                $where_clause
                ORDER BY sr.created_at DESC
            ";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            while ($row = $result->fetch_assoc()) {
                // Map status to match kanban columns
                if ($row['status'] === 'Assigned') {
                    $row['status'] = 'in_progress';
                } elseif ($row['status'] === 'In Progress') {
                    $row['status'] = 'in_progress';
                } elseif ($row['status'] === 'Completed') {
                    $row['status'] = 'completed';
                } else {
                    $row['status'] = 'pending';
                }
                $requests[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $requests;
            break;

        case 'accept_service_request':
            if (!isset($input['request_id'], $input['technician_id'])) {
                throw new Exception('Request ID and Technician ID are required');
            }

            // Generate ICT SRF No if not exists
            $srf_no = $input['ict_srf_no'] ?? 'SRF-' . date('Y') . '-' . str_pad($input['request_id'], 5, '0', STR_PAD_LEFT);

            $stmt = $conn->prepare("
                UPDATE service_requests 
                SET technician_id = ?, ict_srf_no = ?, status = 'In Progress', updated_at = NOW() 
                WHERE id = ? AND status = 'Pending'
            ");
            $stmt->bind_param("isi", $input['technician_id'], $srf_no, $input['request_id']);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Service request accepted successfully';
                } else {
                    throw new Exception('Request not found or already assigned');
                }
            } else {
                throw new Exception('Failed to accept request: ' . $stmt->error);
            }
            break;

        case 'update_service_request':
            if (!isset($input['request_id'], $input['new_status'])) {
                throw new Exception('Request ID and new status are required');
            }

            ensureServiceRequestSlaColumns($conn);
            $request_id = intval($input['request_id']);
            $new_status = $input['new_status'];
            $support_level = $input['support_level'] ?? null;
            $processing_time = $input['processing_time'] ?? null;
            $accomplishment = $input['accomplishment'] ?? null;
            $remarks = $input['remarks'] ?? null;

            // Map kanban status to database status
            $db_status = $new_status === 'pending' ? 'Pending' : ($new_status === 'in_progress' ? 'In Progress' : 'Completed');

            $update_fields = ["status = ?"];
            $params = [$db_status];
            $types = "s";

            $deadlineToSet = null;
            $supportMinutes = null;

            if ($support_level !== null) {
                $update_fields[] = "support_level = ?";
                $params[] = $support_level;
                $types .= "s";
                $supportMinutes = getSupportLevelDurationMinutes($support_level);
            }
            if ($processing_time !== null) {
                $update_fields[] = "processing_time = ?";
                $params[] = $processing_time;
                $types .= "s";
            }
            if ($accomplishment !== null) {
                $update_fields[] = "accomplishment = ?";
                $params[] = $accomplishment;
                $types .= "s";
            }
            if ($remarks !== null) {
                $update_fields[] = "remarks = ?";
                $params[] = $remarks;
                $types .= "s";
            }

            if ($db_status === 'Completed') {
                $update_fields[] = "completed_at = NOW()";
                $update_fields[] = "completed_within_sla = CASE WHEN processing_deadline IS NULL THEN NULL WHEN NOW() <= processing_deadline THEN 1 ELSE 0 END";
            }

            if ($db_status === 'In Progress' && $supportMinutes !== null) {
                $deadlineToSet = date('Y-m-d H:i:s', time() + ($supportMinutes * 60));
                $update_fields[] = "processing_deadline = ?";
                $params[] = $deadlineToSet;
                $types .= "s";
            }

            $update_fields[] = "updated_at = NOW()";
            $params[] = $request_id;
            $types .= "i";

            $stmt = $conn->prepare("UPDATE service_requests SET " . implode(", ", $update_fields) . " WHERE id = ?");
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                // If completed, send notification to department
                if ($db_status === 'Completed') {
                    // Get request details for notification
                    $notifQuery = "SELECT sr.*, u.email, u.full_name as client_name 
                                  FROM service_requests sr 
                                  LEFT JOIN users u ON sr.user_id = u.id 
                                  WHERE sr.id = ?";
                    $notifStmt = $conn->prepare($notifQuery);
                    $notifStmt->bind_param("i", $request_id);
                    $notifStmt->execute();
                    $notifResult = $notifStmt->get_result();
                    $requestData = $notifResult->fetch_assoc();
                    $notifStmt->close();
                    
                    // Send notification to department (if notifications are available)
                    if ($requestData && function_exists('notifyDeptAdminRequestStatus')) {
                        try {
                            notifyDeptAdminRequestStatus(
                                $request_id,
                                'ICT Service Request',
                                'Completed',
                                $requestData['email'],
                                'ICT Staff',
                                $remarks
                            );
                        } catch (Exception $e) {
                            // Log but don't fail the request
                            error_log("Notification error: " . $e->getMessage());
                        }
                    }
                }
                
                $response['success'] = true;
                $response['message'] = $db_status === 'Completed' 
                    ? 'Service request completed successfully. Department has been notified.' 
                    : 'Service request updated successfully';
            } else {
                throw new Exception('Failed to update request: ' . $stmt->error);
            }
            break;

        case 'delete_service_request':
            if (!isset($input['request_id'])) {
                throw new Exception('Request ID is required');
            }

            $request_id = intval($input['request_id']);
            $stmt = $conn->prepare("DELETE FROM service_requests WHERE id = ? AND status = 'Completed'");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Service request removed from board.';
            } else {
                throw new Exception('Request not found or not yet completed.');
            }
            break;

        case 'get_service_request_surveys':
            if (!isset($input['request_id'])) {
                throw new Exception('Request ID is required');
            }

            $request_id = intval($input['request_id']);
            
            // Fetch all surveys for this service request
            $query = "
                SELECT 
                    ss.*,
                    u.full_name as user_name
                FROM service_surveys ss
                LEFT JOIN users u ON ss.user_id = u.id
                WHERE ss.service_request_id = ?
                ORDER BY ss.submitted_at DESC
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $surveys = [];
            while ($row = $result->fetch_assoc()) {
                $surveys[] = $row;
            }
            
            // Calculate average rating
            $average = null;
            if (count($surveys) > 0) {
                $total = 0;
                foreach ($surveys as $survey) {
                    $total += (intval($survey['eval_response']) + intval($survey['eval_quality']) + intval($survey['eval_courtesy']) + intval($survey['eval_overall'])) / 4;
                }
                $average = $total / count($surveys);
            }
            
            $response['success'] = true;
            $response['surveys'] = $surveys;
            $response['average'] = $average;
            break;

        case 'get_system_requests':
            $status = $input['status'] ?? null;
            $technician_id = $input['technician_id'] ?? null;

            $where = [];
            $params = [];
            $types = '';

            // For pending requests, show all. For assigned/in progress/completed, show only assigned to this technician
            if ($status === 'Pending' || $status === 'pending') {
                $where[] = "sr.status = 'Pending'";
            } elseif (($status === 'In Progress' || $status === 'in_progress') && $technician_id) {
                $where[] = "sr.status = 'In Progress' AND sr.technician_id = ?";
                $params[] = $technician_id;
                $types = 'i';
            } elseif ($status && $technician_id) {
                $where[] = "sr.status = ? AND sr.technician_id = ?";
                $params[] = $status;
                $params[] = $technician_id;
                $types = 'si';
            } elseif ($technician_id) {
                $where[] = "(sr.technician_id = ? OR sr.status = 'Pending')";
                $params[] = $technician_id;
                $types = 'i';
            }

            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            $query = "
                SELECT 
                    sr.*, 
                    u.full_name AS client_name_full
                FROM system_requests sr
                LEFT JOIN users u ON sr.user_id = u.id
                $where_clause
                ORDER BY sr.created_at DESC
            ";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $requests = [];
            while ($row = $result->fetch_assoc()) {
                // Map status to match kanban columns
                if ($row['status'] === 'In Progress') {
                    $row['status'] = 'in_progress';
                } elseif ($row['status'] === 'Completed') {
                    $row['status'] = 'completed';
                } else {
                    $row['status'] = 'pending';
                }
                $requests[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $requests;
            break;

        case 'accept_system_request':
            if (!isset($input['request_id'], $input['technician_id'])) {
                throw new Exception('Request ID and Technician ID are required');
            }

            $request_id = intval($input['request_id']);
            $technician_id = intval($input['technician_id']);

            $stmt = $conn->prepare("
                UPDATE system_requests 
                SET technician_id = ?, status = 'In Progress', updated_at = NOW() 
                WHERE id = ? AND status = 'Pending'
            ");
            $stmt->bind_param("ii", $technician_id, $request_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'System request accepted successfully';
            } else {
                throw new Exception('Failed to accept system request: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'update_system_request_status':
            if (!isset($input['request_id'], $input['new_status'])) {
                throw new Exception('Request ID and new status are required');
            }

            $request_id = intval($input['request_id']);
            $new_status = $input['new_status'];
            $remarks = $input['remarks'] ?? '';

            // Map status
            $db_status = $new_status === 'in_progress' ? 'In Progress' : ($new_status === 'completed' ? 'Completed' : 'Pending');

            $stmt = $conn->prepare("
                UPDATE system_requests 
                SET status = ?, remarks = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("ssi", $db_status, $remarks, $request_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = $db_status === 'Completed' 
                    ? 'System request completed successfully. Department has been notified.' 
                    : 'System request updated successfully';
            } else {
                throw new Exception('Failed to update system request: ' . $stmt->error);
            }
            $stmt->close();
            break;

        default:
            throw new Exception('Invalid action: ' . $input['action']);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);

