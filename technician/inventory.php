<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Search and filter setup
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$user_filter = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : '';
$location_filter = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

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

// We'll fetch each table's rows separately (for the tabs)
$desktop_where = buildWhere($search, $user_filter, $location_filter);
$laptops_where = buildWhere($search, $user_filter, $location_filter);
$printers_where = buildWhere($search, $user_filter, $location_filter);
$accesspoint_where = buildWhere($search, $user_filter, $location_filter);
$switch_where = buildWhere($search, $user_filter, $location_filter);
$telephone_where = buildWhere($search, $user_filter, $location_filter);

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
                                $q = "SELECT * FROM desktop" . $desktop_where . " ORDER BY date_acquired DESC";
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
                                $q = "SELECT * FROM laptops" . $laptops_where . " ORDER BY date_acquired DESC";
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
                                $q = "SELECT * FROM printers" . $printers_where . " ORDER BY date_acquired DESC";
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
                                $q = "SELECT * FROM accesspoint" . $accesspoint_where . " ORDER BY date_acquired DESC";
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
                                $q = "SELECT * FROM `switch`" . $switch_where . " ORDER BY date_acquired DESC";
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
                                $q = "SELECT * FROM telephone" . $telephone_where . " ORDER BY date_acquired DESC";
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Equipment Details Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-box"></i> Equipment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="equipmentModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
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
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();
    
    // Fetch equipment details
    fetch(`get_equipment_details.php?type=${encodeURIComponent(type)}&asset_tag=${encodeURIComponent(assetTag)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const equipment = data.equipment;
                let html = '<div class="row">';
                html += '<div class="col-md-12">';
                html += '<h6 class="mb-3"><i class="fas fa-info-circle"></i> Equipment Information</h6>';
                html += '<table class="table table-bordered">';
                
                // Common fields
                html += `<tr><th style="width: 30%;">Asset Tag</th><td>${escapeHtml(equipment.asset_tag || 'N/A')}</td></tr>`;
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
</style>

<?php require_once 'footer.php'; ?>

