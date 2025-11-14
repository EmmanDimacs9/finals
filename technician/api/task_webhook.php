<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../includes/db.php';
require_once '../../includes/notifications.php';

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    if (!isset($input['action'])) {
        throw new Exception('Action is required');
    }

    switch ($input['action']) {
        /* ================= TASKS ================= */
        case 'create_task':
            if (!isset($input['title'], $input['description'], $input['priority'], $input['due_date'], $input['assigned_to'], $input['assigned_by'])) {
                throw new Exception('Missing required fields for create_task');
            }

            $stmt = $conn->prepare("
                INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, due_date, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->bind_param(
                "ssiiss",
                $input['title'],
                $input['description'],
                $input['assigned_to'],
                $input['assigned_by'],
                $input['priority'],
                $input['due_date']
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Task created successfully';
                $response['data'] = ['task_id' => $conn->insert_id];
            } else {
                throw new Exception('Failed to create task: ' . $stmt->error);
            }
            break;

        case 'update_status':
            if (!isset($input['task_id'], $input['new_status'])) {
                throw new Exception('Task ID and new status are required');
            }

            if ($input['new_status'] === 'completed') {
                if (empty($input['remarks'])) {
                    throw new Exception('Remarks are required when completing a task');
                }
                $stmt = $conn->prepare("UPDATE tasks SET status = ?, remarks = ? WHERE id = ?");
                $stmt->bind_param("ssi", $input['new_status'], $input['remarks'], $input['task_id']);
            } else {
                $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $input['new_status'], $input['task_id']);
            }

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Task status updated successfully';
            } else {
                throw new Exception('Failed to update task: ' . $stmt->error);
            }
            break;

        case 'get_tasks':
            $status = $input['status'] ?? null;
            $user_id = $input['user_id'] ?? null;

            $where = [];
            $params = [];
            $types = '';

            if ($status) { $where[] = "t.status = ?"; $params[] = $status; $types .= 's'; }
            if ($user_id) { $where[] = "t.assigned_to = ?"; $params[] = $user_id; $types .= 'i'; }

            $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

            $query = "
                SELECT t.*, u.full_name AS assigned_to_name, u2.full_name AS assigned_by_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN users u2 ON t.assigned_by = u2.id
                $where_clause
                ORDER BY t.priority DESC, t.created_at ASC
            ";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $tasks;
            break;

        /* ================= MAINTENANCE ================= */
        case 'create_maintenance':
            if (!isset($input['equipment_id'], $input['equipment_type'], $input['technician_id'], $input['maintenance_type'])) {
                throw new Exception('Missing required fields for create_maintenance');
            }

            // Optional fields
            $description = $input['description'] ?? '';
            $cost = $input['cost'] ?? 0;
            $start_date = $input['start_date'] ?? date('Y-m-d');
            $end_date = $input['end_date'] ?? date('Y-m-d');

            $stmt = $conn->prepare("
                INSERT INTO maintenance_records 
                    (equipment_id, equipment_type, technician_id, maintenance_type, description, cost, start_date, end_date, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->bind_param(
                "isissdss",
                $input['equipment_id'],
                $input['equipment_type'],
                $input['technician_id'],
                $input['maintenance_type'],
                $description,
                $cost,
                $start_date,
                $end_date
            );

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Maintenance scheduled successfully';
                $response['data'] = ['maintenance_id' => $conn->insert_id];
            } else {
                throw new Exception('Failed to schedule maintenance: ' . $stmt->error);
            }
            break;

        case 'update_maintenance_status':
            if (!isset($input['maintenance_id'], $input['new_status'])) {
                throw new Exception('Maintenance ID and new status are required');
            }

            if ($input['new_status'] === 'completed') {
                if (empty($input['remarks'])) {
                    throw new Exception('Remarks are required when completing maintenance');
                }
                $stmt = $conn->prepare("UPDATE maintenance_records SET status = ?, remarks = ? WHERE id = ?");
                $stmt->bind_param("ssi", $input['new_status'], $input['remarks'], $input['maintenance_id']);
            } else {
                $stmt = $conn->prepare("UPDATE maintenance_records SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $input['new_status'], $input['maintenance_id']);
            }

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Maintenance status updated successfully';
            } else {
                throw new Exception('Failed to update maintenance: ' . $stmt->error);
            }
            break;

        case 'get_maintenance':
            $user_id = $input['user_id'] ?? null;

            $where = '';
            $params = [];
            $types = '';

            if ($user_id) {
                $where = "WHERE mr.technician_id = ?";
                $params[] = $user_id;
                $types = 'i';
            }

            $query = "
                SELECT mr.*, u.full_name AS assigned_to_name
                FROM maintenance_records mr
                LEFT JOIN users u ON mr.technician_id = u.id
                $where
                ORDER BY mr.created_at DESC
            ";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $records = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['status'] === 'scheduled') $row['status'] = 'pending';
                $records[] = $row;
            }

            $response['success'] = true;
            $response['data'] = $records;
            break;

        /* ================= SERVICE REQUESTS ================= */
        case 'get_service_requests':
            $status = $input['status'] ?? null;
            $technician_id = $input['technician_id'] ?? null;

            $where = [];
            $params = [];
            $types = '';

            // For pending requests, show all. For assigned/in progress/completed, show only assigned to this technician
            if ($status === 'Pending' || $status === 'pending') {
                $where[] = "sr.status = 'Pending'";
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

            $support_level = $input['support_level'] ?? null;
            $processing_time = $input['processing_time'] ?? null;
            $accomplishment = $input['accomplishment'] ?? null;
            $remarks = $input['remarks'] ?? null;

            // Map kanban status to database status
            $db_status = $input['new_status'];
            if ($db_status === 'in_progress') {
                $db_status = 'In Progress';
            } elseif ($db_status === 'completed') {
                $db_status = 'Completed';
            }

            if ($db_status === 'Completed') {
                $stmt = $conn->prepare("
                    UPDATE service_requests 
                    SET status = ?, support_level = ?, processing_time = ?, accomplishment = ?, remarks = ?, completed_at = NOW(), updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param("sssssi", $db_status, $support_level, $processing_time, $accomplishment, $remarks, $input['request_id']);
            } else {
                $update_fields = ["status = ?", "updated_at = NOW()"];
                $params = [$db_status];
                $types = "s";

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

                $params[] = $input['request_id'];
                $types .= "i";

                $stmt = $conn->prepare("UPDATE service_requests SET " . implode(", ", $update_fields) . " WHERE id = ?");
                $stmt->bind_param($types, ...$params);
            }

            if ($stmt->execute()) {
                // If completed, send notification to department
                if ($db_status === 'Completed') {
                    // Get request details for notification
                    $notifQuery = "SELECT sr.*, u.email, u.full_name as client_name 
                                  FROM service_requests sr 
                                  LEFT JOIN users u ON sr.user_id = u.id 
                                  WHERE sr.id = ?";
                    $notifStmt = $conn->prepare($notifQuery);
                    $notifStmt->bind_param("i", $input['request_id']);
                    $notifStmt->execute();
                    $notifResult = $notifStmt->get_result();
                    $requestData = $notifResult->fetch_assoc();
                    $notifStmt->close();
                    
                    // Send notification to department (if notifications are available)
                    if ($requestData && function_exists('notifyDeptAdminRequestStatus')) {
                        try {
                            notifyDeptAdminRequestStatus(
                                $input['request_id'],
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

        default:
            throw new Exception('Invalid action: ' . $input['action']);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);


