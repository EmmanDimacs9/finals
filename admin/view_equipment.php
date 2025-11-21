<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Get equipment ID from URL
$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$equipment_id) {
    header('Location: equipment.php');
    exit();
}

// Get equipment details
$stmt = $conn->prepare("
    SELECT e.*, 
           ec.name as category_name, 
           d.name as department_name, 
           u.full_name as assigned_user_name,
           u.email as assigned_user_email
    FROM equipment e
    LEFT JOIN equipment_categories ec ON e.category_id = ec.id
    LEFT JOIN departments d ON e.department_id = d.id
    LEFT JOIN users u ON e.assigned_to = u.id
    WHERE e.id = ?
");
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$equipment = $stmt->get_result()->fetch_assoc();

if (!$equipment) {
    header('Location: equipment.php');
    exit();
}

// Get maintenance history
$maintenance_stmt = $conn->prepare("
    SELECT mr.*, u.full_name as technician_name
    FROM maintenance_records mr
    LEFT JOIN users u ON mr.technician_id = u.id
    WHERE mr.equipment_id = ?
    ORDER BY mr.created_at DESC
");
$maintenance_stmt->bind_param("i", $equipment_id);
$maintenance_stmt->execute();
$maintenance_history = $maintenance_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Equipment - BSU Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: var(--secondary-color);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
        
        .equipment-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
        }
        
        .info-item {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .qr-container {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .maintenance-item {
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .maintenance-status {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-university"></i> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-eye"></i> View Equipment</h2>
                    <div>
                        <a href="edit_equipment.php?id=<?php echo $equipment_id; ?>" class="btn btn-primary me-2">
                            <i class="fas fa-edit"></i> Edit Equipment
                        </a>
                        <a href="equipment.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Equipment
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Equipment Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="equipment-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h3 class="mb-2"><?php echo htmlspecialchars($equipment['name']); ?></h3>
                                        <p class="mb-0"><?php echo htmlspecialchars($equipment['category_name']); ?></p>
                                    </div>
                                    <div>
                                        <?php
                                        $status_class = [
                                            'active' => 'success',
                                            'maintenance' => 'warning',
                                            'disposed' => 'danger',
                                            'lost' => 'secondary'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $status_class[$equipment['status']]; ?> status-badge">
                                            <?php echo ucfirst($equipment['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body p-0">
                                <div class="info-item">
                                    <div class="info-label">Serial Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($equipment['serial_number']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Model</div>
                                    <div class="info-value"><?php echo htmlspecialchars($equipment['model']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Brand</div>
                                    <div class="info-value"><?php echo htmlspecialchars($equipment['brand']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Department</div>
                                    <div class="info-value"><?php echo htmlspecialchars($equipment['department_name']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Location</div>
                                    <div class="info-value"><?php echo htmlspecialchars($equipment['location']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Assigned To</div>
                                    <div class="info-value">
                                        <?php if ($equipment['assigned_user_name']): ?>
                                            <?php echo htmlspecialchars($equipment['assigned_user_name']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($equipment['assigned_user_email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Acquisition Date</div>
                                    <div class="info-value">
                                        <?php echo $equipment['acquisition_date'] ? date('M d, Y', strtotime($equipment['acquisition_date'])) : 'Not specified'; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Acquisition Cost</div>
                                    <div class="info-value">
                                        <?php echo $equipment['acquisition_cost'] ? '₱' . number_format($equipment['acquisition_cost'], 2) : 'Not specified'; ?>
                                    </div>
                                </div>
                                
                                <?php if ($equipment['notes']): ?>
                                    <div class="info-item">
                                        <div class="info-label">Notes</div>
                                        <div class="info-value"><?php echo nl2br(htmlspecialchars($equipment['notes'])); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <div class="info-label">Created</div>
                                    <div class="info-value"><?php echo date('M d, Y H:i:s', strtotime($equipment['created_at'])); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value"><?php echo date('M d, Y H:i:s', strtotime($equipment['updated_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR Code and Actions -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-qrcode"></i> QR Code</h5>
                                <div class="qr-container">
                                    <?php if ($equipment['qr_code']): ?>
                                        <img src="<?php echo htmlspecialchars($equipment['qr_code']); ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                                        <br>
                                        <button class="btn btn-sm btn-outline-primary" onclick="downloadQRCode()">
                                            <i class="fas fa-download"></i> Download QR Code
                                        </button>
                                    <?php else: ?>
                                        <p class="text-muted">QR Code not available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-tools"></i> Quick Actions</h5>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-secondary" onclick="printEquipment()">
                                        <i class="fas fa-print"></i> Print Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance History -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-history"></i> Maintenance History</h5>
                        
                        <?php if ($maintenance_history->num_rows > 0): ?>
                            <div class="maintenance-list">
                                <?php while ($maintenance = $maintenance_history->fetch_assoc()): ?>
                                    <div class="maintenance-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($maintenance['maintenance_type']); ?> Maintenance</h6>
                                                <p class="mb-1"><?php echo htmlspecialchars($maintenance['description']); ?></p>
                                                <small class="text-muted">
                                                    Technician: <?php echo htmlspecialchars($maintenance['technician_name']); ?> |
                                                    Cost: ₱<?php echo number_format($maintenance['cost'], 2); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <?php
                                                $status_class = [
                                                    'scheduled' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$maintenance['status']]; ?> maintenance-status">
                                                    <?php echo ucfirst(str_replace('_', ' ', $maintenance['status'])); ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($maintenance['start_date'])); ?> - 
                                                    <?php echo date('M d, Y', strtotime($maintenance['end_date'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-tools fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No maintenance history found for this equipment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadQRCode() {
            const qrImage = document.querySelector('.qr-container img');
            if (qrImage) {
                const link = document.createElement('a');
                link.download = 'qr_code_<?php echo $equipment_id; ?>.png';
                link.href = qrImage.src;
                link.click();
            }
        }
        
        function printEquipment() {
            window.print();
        }
    </script>
</body>
</html> 