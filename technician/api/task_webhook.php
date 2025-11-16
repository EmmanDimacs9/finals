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

        /* ================= MAINTENANCE ================= */
        case 'get_maintenance':
            $user_id = $input['user_id'] ?? 0;

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

        case 'update_maintenance_status':
            if (!isset($input['maintenance_id'], $input['new_status'])) {
                throw new Exception('Maintenance ID and new status are required');
            }

            $maintenance_id = intval($input['maintenance_id']);
            $new_status = $input['new_status'];
            $remarks = $input['remarks'] ?? '';

            // Map kanban status to database status
            $db_status = $new_status === 'pending' ? 'scheduled' : ($new_status === 'in_progress' ? 'in_progress' : 'completed');

            if ($new_status === 'completed') {
                $stmt = $conn->prepare("UPDATE maintenance_records SET status = ?, remarks = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $db_status, $remarks, $maintenance_id);
            } else {
                $stmt = $conn->prepare("UPDATE maintenance_records SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $db_status, $maintenance_id);
            }

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Maintenance updated successfully';
            } else {
                throw new Exception('Failed to update maintenance: ' . $stmt->error);
            }
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
            } elseif (($status === 'In Progress' || $status === 'in_progress') && $technician_id) {
                // Include both 'Assigned' and 'In Progress' statuses since both map to 'in_progress' column
                $where[] = "(sr.status = 'In Progress' OR sr.status = 'Assigned') AND sr.technician_id = ?";
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

            if ($db_status === 'Completed') {
                $update_fields[] = "completed_at = NOW()";
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

