<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

$message = '';
$error = '';

function extractEquipmentName($formData) {
    if (!is_array($formData)) {
        return 'N/A';
    }
    $candidates = [
        $formData['equipment'] ?? null,
        $formData['nameSystem'] ?? null,
        $formData['application'] ?? null,
        $formData['system_name'] ?? null
    ];
    foreach ($candidates as $value) {
        $value = trim((string)$value);
        if ($value !== '') {
            return $value;
        }
    }
    return 'N/A';
}

function assignServiceRequestToTechnician($conn, $request, $technician_id) {
    if (!$request) {
        return;
    }

    $userId = intval($request['user_id'] ?? 0);
    if ($userId <= 0) {
        return;
    }

    $formData = json_decode($request['form_data'] ?? '{}', true);
    $equipment = trim((string)($formData['equipment'] ?? ''));
    $requirements = trim((string)($formData['requirements'] ?? ''));
    $createdAt = $request['created_at'] ?? date('Y-m-d H:i:s');
    $serviceRequestId = null;

    if ($equipment !== '' && $requirements !== '') {
        $findStmt = $conn->prepare("
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
        if ($findStmt) {
            $findStmt->bind_param("isss", $userId, $equipment, $requirements, $createdAt);
            $findStmt->execute();
            $result = $findStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $serviceRequestId = $row['id'];
            }
            $findStmt->close();
        }
    }

    if (!$serviceRequestId) {
        $fallbackStmt = $conn->prepare("
            SELECT id FROM service_requests 
            WHERE user_id = ? 
            AND status = 'Pending' 
            AND technician_id IS NULL
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        if ($fallbackStmt) {
            $fallbackStmt->bind_param("i", $userId);
            $fallbackStmt->execute();
            $result = $fallbackStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $serviceRequestId = $row['id'];
            }
            $fallbackStmt->close();
        }
    }

    if (!$serviceRequestId) {
        $anyPendingStmt = $conn->prepare("
            SELECT id FROM service_requests 
            WHERE status = 'Pending' 
            AND technician_id IS NULL
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        if ($anyPendingStmt) {
            $anyPendingStmt->execute();
            $result = $anyPendingStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $serviceRequestId = $row['id'];
            }
            $anyPendingStmt->close();
        }
    }

    if ($serviceRequestId) {
        $updateStmt = $conn->prepare("
            UPDATE service_requests 
            SET technician_id = ?, status = 'Pending', updated_at = NOW() 
            WHERE id = ?
        ");
        if ($updateStmt) {
            $updateStmt->bind_param("ii", $technician_id, $serviceRequestId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
}

function assignSystemRequestToTechnician($conn, $request, $technician_id) {
    if (!$request) {
        return;
    }

    $userId = intval($request['user_id'] ?? 0);
    if ($userId <= 0) {
        return;
    }

    $formData = json_decode($request['form_data'] ?? '{}', true);
    $systemName = trim((string)($formData['nameSystem'] ?? $formData['system_name'] ?? ''));
    $createdAt = $request['created_at'] ?? date('Y-m-d H:i:s');
    $systemRequestId = null;

    if ($systemName !== '') {
        $findStmt = $conn->prepare("
            SELECT id FROM system_requests 
            WHERE user_id = ? 
            AND status = 'Pending' 
            AND (technician_id IS NULL OR technician_id = 0)
            AND TRIM(system_name) = TRIM(?) 
            AND created_at >= DATE_SUB(?, INTERVAL 7 DAY)
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        if ($findStmt) {
            $findStmt->bind_param("iss", $userId, $systemName, $createdAt);
            $findStmt->execute();
            $result = $findStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $systemRequestId = $row['id'];
            }
            $findStmt->close();
        }
    }

    if (!$systemRequestId) {
        $fallbackStmt = $conn->prepare("
            SELECT id FROM system_requests 
            WHERE user_id = ? 
            AND status = 'Pending' 
            AND (technician_id IS NULL OR technician_id = 0)
            AND created_at >= DATE_SUB(?, INTERVAL 7 DAY)
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        if ($fallbackStmt) {
            $fallbackStmt->bind_param("is", $userId, $createdAt);
            $fallbackStmt->execute();
            $result = $fallbackStmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $systemRequestId = $row['id'];
            }
            $fallbackStmt->close();
        }
    }

    if ($systemRequestId) {
        $updateStmt = $conn->prepare("
            UPDATE system_requests 
            SET technician_id = ?, status = 'Pending', updated_at = NOW() 
            WHERE id = ?
        ");
        if ($updateStmt) {
            $updateStmt->bind_param("ii", $technician_id, $systemRequestId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }
}

// Handle assignment of maintenance to technician
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'assign_maintenance') {
        $request_id = intval($_POST['request_id'] ?? 0);
        $technician_id = intval($_POST['technician_id'] ?? 0);
        $maintenance_type = $_POST['maintenance_type'] ?? 'corrective';
        $description = $_POST['description'] ?? '';
        $start_date = $_POST['start_date'] ?? date('Y-m-d');
        $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $cost = floatval($_POST['cost'] ?? 0);

        if ($request_id > 0 && $technician_id > 0) {
            // Get request details
            $requestQuery = "SELECT * FROM requests WHERE id = ?";
            $requestStmt = $conn->prepare($requestQuery);
            $requestStmt->bind_param("i", $request_id);
            $requestStmt->execute();
            $requestResult = $requestStmt->get_result();
            $request = $requestResult->fetch_assoc();
            $requestStmt->close();

            if ($request && $request['status'] === 'Approved') {
                // Parse form_data to get equipment info
                $form_data = json_decode($request['form_data'] ?? '{}', true);
                $equipment_type = extractEquipmentName($form_data);
                
                // Create maintenance record
                $checkTable = $conn->query("SHOW TABLES LIKE 'maintenance_records'");
                if ($checkTable && $checkTable->num_rows === 0) {
                    $createTable = "CREATE TABLE IF NOT EXISTS `maintenance_records` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `request_id` int(11) DEFAULT NULL,
                        `equipment_id` int(11) DEFAULT NULL,
                        `equipment_type` varchar(50) NOT NULL,
                        `technician_id` int(11) DEFAULT NULL,
                        `maintenance_type` enum('preventive','corrective','upgrade') NOT NULL,
                        `description` text DEFAULT NULL,
                        `cost` decimal(10,2) DEFAULT NULL,
                        `start_date` date DEFAULT NULL,
                        `end_date` date DEFAULT NULL,
                        `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
                        `support_level` varchar(10) DEFAULT NULL,
                        `processing_time` varchar(255) DEFAULT NULL,
                        `processing_deadline` datetime DEFAULT NULL,
                        `completed_within_sla` tinyint(1) DEFAULT NULL,
                        `support_level` varchar(10) DEFAULT NULL,
                        `processing_time` varchar(255) DEFAULT NULL,
                        `processing_deadline` datetime DEFAULT NULL,
                        `completed_within_sla` tinyint(1) DEFAULT NULL,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                    $conn->query($createTable);
                }

                // Ensure new columns exist
                $requiredColumns = [
                    'updated_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()",
                    'support_level' => "ALTER TABLE `maintenance_records` ADD COLUMN `support_level` varchar(10) DEFAULT NULL",
                    'processing_time' => "ALTER TABLE `maintenance_records` ADD COLUMN `processing_time` varchar(255) DEFAULT NULL",
                    'processing_deadline' => "ALTER TABLE `maintenance_records` ADD COLUMN `processing_deadline` datetime DEFAULT NULL",
                    'completed_within_sla' => "ALTER TABLE `maintenance_records` ADD COLUMN `completed_within_sla` tinyint(1) DEFAULT NULL",
                    'request_id' => "ALTER TABLE `maintenance_records` ADD COLUMN `request_id` int(11) DEFAULT NULL",
                    'started_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `started_at` datetime DEFAULT NULL",
                    'completed_at' => "ALTER TABLE `maintenance_records` ADD COLUMN `completed_at` datetime DEFAULT NULL"
                ];
                foreach ($requiredColumns as $column => $alterQuery) {
                    $columnCheck = $conn->query("SHOW COLUMNS FROM `maintenance_records` LIKE '{$column}'");
                    if ($columnCheck && $columnCheck->num_rows === 0) {
                        $conn->query($alterQuery);
                    }
                }

                // Insert maintenance record
                $insertQuery = "INSERT INTO maintenance_records (request_id, equipment_type, technician_id, maintenance_type, description, cost, start_date, end_date, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("isisssss", $request_id, $equipment_type, $technician_id, $maintenance_type, $description, $cost, $start_date, $end_date);

                    if ($insertStmt->execute()) {
                        $maintenanceId = $conn->insert_id;

                        // Keep newly assigned maintenance in pending state until technician starts it
                        $pendingStmt = $conn->prepare("
                            UPDATE maintenance_records 
                            SET status = 'scheduled', started_at = NULL, updated_at = NOW() 
                            WHERE id = ?
                        ");
                        if ($pendingStmt) {
                            $pendingStmt->bind_param("i", $maintenanceId);
                            $pendingStmt->execute();
                            $pendingStmt->close();
                        }

                    $message = 'Maintenance assigned to technician successfully!';

                    if (isset($request['form_type'])) {
                        if ($request['form_type'] === 'ICT Service Request Form') {
                            assignServiceRequestToTechnician($conn, $request, $technician_id);
                        } elseif (stripos($request['form_type'], 'System Request') !== false) {
                            assignSystemRequestToTechnician($conn, $request, $technician_id);
                        }
                    }
                } else {
                    $error = 'Failed to create maintenance record: ' . $insertStmt->error;
                }
                $insertStmt->close();
            } else {
                $error = 'Request not found or not approved.';
            }
        } else {
            $error = 'Please select a technician.';
        }
    }
}

// Check if maintenance records table exists
$maintenanceTableCheck = $conn->query("SHOW TABLES LIKE 'maintenance_records'");
$maintenanceTableExists = $maintenanceTableCheck && $maintenanceTableCheck->num_rows > 0;

// Fetch approved service and system requests (exclude those already assigned to maintenance)
$approvedRequestsQuery = "SELECT r.*, u.full_name, u.email 
                          FROM requests r 
                          LEFT JOIN users u ON r.user_id = u.id 
                          WHERE r.status = 'Approved' 
                          AND (r.form_type = 'ICT Service Request Form' OR r.form_type LIKE '%System Request%')";
if ($maintenanceTableExists) {
    $approvedRequestsQuery .= " 
                          AND NOT EXISTS (
                              SELECT 1 FROM maintenance_records mr 
                              WHERE mr.request_id = r.id
                          )";
}
$approvedRequestsQuery .= " ORDER BY r.created_at DESC";
$approvedRequests = $conn->query($approvedRequestsQuery);

// Fetch all technicians
$techniciansQuery = "SELECT id, full_name, email FROM users WHERE role = 'technician' ORDER BY full_name";
$techniciansResult = $conn->query($techniciansQuery);
$technicians = [];
if ($techniciansResult) {
    while ($tech = $techniciansResult->fetch_assoc()) {
        $technicians[] = $tech;
    }
}

// Fetch maintenance records
if ($maintenanceTableExists) {
    $maintenanceQuery = "SELECT mr.*, u.full_name as technician_name 
                        FROM maintenance_records mr 
                        LEFT JOIN users u ON mr.technician_id = u.id 
                        ORDER BY mr.created_at DESC";
    $maintenanceRecords = $conn->query($maintenanceQuery);
} else {
    $maintenanceRecords = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Assignment - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
         :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: var(--secondary-color); margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: #fff; }
        .main-content { padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-assign { background-color: #28a745; color: white; border: none; }
        .btn-assign:hover { background-color: #218838 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto">
                <a href="profile.php" class="btn btn-light me-2"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2><i class="fas fa-tools"></i> Maintenance Assignment</h2>
                
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Approved Requests Section -->
                <div class="card p-4 mt-3 mb-4">
                    <h4 class="mb-3"><i class="fas fa-check-circle text-success"></i> Approved Requests (Ready for Assignment)</h4>
                    <?php if ($approvedRequests && $approvedRequests->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="approvedRequestsTable" class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Request Type</th>
                                        <th>Requested By</th>
                                        <th>Date Approved</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $approvedRequests->fetch_assoc()): 
                                        $form_data = json_decode($row['form_data'] ?? '{}', true);
                                        $office = $form_data['office'] ?? 'N/A';
                                        $equipment = extractEquipmentName($form_data);
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['form_type']) ?></strong><br>
                                                <small class="text-muted">Office: <?= htmlspecialchars($office) ?></small><br>
                                                <small class="text-muted">Equipment: <?= htmlspecialchars($equipment) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                            <td><?= htmlspecialchars($row['updated_at']) ?></td>
                                            <td>
                                                <button class="btn btn-assign btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#assignModal"
                                                        data-request-id="<?= $row['id'] ?>"
                                                        data-request-type="<?= htmlspecialchars($row['form_type']) ?>"
                                                        data-equipment="<?= htmlspecialchars($equipment) ?>"
                                                        data-office="<?= htmlspecialchars($office) ?>">
                                                    <i class="fas fa-user-cog"></i> Assign to Technician
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No approved requests available for assignment.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Maintenance Records Section -->
                <div class="card p-4">
                    <h4 class="mb-3"><i class="fas fa-clipboard-list"></i> Maintenance Records</h4>
                    <?php if ($maintenanceRecords && $maintenanceRecords->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="maintenanceTable" class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Equipment Type</th>
                                        <th>Maintenance Type</th>
                                        <th>Technician</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $maintenanceRecords->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['equipment_type'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars(ucfirst($row['maintenance_type'])) ?></td>
                                            <td><?= htmlspecialchars($row['technician_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($row['status']) {
                                                    'completed' => 'bg-success text-white',
                                                    'in_progress' => 'bg-warning text-dark',
                                                    'cancelled' => 'bg-danger text-white',
                                                    default => 'bg-secondary text-white'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $row['status']))) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['start_date'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['end_date'] ?? 'N/A') ?></td>
                                            <td>₱<?= number_format($row['cost'] ?? 0, 2) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No maintenance records found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Maintenance Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-cog"></i> Assign Maintenance to Technician</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_maintenance">
                        <input type="hidden" name="request_id" id="modal_request_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Request Type</label>
                            <input type="text" class="form-control" id="modal_request_type" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Equipment</label>
                            <input type="text" class="form-control" id="modal_equipment" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Office/Department</label>
                            <input type="text" class="form-control" id="modal_office" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Assign to Technician <span class="text-danger">*</span></label>
                            <select name="technician_id" class="form-select" required>
                                <option value="">Select Technician</option>
                                <?php if (!empty($technicians)): 
                                    foreach ($technicians as $tech): ?>
                                        <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['full_name']) ?> (<?= htmlspecialchars($tech['email']) ?>)</option>
                                    <?php endforeach; 
                                endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                            <select name="maintenance_type" class="form-select" required>
                                <option value="corrective">Corrective</option>
                                <option value="preventive">Preventive</option>
                                <option value="upgrade">Upgrade</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter maintenance description..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estimated Cost</label>
                            <input type="number" name="cost" class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Assign Maintenance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#approvedRequestsTable').DataTable();
            $('#maintenanceTable').DataTable();
            
            // Handle modal data
            $('#assignModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var requestId = button.data('request-id');
                var requestType = button.data('request-type');
                var equipment = button.data('equipment');
                var office = button.data('office');
                
                var modal = $(this);
                modal.find('#modal_request_id').val(requestId);
                modal.find('#modal_request_type').val(requestType);
                modal.find('#modal_equipment').val(equipment);
                modal.find('#modal_office').val(office);
            });
        });
    </script>
</body>
</html>
