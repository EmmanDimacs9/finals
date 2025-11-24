<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

// Search and filter setup
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$user_filter = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : '';
$location_filter = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

// Pagination setup - each tab has its own page
$items_per_page = 10;
$desktop_page = isset($_GET['desktop_page']) ? max(1, (int)$_GET['desktop_page']) : 1;
$laptops_page = isset($_GET['laptops_page']) ? max(1, (int)$_GET['laptops_page']) : 1;
$printers_page = isset($_GET['printers_page']) ? max(1, (int)$_GET['printers_page']) : 1;
$accesspoint_page = isset($_GET['accesspoint_page']) ? max(1, (int)$_GET['accesspoint_page']) : 1;
$switch_page = isset($_GET['switch_page']) ? max(1, (int)$_GET['switch_page']) : 1;
$telephone_page = isset($_GET['telephone_page']) ? max(1, (int)$_GET['telephone_page']) : 1;

// Fetch distinct users and locations from all equipment tables
$all_users = [];
$all_locations = [];

// Get users and locations from desktop
$result = $conn->query("SELECT DISTINCT assigned_person FROM desktop WHERE assigned_person IS NOT NULL AND assigned_person != ''");
while ($row = $result->fetch_assoc()) {
    if (!in_array($row['assigned_person'], $all_users)) {
        $all_users[] = $row['assigned_person'];
    }
}
$result = $conn->query("SELECT DISTINCT location FROM desktop WHERE location IS NOT NULL AND location != ''");
while ($row = $result->fetch_assoc()) {
    if (!in_array($row['location'], $all_locations)) {
        $all_locations[] = $row['location'];
    }
}

// Get from other tables (laptops, printers, etc.)
$tables = ['laptops', 'printers', 'accesspoint', 'switch', 'telephone'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT DISTINCT assigned_person FROM $table WHERE assigned_person IS NOT NULL AND assigned_person != ''");
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['assigned_person'], $all_users)) {
            $all_users[] = $row['assigned_person'];
        }
    }
    $result = $conn->query("SELECT DISTINCT location FROM $table WHERE location IS NOT NULL AND location != ''");
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['location'], $all_locations)) {
            $all_locations[] = $row['location'];
        }
    }
}

sort($all_users);
sort($all_locations);

// Helper to build WHERE clause and parameters for prepared statements
function buildWhereClause($search, $user_filter, $location_filter) {
    $clauses = [];
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $clauses[] = "(asset_tag LIKE ? OR assigned_person LIKE ? OR location LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'sss';
    }
    if (!empty($user_filter)) {
        $clauses[] = "assigned_person = ?";
        $params[] = $user_filter;
        $types .= 's';
    }
    if (!empty($location_filter)) {
        $clauses[] = "location = ?";
        $params[] = $location_filter;
        $types .= 's';
    }
    
    $where_clause = (count($clauses) > 0) ? ' WHERE ' . implode(' AND ', $clauses) : '';
    
    return [
        'where' => $where_clause,
        'params' => $params,
        'types' => $types
    ];
}

