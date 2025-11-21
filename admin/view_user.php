<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Get user details
$stmt = $conn->prepare("
    SELECT u.*, 
           COUNT(DISTINCT mr.id) as maintenance_count,
           COUNT(DISTINCT t.id) as task_count
    FROM users u
    LEFT JOIN maintenance_records mr ON u.id = mr.technician_id
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit();
}

// Get user's assigned equipment (using assigned_person field)
$user_name = $user['full_name'];
$equipment_stmt = $conn->prepare("
    SELECT e.*, e.type as category_name
    FROM equipment e
    WHERE e.assigned_person = ?
    ORDER BY e.date_acquired DESC
");
$equipment_stmt->bind_param("s", $user_name);
$equipment_stmt->execute();
$assigned_equipment = $equipment_stmt->get_result();

// Count assigned equipment for statistics
$equipment_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM equipment WHERE assigned_person = ?");
$equipment_count_stmt->bind_param("s", $user_name);
$equipment_count_stmt->execute();
$equipment_count_result = $equipment_count_stmt->get_result()->fetch_assoc();
$user['equipment_count'] = $equipment_count_result['count'];

// Get user's maintenance records
$maintenance_stmt = $conn->prepare("
    SELECT mr.*, e.property_equipment as equipment_name, e.asset_tag
    FROM maintenance_records mr
    LEFT JOIN equipment e ON mr.equipment_id = e.id
    WHERE mr.technician_id = ?
    ORDER BY mr.created_at DESC
    LIMIT 10
");
$maintenance_stmt->bind_param("i", $user_id);
$maintenance_stmt->execute();
$maintenance_records = $maintenance_stmt->get_result();

// Get user's assigned tasks
$tasks_stmt = $conn->prepare("
    SELECT t.*, u.full_name as assigned_by_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_by = u.id
    WHERE t.assigned_to = ?
    ORDER BY t.created_at DESC
    LIMIT 10
");
$tasks_stmt->bind_param("i", $user_id);
$tasks_stmt->execute();
$assigned_tasks = $tasks_stmt->get_result();

// Get recent activity from admin_logs table
$recent_activity_stmt = $conn->prepare("
    SELECT action, description, created_at
    FROM admin_logs
    WHERE admin_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$recent_activity_stmt->bind_param("i", $user_id);
$recent_activity_stmt->execute();
$recent_activity = $recent_activity_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - BSU Inventory Management System</title>
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
        
        .user-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        
        .role-badge {
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
        
        .stats-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .activity-item {
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .activity-time {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .equipment-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .equipment-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .status-badge {
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
                    <h2><i class="fas fa-user"></i> View User Details</h2>
                    <div>
                        <?php if (isAdmin()): ?>
                            <a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-primary me-2">
                                <i class="fas fa-edit"></i> Edit User
                            </a>
                        <?php endif; ?>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- User Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="user-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h3 class="mb-2"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                                        <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                    <div>
                                        <?php
                                        $role_class = $user['role'] === 'admin' ? 'danger' : 'info';
                                        ?>
                                        <span class="badge bg-<?php echo $role_class; ?> role-badge">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body p-0">
                                <div class="info-item">
                                    <div class="info-label">Email Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($user['phone_number']); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Role</div>
                                    <div class="info-value">
                                        <span class="badge bg-<?php echo $role_class; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Account Created</div>
                                    <div class="info-value"><?php echo date('M d, Y H:i:s', strtotime($user['created_at'])); ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value"><?php echo date('M d, Y H:i:s', strtotime($user['updated_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-chart-pie"></i> User Statistics</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stats-card bg-light">
                                            <div class="stats-number"><?php echo $user['equipment_count']; ?></div>
                                            <div class="stats-label">Equipment Assigned</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stats-card bg-light">
                                            <div class="stats-number"><?php echo $user['maintenance_count']; ?></div>
                                            <div class="stats-label">Maintenance Records</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stats-card bg-light">
                                            <div class="stats-number"><?php echo $user['task_count']; ?></div>
                                            <div class="stats-label">Tasks Assigned</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stats-card bg-light">
                                            <div class="stats-number">
                                                <?php 
                                                $days = floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24));
                                                echo $days;
                                                ?>
                                            </div>
                                            <div class="stats-label">Days Active</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-cog"></i> Quick Actions</h5>
                                <div class="d-grid gap-2">
                                    <?php if (isAdmin()): ?>
                                        <a href="edit_user.php?id=<?php echo $user_id; ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit User
                                        </a>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-secondary" onclick="printUserDetails()">
                                        <i class="fas fa-print"></i> Print Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Assigned Equipment -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-laptop"></i> Assigned Equipment</h5>
                        
                        <?php if ($assigned_equipment->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($equipment = $assigned_equipment->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="equipment-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($equipment['property_equipment']); ?></h6>
                                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($equipment['category_name'] ?? $equipment['type']); ?></p>
                                                    <small class="text-muted">
                                                        Asset Tag: <?php echo htmlspecialchars($equipment['asset_tag']); ?><br>
                                                        Location: <?php echo htmlspecialchars($equipment['location']); ?>
                                                    </small>
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
                                            <div class="mt-2">
                                                <a href="view_equipment.php?id=<?php echo $equipment['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-laptop fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No equipment assigned to this user.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-history"></i> Recent Activity</h5>
                        
                        <?php if ($recent_activity->num_rows > 0): ?>
                            <div class="activity-list">
                                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="fw-bold"><?php echo htmlspecialchars($activity['action']); ?></span>
                                                <?php if ($activity['description']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($activity['description']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-time">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?>
                                            </div>
                                        </div>
                                        
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-3"></i>
                                <p class="text-muted">No recent activity found for this user.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printUserDetails() {
            window.print();
        }
    </script>
</body>
</html> 