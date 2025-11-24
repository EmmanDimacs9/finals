<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

// Inventory has been moved to admin section
// Redirect to technician dashboard with message
$_SESSION['info_message'] = 'Inventory management has been moved to the Admin section.';
header('Location: kanban.php');
exit();

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

// Helper to return WHERE clause for search + user and location filters
function buildWhere($search, $user_filter, $location_filter) {
    $clauses = [];
    if (!empty($search)) {
        $s = $search;
        $clauses[] = "(asset_tag LIKE '%$s%' OR assigned_person LIKE '%$s%' OR location LIKE '%$s%')";
    }
    if (!empty($user_filter)) {
        $clauses[] = "assigned_person = '$user_filter'";
    }
    if (!empty($location_filter)) {
        $clauses[] = "location = '$location_filter'";
    }
    return (count($clauses) > 0) ? ' WHERE ' . implode(' AND ', $clauses) : '';
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

// We'll fetch each table's rows separately (for the tabs)
$desktop_where = buildWhere($search, $user_filter, $location_filter);
$laptops_where = buildWhere($search, $user_filter, $location_filter);
$printers_where = buildWhere($search, $user_filter, $location_filter);
$accesspoint_where = buildWhere($search, $user_filter, $location_filter);
$switch_where = buildWhere($search, $user_filter, $location_filter);
$telephone_where = buildWhere($search, $user_filter, $location_filter);

// Get total counts for each table
$desktop_count_q = "SELECT COUNT(*) as total FROM desktop" . $desktop_where;
$desktop_total = $conn->query($desktop_count_q)->fetch_assoc()['total'] ?? 0;
$desktop_total_pages = max(1, ceil($desktop_total / $items_per_page));

$laptops_count_q = "SELECT COUNT(*) as total FROM laptops" . $laptops_where;
$laptops_total = $conn->query($laptops_count_q)->fetch_assoc()['total'] ?? 0;
$laptops_total_pages = max(1, ceil($laptops_total / $items_per_page));

$printers_count_q = "SELECT COUNT(*) as total FROM printers" . $printers_where;
$printers_total = $conn->query($printers_count_q)->fetch_assoc()['total'] ?? 0;
$printers_total_pages = max(1, ceil($printers_total / $items_per_page));

$accesspoint_count_q = "SELECT COUNT(*) as total FROM accesspoint" . $accesspoint_where;
$accesspoint_total = $conn->query($accesspoint_count_q)->fetch_assoc()['total'] ?? 0;
$accesspoint_total_pages = max(1, ceil($accesspoint_total / $items_per_page));

$switch_count_q = "SELECT COUNT(*) as total FROM `switch`" . $switch_where;
$switch_total = $conn->query($switch_count_q)->fetch_assoc()['total'] ?? 0;
$switch_total_pages = max(1, ceil($switch_total / $items_per_page));

$telephone_count_q = "SELECT COUNT(*) as total FROM telephone" . $telephone_where;
$telephone_total = $conn->query($telephone_count_q)->fetch_assoc()['total'] ?? 0;
$telephone_total_pages = max(1, ceil($telephone_total / $items_per_page));

$page_title = 'Inventory';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-boxes"></i> Equipment Inventory</h2>
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
                                $q = "SELECT * FROM desktop" . $desktop_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $desktop_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('desktop', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
                                $q = "SELECT * FROM laptops" . $laptops_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $laptops_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('laptop', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
                                $q = "SELECT * FROM printers" . $printers_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $printers_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('printer', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
                                $q = "SELECT * FROM accesspoint" . $accesspoint_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $accesspoint_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('accesspoint', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
                                $q = "SELECT * FROM `switch`" . $switch_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $switch_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('switch', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
                                $q = "SELECT * FROM telephone" . $telephone_where . " ORDER BY date_acquired DESC LIMIT $items_per_page OFFSET $telephone_offset";
                                $res = $conn->query($q);
                                if ($res && $res->num_rows > 0):
                                    while ($row = $res->fetch_assoc()):
                                ?>
                                    <tr class="clickable-row" onclick="viewEquipment('telephone', '<?php echo htmlspecialchars($row['asset_tag'], ENT_QUOTES); ?>')">
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
</script>

<style>
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

/* Responsive QR Code */
@media (max-width: 768px) {
    .qr-code-image {
        width: 150px;
        height: 150px;
    }
    
    .qr-code-tag-block {
        font-size: 16px;
        letter-spacing: 1px;
        padding: 8px 12px;
    }
    
    .qr-code-container {
        padding: 20px 15px;
    }
}

@media (max-width: 480px) {
    .qr-code-image {
        width: 120px;
        height: 120px;
    }
    
    .qr-code-tag-block {
        font-size: 14px;
        letter-spacing: 0.5px;
        padding: 6px 10px;
    }
}

/* Pagination Styles - Matching Image Design */
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

.pagination-btn.pagination-prev {
    color: #d32f2f;
}

.pagination-btn.pagination-next {
    color: #d32f2f;
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

.pagination-btn.pagination-prev:hover:not(.disabled),
.pagination-btn.pagination-next:hover:not(.disabled) {
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

/* Responsive Design */
@media (max-width: 768px) {
    .pagination-wrapper {
        flex-wrap: wrap;
        border-radius: 6px;
    }
    
    .pagination-btn {
        padding: 8px 12px;
        font-size: 13px;
        border-right: 1px solid #f3b5b5;
        border-bottom: 1px solid #f3b5b5;
    }
    
    .pagination-btn:last-child {
        border-right: 1px solid #f3b5b5;
        border-bottom: none;
        border-radius: 0;
    }
    
    .pagination-btn:nth-child(2) {
        border-radius: 6px 6px 0 0;
    }
    
    .pagination-btn.pagination-prev,
    .pagination-btn.pagination-next {
        width: 100%;
        text-align: center;
        border-right: none;
        border-bottom: 1px solid #f3b5b5;
    }
    
    .pagination-btn.pagination-prev {
        border-radius: 6px 6px 0 0;
    }
    
    .pagination-btn.pagination-next {
        border-radius: 0 0 6px 6px;
        border-bottom: none;
    }
    
    .pagination-btn.pagination-number {
        flex: 1;
        min-width: calc(25% - 1px);
    }
}

@media (max-width: 480px) {
    .pagination-btn {
        padding: 8px 10px;
        font-size: 12px;
    }
    
    .pagination-btn.pagination-number {
        min-width: calc(33.333% - 1px);
    }
}
</style>

<?php require_once 'footer.php'; ?>