// Helper function to execute COUNT query with prepared statement
function executeCountQuery($conn, $table, $where_data) {
    $query = "SELECT COUNT(*) as total FROM `$table`" . $where_data['where'];
    
    if (empty($where_data['params'])) {
        $result = $conn->query($query);
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param($where_data['types'], ...$where_data['params']);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
    
    return $count;
}

// Helper function to execute SELECT query with prepared statement
function executeSelectQuery($conn, $table, $where_data, $order_by, $limit, $offset) {
    $query = "SELECT * FROM `$table`" . $where_data['where'] . " $order_by LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return null;
    }
    
    if (empty($where_data['params'])) {
        $stmt->bind_param('ii', $limit, $offset);
    } else {
        $types = $where_data['types'] . 'ii';
        $params = array_merge($where_data['params'], [$limit, $offset]);
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

// Helper function to build pagination URL
function buildPaginationUrl($page, $page_param, $search, $user_filter, $location_filter) {
    $params = [];
    if (!empty($search)) $params['search'] = $search;
    if (!empty($user_filter)) $params['user'] = $user_filter;
    if (!empty($location_filter)) $params['location'] = $location_filter;
    $params[$page_param] = $page;
    return '?' . http_build_query($params);
}

// Build WHERE clause data for all tables (using prepared statements)
$where_data = buildWhereClause($search, $user_filter, $location_filter);

// Get total counts for each table using prepared statements
$desktop_total = executeCountQuery($conn, 'desktop', $where_data);
$desktop_total_pages = max(1, ceil($desktop_total / $items_per_page));

$laptops_total = executeCountQuery($conn, 'laptops', $where_data);
$laptops_total_pages = max(1, ceil($laptops_total / $items_per_page));

$printers_total = executeCountQuery($conn, 'printers', $where_data);
$printers_total_pages = max(1, ceil($printers_total / $items_per_page));

$accesspoint_total = executeCountQuery($conn, 'accesspoint', $where_data);
$accesspoint_total_pages = max(1, ceil($accesspoint_total / $items_per_page));

$switch_total = executeCountQuery($conn, 'switch', $where_data);
$switch_total_pages = max(1, ceil($switch_total / $items_per_page));

$telephone_total = executeCountQuery($conn, 'telephone', $where_data);
$telephone_total_pages = max(1, ceil($telephone_total / $items_per_page));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
         :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: var(--secondary-color); margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: #fff; }
        .main-content { padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        
        /* Red Navigation Tabs Styling */
        .nav-tabs .nav-link {
            color: #dc3545;
            border: 1px solid transparent;
            border-radius: 8px 8px 0 0;
            padding: 12px 20px;
            margin-right: 5px;
            background-color: transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
            border-bottom-color: #fff;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dc3545;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        /* Clickable row styling */
        .clickable-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .clickable-row:hover {
            background-color: #e3f2fd !important;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .clickable-row:active {
            transform: scale(0.99);
            background-color: #bbdefb !important;
        }

        .table-hover tbody tr:hover:not(.clickable-row) {
            background-color: #f1f3f5;
        }

        /* QR Code Styles with Asset Tag */
        .qr-code-container {
            display: inline-block;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 100%;
        }

        .qr-code-image {
            display: block;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border: 4px solid #f0f0f0;
            border-radius: 10px;
            padding: 12px;
        }

        .qr-code-tag-block {
            display: block;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Arial', 'Helvetica', sans-serif;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 15px;
            padding: 10px 15px;
            word-break: break-all;
            line-height: 1.4;
        }

        /* Pagination Styles */
        .pagination-nav {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-top: 1rem;
        }

        .pagination-wrapper {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d32f2f;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }

        .pagination-btn {
            display: inline-block;
            padding: 10px 18px;
            text-decoration: none;
            color: #d32f2f;
            background-color: #fff;
            border: none;
            border-right: 1px solid #f3b5b5;
            transition: all 0.2s ease;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
        }

        .pagination-btn:last-child {
            border-right: none;
            border-radius: 0 6px 6px 0;
        }

        .pagination-btn:first-child {
            border-radius: 6px 0 0 6px;
        }

        .pagination-btn.pagination-number {
            min-width: 42px;
            text-align: center;
        }

        .pagination-btn.pagination-number.active {
            background-color: #d32f2f;
            color: #fff !important;
            font-weight: 700;
        }

        .pagination-btn:hover:not(.disabled):not(.active) {
            background-color: #ffe7e7;
            color: #b71c1c;
        }

        .pagination-btn.disabled {
            color: #9e9e9e;
            cursor: not-allowed;
            pointer-events: none;
            opacity: 0.6;
            text-decoration: none;
        }
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
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-boxes"></i> Equipment Inventory</h2>
                    <div>
                        <button class="btn btn-secondary" onclick="printAllLabels()"><i class="fas fa-print"></i> Print All Labels</button>
                    </div>
                </div>

                <!-- Search -->
                <form method="GET" class="mb-3 d-flex flex-wrap gap-2">
                    <input type="text" name="search" class="form-control" style="flex: 1; min-width: 200px;" 
                           placeholder="Search asset tag, user or location..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="user" class="form-select" style="max-width:200px;">
                        <option value="">All users</option>
                        <?php foreach ($all_users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($user_filter==$user) ? 'selected':''; ?>>
                                <?php echo htmlspecialchars($user); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="location" class="form-select" style="max-width:200px;">
                        <option value="">All locations</option>
                        <?php foreach ($all_locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>" <?php echo ($location_filter==$location) ? 'selected':''; ?>>
                                <?php echo htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="inventory.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
                </form>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desktops" type="button">
                            <i class="fas fa-desktop"></i> Desktops
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#laptops" type="button">
                            <i class="fas fa-laptop"></i> Laptops
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#printers" type="button">
                            <i class="fas fa-print"></i> Printers
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#accesspoints" type="button">
                            <i class="fas fa-wifi"></i> Access Points
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#switches" type="button">
                            <i class="fas fa-network-wired"></i> Switches
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#telephones" type="button">
                            <i class="fas fa-phone"></i> Telephones
                        </button>
                    </li>
                </ul>

                <div class="tab-content border bg-white p-3 rounded-bottom shadow-sm">
                    <!-- Desktops -->
                    <div class="tab-pane fade show active" id="desktops">
                        <h5><i class="fas fa-desktop"></i> Desktop Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Processor</th>
                                        <th>RAM</th>
                                        <th>OS</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $desktop_offset = ($desktop_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'desktop', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $desktop_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('desktop', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="desktop"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-processor="<?php echo htmlspecialchars($row['processor'] ?? ''); ?>"
                                            data-ram="<?php echo htmlspecialchars($row['ram'] ?? ''); ?>"
                                            data-gpu="<?php echo htmlspecialchars($row['gpu'] ?? ''); ?>"
                                            data-hdd="<?php echo htmlspecialchars($row['hard_drive'] ?? ''); ?>"
                                            data-os="<?php echo htmlspecialchars($row['operating_system'] ?? ''); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['processor'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['ram'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['operating_system'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No desktop equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Desktop Pagination -->
                        <?php if ($desktop_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Desktop pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($desktop_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($desktop_page - 1, 'desktop_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $desktop_page - 2);
                                    $end_page = min($desktop_total_pages, $desktop_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'desktop_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $desktop_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($desktop_page < $desktop_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($desktop_page + 1, 'desktop_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Laptops -->
                    <div class="tab-pane fade" id="laptops">
                        <h5><i class="fas fa-laptop"></i> Laptop Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Hardware Specs</th>
                                        <th>Software Specs</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $laptops_offset = ($laptops_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'laptops', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $laptops_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('laptop', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="generic"
                                            data-equipment="Laptop"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-specs="<?php echo 'HW: '.htmlspecialchars($row['hardware_specifications']).' | SW: '.htmlspecialchars($row['software_specifications']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hardware_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['software_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No laptop equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Laptops Pagination -->
                        <?php if ($laptops_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Laptops pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($laptops_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($laptops_page - 1, 'laptops_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $laptops_page - 2);
                                    $end_page = min($laptops_total_pages, $laptops_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'laptops_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $laptops_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($laptops_page < $laptops_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($laptops_page + 1, 'laptops_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Printers -->
                    <div class="tab-pane fade" id="printers">
                        <h5><i class="fas fa-print"></i> Printer Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Hardware Specs</th>
                                        <th>Remarks</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $printers_offset = ($printers_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'printers', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $printers_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('printer', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="generic"
                                            data-equipment="Printer"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hardware_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No printer equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Printers Pagination -->
                        <?php if ($printers_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Printers pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($printers_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($printers_page - 1, 'printers_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $printers_page - 2);
                                    $end_page = min($printers_total_pages, $printers_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'printers_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $printers_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($printers_page < $printers_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($printers_page + 1, 'printers_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Access Points -->
                    <div class="tab-pane fade" id="accesspoints">
                        <h5><i class="fas fa-wifi"></i> Access Point Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Hardware Specs</th>
                                        <th>Remarks</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $accesspoint_offset = ($accesspoint_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'accesspoint', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $accesspoint_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('accesspoint', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="generic"
                                            data-equipment="Access Point"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hardware_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No access point equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Access Point Pagination -->
                        <?php if ($accesspoint_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Access Point pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($accesspoint_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($accesspoint_page - 1, 'accesspoint_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $accesspoint_page - 2);
                                    $end_page = min($accesspoint_total_pages, $accesspoint_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'accesspoint_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $accesspoint_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($accesspoint_page < $accesspoint_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($accesspoint_page + 1, 'accesspoint_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Switches -->
                    <div class="tab-pane fade" id="switches">
                        <h5><i class="fas fa-network-wired"></i> Switch Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Hardware Specs</th>
                                        <th>Remarks</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $switch_offset = ($switch_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'switch', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $switch_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('switch', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="generic"
                                            data-equipment="Switch"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hardware_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No switch equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Switch Pagination -->
                        <?php if ($switch_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Switch pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($switch_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($switch_page - 1, 'switch_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $switch_page - 2);
                                    $end_page = min($switch_total_pages, $switch_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'switch_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $switch_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($switch_page < $switch_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($switch_page + 1, 'switch_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Telephones -->
                    <div class="tab-pane fade" id="telephones">
                        <h5><i class="fas fa-phone"></i> Telephone Inventory</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Tag</th>
                                        <th>Assigned Person</th>
                                        <th>Location</th>
                                        <th>Hardware Specs</th>
                                        <th>Remarks</th>
                                        <th>Date Acquired</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $telephone_offset = ($telephone_page - 1) * $items_per_page;
                                    $res = executeSelectQuery($conn, 'telephone', $where_data, 'ORDER BY date_acquired DESC', $items_per_page, $telephone_offset);
                                    if ($res && $res->num_rows > 0):
                                        while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr class="clickable-row" 
                                            onclick="viewEquipment('telephone', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')"
                                            data-type="generic"
                                            data-equipment="Telephone"
                                            data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                                            data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                                            data-location="<?php echo htmlspecialchars($row['location']); ?>"
                                            data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                                            data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                                            data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                                            data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                            <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                                            <td><?php echo htmlspecialchars($row['hardware_specifications'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['remarks'] ?? 'N/A'); ?></td>
                                            <td><?php echo $row['date_acquired'] ? date('M d, Y', strtotime($row['date_acquired'])) : 'N/A'; ?></td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                No telephone equipment found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Telephone Pagination -->
                        <?php if ($telephone_total_pages > 1): ?>
                            <nav class="pagination-nav mt-3" aria-label="Telephone pagination">
                                <div class="pagination-wrapper">
                                    <?php if ($telephone_page > 1): ?>
                                        <a href="<?php echo buildPaginationUrl($telephone_page - 1, 'telephone_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-prev">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-prev disabled">Previous</span>
                                    <?php endif; ?>
                                    <?php 
                                    $start_page = max(1, $telephone_page - 2);
                                    $end_page = min($telephone_total_pages, $telephone_page + 2);
                                    for ($i = $start_page; $i <= $end_page; $i++): 
                                    ?>
                                        <a href="<?php echo buildPaginationUrl($i, 'telephone_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-number <?php echo $i == $telephone_page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    <?php if ($telephone_page < $telephone_total_pages): ?>
                                        <a href="<?php echo buildPaginationUrl($telephone_page + 1, 'telephone_page', $search, $user_filter, $location_filter); ?>" 
                                           class="pagination-btn pagination-next">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span class="pagination-btn pagination-next disabled">Next</span>
                                    <?php endif; ?>
                                </div>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Equipment Details Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white" style="background-color: #dc3545 !important;">
                <h5 class="modal-title"><i class="fas fa-box"></i> Equipment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="equipmentModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewEquipment(type, assetTag) {
    const modal = new bootstrap.Modal(document.getElementById('equipmentModal'));
    const modalBody = document.getElementById('equipmentModalBody');
    
    // Show loading
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    // Fetch equipment details
    fetch(`get_equipment_details.php?type=${encodeURIComponent(type)}&asset_tag=${encodeURIComponent(assetTag)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const equipment = data.equipment;
                const assetTag = escapeHtml(equipment.asset_tag || 'N/A');
                
                let html = '<div class="row">';
                
                // QR Code Section with Asset Tag
                html += '<div class="col-md-12 mb-4 text-center">';
                html += '<div class="qr-code-container">';
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(equipment.asset_tag || '')}`;
                html += `<img src="${qrUrl}" alt="QR Code" class="qr-code-image mb-3" id="qrCodeImage">`;
                html += `<div class="qr-code-tag-block">${assetTag}</div>`;
                const assetTagRaw = equipment.asset_tag || '';
                html += '<button class="btn btn-danger btn-sm mt-3" onclick="downloadQRCode(\'' + assetTagRaw.replace(/'/g, "\\'") + '\')">';
                html += '<i class="fas fa-download"></i> Download QR Code';
                html += '</button>';
                html += '</div>';
                html += '</div>';
                
                // Equipment Information Section
                html += '<div class="col-md-12">';
                html += '<h6 class="mb-3"><i class="fas fa-info-circle"></i> Equipment Information</h6>';
                html += '<table class="table table-bordered">';
                
                // Common fields
                html += `<tr><th style="width: 30%;">Asset Tag</th><td>${assetTag}</td></tr>`;
                html += `<tr><th>Property/Equipment</th><td>${escapeHtml(equipment.property_equipment || 'N/A')}</td></tr>`;
                html += `<tr><th>Assigned Person</th><td>${escapeHtml(equipment.assigned_person || 'N/A')}</td></tr>`;
                html += `<tr><th>Location</th><td>${escapeHtml(equipment.location || 'N/A')}</td></tr>`;
                html += `<tr><th>Date Acquired</th><td>${equipment.date_acquired || 'N/A'}</td></tr>`;
                html += `<tr><th>Inventory Item No</th><td>${escapeHtml(equipment.inventory_item_no || 'N/A')}</td></tr>`;
                
                // Type-specific fields
                if (type === 'desktop') {
                    html += `<tr><th>Processor</th><td>${escapeHtml(equipment.processor || 'N/A')}</td></tr>`;
                    html += `<tr><th>RAM</th><td>${escapeHtml(equipment.ram || 'N/A')}</td></tr>`;
                    html += `<tr><th>GPU</th><td>${escapeHtml(equipment.gpu || 'N/A')}</td></tr>`;
                    html += `<tr><th>Hard Drive</th><td>${escapeHtml(equipment.hard_drive || 'N/A')}</td></tr>`;
                    html += `<tr><th>Operating System</th><td>${escapeHtml(equipment.operating_system || 'N/A')}</td></tr>`;
                } else {
                    html += `<tr><th>Department</th><td>${escapeHtml(equipment.department || 'N/A')}</td></tr>`;
                    html += `<tr><th>Hardware Specifications</th><td>${escapeHtml(equipment.hardware_specifications || 'N/A')}</td></tr>`;
                    html += `<tr><th>Software Specifications</th><td>${escapeHtml(equipment.software_specifications || 'N/A')}</td></tr>`;
                    html += `<tr><th>Useful Life</th><td>${escapeHtml(equipment.useful_life || 'N/A')}</td></tr>`;
                    html += `<tr><th>High Value ICS No</th><td>${escapeHtml(equipment.high_value_ics_no || 'N/A')}</td></tr>`;
                }
                
                html += `<tr><th>Remarks</th><td>${escapeHtml(equipment.remarks || 'N/A')}</td></tr>`;
                html += '</table>';
                html += '</div>';
                html += '</div>';
                
                modalBody.innerHTML = html;
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">${escapeHtml(data.message || 'Failed to load equipment details')}</div>`;
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="alert alert-danger">Error loading equipment details: ${escapeHtml(error.message)}</div>`;
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function downloadQRCode(assetTag) {
    if (!assetTag) {
        alert('Asset tag is missing');
        return;
    }
    
    // Get the QR code image element
    const qrImage = document.getElementById('qrCodeImage');
    if (!qrImage) {
        alert('QR code image not found');
        return;
    }
    
    // Create a canvas to draw the QR code and text
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Set canvas size (larger for better quality)
    canvas.width = 400;
    canvas.height = 500;
    
    // Draw white background
    ctx.fillStyle = '#FFFFFF';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Load the QR code image
    const img = new Image();
    img.crossOrigin = 'anonymous';
    
    img.onload = function() {
        // Draw QR code in the center
        const qrSize = 280;
        const qrX = (canvas.width - qrSize) / 2;
        const qrY = 40;
        ctx.drawImage(img, qrX, qrY, qrSize, qrSize);
        
        // Draw asset tag text below QR code in black
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 24px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        
        ctx.fillText(assetTag, canvas.width / 2, qrY + qrSize + 30);
        
        // Convert canvas to blob and download
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `QRCode_${assetTag}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });
    };
    
    img.onerror = function() {
        // Fallback: download QR code directly
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(assetTag)}`;
        const link = document.createElement('a');
        link.href = qrUrl;
        link.download = `QRCode_${assetTag}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    
    // Set the image source
    img.src = qrImage.src;
}

// Print All Labels
function printAllLabels() {
  // Get all visible equipment rows from the active tab
  const activeTab = document.querySelector('.tab-pane.active');
  const visibleRows = activeTab.querySelectorAll('.clickable-row');
  
  if (visibleRows.length === 0) {
    alert('No equipment found to print.');
    return;
  }
  
  if (!confirm(`Print ${visibleRows.length} label(s)?`)) {
    return;
  }
  
  // Create HTML for all labels
  let allLabelsHTML = '';
  
  visibleRows.forEach((row, index) => {
    const assetTag = row.dataset.asset || '';
    const assignedPerson = row.dataset.user || '';
    const location = row.dataset.location || '';
    const processor = row.dataset.processor || '';
    const ram = row.dataset.ram || '';
    const gpu = row.dataset.gpu || '';
    const hardDrive = row.dataset.hdd || '';
    const os = row.dataset.os || '';
    const equipmentType = row.dataset.type === 'desktop' ? 'Desktop' : (row.dataset.equipment || 'Equipment');
    const specifications = row.dataset.specs || '';
    const dateAcquired = row.dataset.date || '';
    const inventoryItemNo = row.dataset.itemno || '';
    const remarks = row.dataset.remarks || '';
    
    // Generate QR code URL using an online service
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(assetTag)}`;
    
    // Create the label HTML
    const labelHTML = `
      <div class="asset-label" style="
          width: 100%;
          max-width: 700px;
          margin: 0 auto;
          border: 4px solid #dc3545;
          background: white;
          padding: 25px;
          box-sizing: border-box;
          position: relative;
        ">
          <div style="
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            position: relative;
          ">
            <div style="
              width: 70px;
              height: 70px;
              margin-right: 20px;
              flex-shrink: 0;
            ">
              <img src="../assets/logo/bsutneu.png" alt="BSU Logo" style="
                width: 100%;
                height: 100%;
                object-fit: contain;
              " onerror="this.style.display='none';">
            </div>
            <div style="
              font-size: 20px;
              font-weight: bold;
              text-transform: uppercase;
              color: #000;
              letter-spacing: 1px;
              flex: 1;
              margin-right: 140px;
            ">BATANGAS STATE UNIVERSITY</div>
            <div style="
              position: absolute;
              top: 0;
              right: 0;
              width: 100px;
              height: 100px;
              z-index: 1;
            ">
              <img src="${qrCodeUrl}" alt="QR Code" style="
                width: 100px;
                height: 100px;
                object-fit: contain;
              " onerror="this.style.display='none';">
            </div>
            <div style="
              position: absolute;
              top: 105px;
              right: 0;
              text-align: center;
              width: 100px;
              font-size: 10px;
              font-weight: bold;
              color: #000;
              line-height: 1;
            ">${assetTag}</div>
          </div>
          
          <div style="margin-top: 50px;">
            <div style="display: flex; flex-direction: column; gap: 18px;">
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 90px;">Property No:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;"></div>
              </div>
              
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 55px;">Article:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;">${equipmentType}</div>
              </div>
              
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 90px;">Specification:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;">${processor || specifications || ''}</div>
              </div>
              
              <div style="border-top: 1px solid #000; padding-top: 18px; margin-top: 10px; display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 65px;">Date Acq:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px;">${dateAcquired}</div>
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 35px;">Amt:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px;"></div>
              </div>
              
              <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
                <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 65px;">End User:</span>
                <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;">${assignedPerson}</div>
              </div>
            </div>
            
            <div style="text-align: center; margin-top: 35px; padding-top: 15px;">
              <div style="width: 180px; border-bottom: 2px solid #000; margin: 0 auto 8px; height: 18px;"></div>
              <div style="font-size: 11px; font-weight: bold; color: #000;">Supply Officer</div>
            </div>
          </div>
        </div>
    `;
    
    // Wrap each label for proper page breaking
    allLabelsHTML += '<div style="page-break-after: always;">' + labelHTML + '</div>';
  });
  
  // Create complete HTML document
  const printHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print All BSU Asset Labels</title>
      <style>
        body { 
          font-family: Arial, sans-serif; 
          margin: 0; 
          padding: 0; 
          background: white;
        }
        @media print {
          body { margin: 0; padding: 0; }
          .asset-label { 
            page-break-after: always; 
            margin-bottom: 0 !important;
          }
          .asset-label:last-child {
            page-break-after: auto;
          }
        }
      </style>
    </head>
    <body>
      ${allLabelsHTML}
    </body>
    </html>
  `;
  
  // Open print window
  const printWindow = window.open('', '_blank');
  printWindow.document.write(printHTML);
  printWindow.document.close();
  
  // Wait a bit for content to render, then print
  setTimeout(() => {
    printWindow.print();
  }, 500);
}
</script>

</body>
</html>

