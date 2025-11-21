<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Function to check if asset tag already exists across all equipment tables
function checkAssetTagExists($conn, $asset_tag, $exclude_table = null, $exclude_id = null) {
    $tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone', 'equipment'];
    
    foreach ($tables as $table) {
        // Skip excluded table/id for edit operations
        if ($exclude_table && $exclude_table === $table && $exclude_id) {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE asset_tag = ? AND id != ?");
            $stmt->bind_param("si", $asset_tag, $exclude_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE asset_tag = ?");
            $stmt->bind_param("s", $asset_tag);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return true; // Asset tag exists
        }
        $stmt->close();
    }
    return false; // Asset tag is unique
}

// Handle Add Equipment (form POST handled here)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_equipment') {
    $type = $conn->real_escape_string($_POST['type'] ?? '');
    // Generic fields (present in most tables)
    $asset_tag = $conn->real_escape_string($_POST['asset_tag'] ?? '');
    
    // Check for duplicate asset tag
    if (!empty($asset_tag) && checkAssetTagExists($conn, $asset_tag)) {
        $error = "Asset tag '$asset_tag' already exists. Please use a unique asset tag.";
    } else {
        $property_equipment = $conn->real_escape_string($_POST['property_equipment'] ?? '');
    $department = $conn->real_escape_string($_POST['department'] ?? '');
    $assigned_person = $conn->real_escape_string($_POST['assigned_person'] ?? '');
    $location = $conn->real_escape_string($_POST['location'] ?? '');
    $date_acquired = !empty($_POST['date_acquired']) ? $conn->real_escape_string($_POST['date_acquired']) : null;
    $useful_life = $conn->real_escape_string($_POST['useful_life'] ?? '');
    $hardware_specifications = $conn->real_escape_string($_POST['hardware_specifications'] ?? '');
    $software_specifications = $conn->real_escape_string($_POST['software_specifications'] ?? '');
    $high_value_ics_no = $conn->real_escape_string($_POST['high_value_ics_no'] ?? '');
    $inventory_item_no = $conn->real_escape_string($_POST['inventory_item_no'] ?? '');
    $remarks = $conn->real_escape_string($_POST['remarks'] ?? '');

    // Desktop extra fields
    $processor = $conn->real_escape_string($_POST['processor'] ?? '');
    $ram = $conn->real_escape_string($_POST['ram'] ?? '');
    $gpu = $conn->real_escape_string($_POST['gpu'] ?? '');
    $hard_drive = $conn->real_escape_string($_POST['hard_drive'] ?? '');
    $operating_system = $conn->real_escape_string($_POST['operating_system'] ?? '');


	include '../logger.php';
	logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], "Equipment", "Added an equipment. Category Type:". $type);


    // Insert based on type
    if ($type === 'desktop') {
        // Adjust columns if your desktop table has different column names
        $stmt = $conn->prepare("INSERT INTO desktop (asset_tag, property_equipment, assigned_person, location, processor, ram, gpu, hard_drive, operating_system, date_acquired, inventory_item_no, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss",
            $asset_tag, $property_equipment, $assigned_person, $location,
            $processor, $ram, $gpu, $hard_drive, $operating_system,
            $date_acquired, $inventory_item_no, $remarks
        );
    } elseif (in_array($type, ['laptop','printer','accesspoint','switch','telephone'])) {
        // Map type to table name
        $map = [
            'laptop' => 'laptops',
            'printer' => 'printers',
            'accesspoint' => 'accesspoint',
            'switch' => 'switch',
            'telephone' => 'telephone'
        ];
        $table = $map[$type];

        $stmt = $conn->prepare("INSERT INTO {$table} (asset_tag, property_equipment, department, assigned_person, location, date_acquired, useful_life, hardware_specifications, software_specifications, high_value_ics_no, inventory_item_no, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssisssss",
            $asset_tag, $property_equipment, $department, $assigned_person, $location,
            $date_acquired, $useful_life, $hardware_specifications, $software_specifications,
            $high_value_ics_no, $inventory_item_no, $remarks
        );
    } else {
        $error = "Unknown equipment type.";
    }

    if (empty($error)) {
        if ($stmt->execute()) {
            $stmt->close();
            // redirect to avoid resubmission
            header("Location: equipment.php?added=1");
            exit;
        } else {
            $error = "Insert failed: " . $stmt->error;
            $stmt->close();
        }
    }
    } // Close validation else block
}

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - BSU Inventory Management System</title>
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
    </style>
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
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="fas fa-laptop"></i> Equipment</h2>
        <div>
          <button class="btn btn-secondary me-2" onclick="printAllLabels()"><i class="fas fa-print"></i> Print All Labels</button>
          <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addEquipmentModal"><i class="fas fa-plus"></i> Add Equipment</button>
        </div>
      </div>

      <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="alert alert-success">✅ Equipment added successfully!</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- Search -->
      <form method="GET" class="mb-3 d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search asset tag, user or location..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="user" class="form-select me-2" style="max-width:200px;">
          <option value="">All users</option>
          <?php foreach ($all_users as $user): ?>
            <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($user_filter==$user) ? 'selected':''; ?>>
              <?php echo htmlspecialchars($user); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="location" class="form-select me-2" style="max-width:200px;">
          <option value="">All locations</option>
          <?php foreach ($all_locations as $location): ?>
            <option value="<?php echo htmlspecialchars($location); ?>" <?php echo ($location_filter==$location) ? 'selected':''; ?>>
              <?php echo htmlspecialchars($location); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i></button>
      </form>

      <!-- Tabs -->
      <ul class="nav nav-tabs mb-2" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desktops" type="button">Desktops</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#laptops" type="button">Laptops</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#printers" type="button">Printers</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#accesspoints" type="button">Access Points</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#switches" type="button">Switches</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#telephones" type="button">Telephones</button></li>
      </ul>

      <div class="tab-content border bg-white p-3 rounded-bottom shadow-sm">
        <!-- Desktops -->
        <div class="tab-pane fade show active" id="desktops">
          <h5>🖥️ Desktop Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Processor</th><th>OS</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM desktop" . $desktop_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
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
    <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
    <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
    <td><?php echo htmlspecialchars($row['location']); ?></td>
    <td><?php echo htmlspecialchars($row['processor'] ?? ''); ?></td>
    <td><?php echo htmlspecialchars($row['operating_system'] ?? ''); ?></td>
    
  </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Laptops -->
        <div class="tab-pane fade" id="laptops">
          <h5>💻 Laptop Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Hardware</th><th>Software</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM laptops" . $laptops_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
                  data-type="generic"
                  data-equipment="Laptop"
                  data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                  data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                  data-location="<?php echo htmlspecialchars($row['location']); ?>"
                  data-specs="<?php echo 'HW: '.htmlspecialchars($row['hardware_specifications']).' | SW: '.htmlspecialchars($row['software_specifications']); ?>"
                  data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                  data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                  data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['hardware_specifications']); ?></td>
                <td><?php echo htmlspecialchars($row['software_specifications']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Printers -->
        <div class="tab-pane fade" id="printers">
          <h5>🖨️ Printer Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Remarks</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM printers" . $printers_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
                  data-type="generic"
                  data-equipment="Printer"
                  data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                  data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                  data-location="<?php echo htmlspecialchars($row['location']); ?>"
                  data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                  data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                  data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                  data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Access Points -->
        <div class="tab-pane fade" id="accesspoints">
          <h5>📡 Access Point Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Remarks</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM accesspoint" . $accesspoint_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
                  data-type="generic"
                  data-equipment="Access Point"
                  data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                  data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                  data-location="<?php echo htmlspecialchars($row['location']); ?>"
                  data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                  data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                  data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                  data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Switches -->
        <div class="tab-pane fade" id="switches">
          <h5>🔀 Switch Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Remarks</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM `switch`" . $switch_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
                  data-type="generic"
                  data-equipment="Switch"
                  data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                  data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                  data-location="<?php echo htmlspecialchars($row['location']); ?>"
                  data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                  data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                  data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                  data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Telephones -->
        <div class="tab-pane fade" id="telephones">
          <h5>☎️ Telephone Inventory</h5>
          <div class="table-responsive">
          <table class="table table-hover">
            <thead><tr><th>Asset Tag</th><th>User</th><th>Location</th><th>Remarks</th></tr></thead>
            <tbody>
            <?php
            $q = "SELECT * FROM telephone" . $telephone_where . " ORDER BY date_acquired DESC";
            $res = $conn->query($q);
            while ($row = $res->fetch_assoc()):
            ?>
              <tr class="clickable-row"
                  data-type="generic"
                  data-equipment="Telephone"
                  data-asset="<?php echo htmlspecialchars($row['asset_tag']); ?>"
                  data-user="<?php echo htmlspecialchars($row['assigned_person']); ?>"
                  data-location="<?php echo htmlspecialchars($row['location']); ?>"
                  data-specs="<?php echo htmlspecialchars($row['hardware_specifications']); ?>"
                  data-date="<?php echo htmlspecialchars($row['date_acquired'] ?? ''); ?>"
                  data-itemno="<?php echo htmlspecialchars($row['inventory_item_no'] ?? ''); ?>"
                  data-remarks="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                <td><?php echo htmlspecialchars($row['asset_tag']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_person']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['remarks']); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Universal Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="modal_title">📦 Equipment Details</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <div class="row">
        <!-- Left: Information -->
        <div class="col-md-8">
          <ul class="list-group" id="equipment_details">
            <!-- Dynamic content will be inserted here -->
          </ul>
        </div>
        <!-- Right: QR Code -->
        <div class="col-md-4 text-center">
		<center>
          <div id="equipment_qrcode"></div>
          <p class="mt-2 text-muted">Scan QR for details</p>
		  </center>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-primary" onclick="editFromModal()">Edit</button>
      <button class="btn btn-success" onclick="showPrintPreview()">Print</button>
      <button class="btn btn-danger" onclick="deleteFromModal()">Delete</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
  </div></div>
</div>

<!-- Print Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">📄 Print Preview - BSU Asset Label</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="previewContent">
          <!-- Preview content will be inserted here -->
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" onclick="printFromPreview()">
          <i class="fas fa-print"></i> Print
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!-- Add Equipment Modal (dynamic fields) -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST">
      <input type="hidden" name="action" value="add_equipment">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Add Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Equipment Type</label>
            <select name="type" id="add-type" class="form-select" required>
              <option value="">-- Select Type --</option>
              <option value="desktop">Desktop</option>
              <option value="laptop">Laptop</option>
              <option value="printer">Printer</option>
              <option value="accesspoint">Access Point</option>
              <option value="switch">Switch</option>
              <option value="telephone">Telephone</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Asset Tag</label>
            <input type="text" name="asset_tag" id="asset_tag" class="form-control" required>
            <div id="asset_tag_feedback" class="invalid-feedback"></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Property / Equipment</label>
            <input type="text" name="property_equipment" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Department</label>
            <input type="text" name="department" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Assigned Person</label>
            <input type="text" name="assigned_person" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">Date Acquired</label>
            <input type="date" name="date_acquired" class="form-control">
          </div>


          <div class="col-md-4">
            <label class="form-label">Inventory Item No</label>
            <input type="text" name="inventory_item_no" class="form-control">
          </div>

          <!-- Generic fields -->
          <div class="col-12" id="generic-fields">
            <label class="form-label">Hardware Specifications</label>
            <textarea name="hardware_specifications" class="form-control"></textarea>
            <label class="form-label mt-2">Software Specifications</label>
            <textarea name="software_specifications" class="form-control"></textarea>
            <label class="form-label mt-2">Useful Life</label>
            <input type="text" name="useful_life" class="form-control">
            <label class="form-label mt-2">High Value ICS No</label>
            <input type="text" name="high_value_ics_no" class="form-control">
          </div>

          <!-- Desktop specific -->
          <div class="col-12 d-none" id="desktop-fields">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label">Processor</label>
                <input type="text" name="processor" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">RAM</label>
                <input type="text" name="ram" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">GPU</label>
                <input type="text" name="gpu" class="form-control">
              </div>
              <div class="col-md-6 mt-2">
                <label class="form-label">Hard Drive</label>
                <input type="text" name="hard_drive" class="form-control">
              </div>
              <div class="col-md-6 mt-2">
                <label class="form-label">Operating System</label>
                <input type="text" name="operating_system" class="form-control">
              </div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label mt-2">Remarks</label>
            <textarea name="remarks" class="form-control"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentAssetTag = '';
let currentType = '';

// Function to check asset tag uniqueness
function checkAssetTagUniqueness(assetTag) {
  if (!assetTag) return;
  
  fetch(`check_asset_tag.php?asset_tag=${encodeURIComponent(assetTag)}`)
    .then(response => response.json())
    .then(data => {
      const input = document.getElementById('asset_tag');
      const feedback = document.getElementById('asset_tag_feedback');
      
      if (data.exists) {
        input.classList.add('is-invalid');
        feedback.textContent = 'This asset tag already exists. Please use a unique asset tag.';
        feedback.style.display = 'block';
      } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error checking asset tag:', error);
    });
}

document.addEventListener('DOMContentLoaded', function() {
  // Asset tag validation on input
  const assetTagInput = document.getElementById('asset_tag');
  if (assetTagInput) {
    let timeout;
    assetTagInput.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        checkAssetTagUniqueness(this.value);
      }, 500); // Debounce for 500ms
    });
  }
  // When a row is clicked → open universal equipment modal
  document.querySelectorAll('.clickable-row').forEach(function(row) {
    row.addEventListener('click', function() {
      currentAssetTag = row.dataset.asset;
      currentType = row.dataset.type || row.dataset.equipment;

      // Get equipment type and icon
      const equipmentType = row.dataset.type === 'desktop' ? 'Desktop' : (row.dataset.equipment || 'Equipment');
      const icons = {
        'Desktop': '🖥️',
        'Laptop': '💻', 
        'Printer': '🖨️',
        'Access Point': '📡',
        'Switch': '🔀',
        'Telephone': '☎️'
      };
      const icon = icons[equipmentType] || '📦';

      // Update modal title
      document.getElementById('modal_title').textContent = `${icon} ${equipmentType} Details`;

      // Build equipment details based on type
      const detailsContainer = document.getElementById('equipment_details');
      detailsContainer.innerHTML = '';

      // Common fields for all equipment types
      const commonFields = [
        { label: 'Asset Tag', value: row.dataset.asset },
        { label: 'Assigned Person', value: row.dataset.user },
        { label: 'Location', value: row.dataset.location },
        { label: 'Date Acquired', value: row.dataset.date },
        { label: 'Inventory Item No', value: row.dataset.itemno },
        { label: 'Remarks', value: row.dataset.remarks }
      ];

      // Desktop-specific fields
      if (row.dataset.type === 'desktop') {
        const desktopFields = [
          { label: 'Processor', value: row.dataset.processor },
          { label: 'RAM', value: row.dataset.ram },
          { label: 'GPU', value: row.dataset.gpu },
          { label: 'Hard Drive', value: row.dataset.hdd },
          { label: 'OS', value: row.dataset.os }
        ];
        // Insert desktop fields after Asset Tag
        commonFields.splice(3, 0, ...desktopFields);
      } else {
        // Generic equipment fields
        const genericFields = [
          { label: 'Equipment Type', value: equipmentType },
          { label: 'Specifications', value: row.dataset.specs }
        ];
        // Insert generic fields after Asset Tag
        commonFields.splice(2, 0, ...genericFields);
      }

      // Create list items
      commonFields.forEach(field => {
        if (field.value && field.value.trim() !== '') {
          const li = document.createElement('li');
          li.className = 'list-group-item';
          li.innerHTML = `<strong>${field.label}:</strong> <span id="eq_${field.label.toLowerCase().replace(/\s+/g, '_')}">${field.value}</span>`;
          detailsContainer.appendChild(li);
        }
      });

      // Generate QR Code
      const qrText = row.dataset.asset || "N/A";
      document.getElementById("equipment_qrcode").innerHTML = "";
      new QRCode(document.getElementById("equipment_qrcode"), {
        text: qrText,
        width: 180,
        height: 180
      });

      // Show universal equipment modal
      new bootstrap.Modal(document.getElementById('equipmentModal')).show();
    });
  });
});

// ✅ Real Edit
function editFromModal() {
  if (!currentAssetTag || !currentType) {
    alert("No equipment selected.");
    return;
  }
  window.location.href = "edit_equipment.php?asset_tag=" + encodeURIComponent(currentAssetTag) + "&type=" + encodeURIComponent(currentType);
}

// ✅ Show Print Preview
function showPrintPreview() {
  // Get all the equipment details from the universal modal
  const assetTag = document.querySelector('#eq_asset_tag') ? document.querySelector('#eq_asset_tag').textContent : '';
  const assignedPerson = document.querySelector('#eq_assigned_person') ? document.querySelector('#eq_assigned_person').textContent : '';
  const location = document.querySelector('#eq_location') ? document.querySelector('#eq_location').textContent : '';
  const processor = document.querySelector('#eq_processor') ? document.querySelector('#eq_processor').textContent : '';
  const ram = document.querySelector('#eq_ram') ? document.querySelector('#eq_ram').textContent : '';
  const gpu = document.querySelector('#eq_gpu') ? document.querySelector('#eq_gpu').textContent : '';
  const hardDrive = document.querySelector('#eq_hard_drive') ? document.querySelector('#eq_hard_drive').textContent : '';
  const os = document.querySelector('#eq_os') ? document.querySelector('#eq_os').textContent : '';
  const equipmentType = document.querySelector('#eq_equipment_type') ? document.querySelector('#eq_equipment_type').textContent : '';
  const specifications = document.querySelector('#eq_specifications') ? document.querySelector('#eq_specifications').textContent : '';
  const dateAcquired = document.querySelector('#eq_date_acquired') ? document.querySelector('#eq_date_acquired').textContent : '';
  const inventoryItemNo = document.querySelector('#eq_inventory_item_no') ? document.querySelector('#eq_inventory_item_no').textContent : '';
  const remarks = document.querySelector('#eq_remarks') ? document.querySelector('#eq_remarks').textContent : '';
  
  // Get the QR code image
  const qrCodeImg = document.querySelector('#equipment_qrcode img');
  const qrCodeSrc = qrCodeImg ? qrCodeImg.src : '';
  
  // Create the preview HTML
  const previewHTML = `
    <div style="
      font-family: Arial, sans-serif; 
      margin: 0; 
      padding: 20px; 
      background: white;
      text-align: center;
    ">
      <div style="
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
            <img src="assets/logo/bsutneu.png" alt="BSU Logo" style="
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
            ${qrCodeSrc ? `<img src="${qrCodeSrc}" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">` : '<div style="width: 100px; height: 100px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px;">QR Code</div>'}
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
              <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;"></div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
              <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 90px;">Specification:</span>
              <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;"></div>
            </div>
            
            <div style="border-top: 1px solid #000; padding-top: 18px; margin-top: 10px; display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
              <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 65px;">Date Acq:</span>
              <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px;"></div>
              <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 35px;">Amt:</span>
              <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px;"></div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 5px;">
              <span style="font-size: 13px; font-weight: bold; color: #000; white-space: nowrap; min-width: 65px;">End User:</span>
              <div style="flex: 1; border-bottom: 2px solid #000; height: 18px; margin: 0 8px; min-width: 50px;"></div>
            </div>
          </div>
          
          <div style="text-align: center; margin-top: 35px; padding-top: 15px;">
            <div style="width: 180px; border-bottom: 2px solid #000; margin: 0 auto 8px; height: 18px;"></div>
            <div style="font-size: 11px; font-weight: bold; color: #000;">Supply Officer</div>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Insert the preview content
  document.getElementById('previewContent').innerHTML = previewHTML;
  
  // Show the preview modal
  new bootstrap.Modal(document.getElementById('previewModal')).show();
}

// ✅ Print from Preview
function printFromPreview() {
  // Get all the equipment details from the universal modal
  const assetTag = document.querySelector('#eq_asset_tag') ? document.querySelector('#eq_asset_tag').textContent : '';
  const assignedPerson = document.querySelector('#eq_assigned_person') ? document.querySelector('#eq_assigned_person').textContent : '';
  const location = document.querySelector('#eq_location') ? document.querySelector('#eq_location').textContent : '';
  const processor = document.querySelector('#eq_processor') ? document.querySelector('#eq_processor').textContent : '';
  const ram = document.querySelector('#eq_ram') ? document.querySelector('#eq_ram').textContent : '';
  const gpu = document.querySelector('#eq_gpu') ? document.querySelector('#eq_gpu').textContent : '';
  const hardDrive = document.querySelector('#eq_hard_drive') ? document.querySelector('#eq_hard_drive').textContent : '';
  const os = document.querySelector('#eq_os') ? document.querySelector('#eq_os').textContent : '';
  const equipmentType = document.querySelector('#eq_equipment_type') ? document.querySelector('#eq_equipment_type').textContent : '';
  const specifications = document.querySelector('#eq_specifications') ? document.querySelector('#eq_specifications').textContent : '';
  const dateAcquired = document.querySelector('#eq_date_acquired') ? document.querySelector('#eq_date_acquired').textContent : '';
  const inventoryItemNo = document.querySelector('#eq_inventory_item_no') ? document.querySelector('#eq_inventory_item_no').textContent : '';
  const remarks = document.querySelector('#eq_remarks') ? document.querySelector('#eq_remarks').textContent : '';
  
  // Get the QR code image
  const qrCodeImg = document.querySelector('#equipment_qrcode img');
  const qrCodeSrc = qrCodeImg ? qrCodeImg.src : '';
  
  // Create a new window for printing
  const printWindow = window.open('', '_blank');
  
  // Create the print HTML matching BSU asset label format
  const printHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>BSU Asset Label - ${assetTag}</title>
      <style>
        body { 
          font-family: Arial, sans-serif; 
          margin: 0; 
          padding: 20px; 
          background: white;
        }
        .asset-label {
          width: 100%;
          max-width: 700px;
          margin: 0 auto;
          border: 4px solid #dc3545;
          background: white;
          padding: 25px;
          box-sizing: border-box;
          position: relative;
        }
        .header-section {
          display: flex;
          align-items: flex-start;
          margin-bottom: 25px;
          position: relative;
        }
        .logo {
          width: 70px;
          height: 70px;
          margin-right: 20px;
          flex-shrink: 0;
        }
        .logo img {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        .university-name {
          font-size: 20px;
          font-weight: bold;
          text-transform: uppercase;
          color: #000;
          letter-spacing: 1px;
          flex: 1;
          margin-right: 140px; /* Space for QR code */
        }
        .qr-section {
          position: absolute;
          top: 0;
          right: 0;
          width: 100px;
          height: 100px;
          z-index: 1;
        }
        .asset-tag-section {
          position: absolute;
          top: 105px;
          right: 0;
          text-align: center;
          width: 100px;
          font-size: 10px;
          font-weight: bold;
          color: #000;
          line-height: 1;
        }
        .qr-code {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
        .form-content {
          margin-top: 50px;
        }
        .form-section {
          display: flex;
          flex-direction: column;
          gap: 18px;
        }
        .form-row {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 5px;
        }
        .form-row.top-section {
          margin-bottom: 8px;
        }
        .form-row.bottom-section {
          border-top: 1px solid #000;
          padding-top: 18px;
          margin-top: 10px;
        }
        .form-label {
          font-size: 13px;
          font-weight: bold;
          color: #000;
          white-space: nowrap;
        }
        .form-label.property-no { min-width: 90px; }
        .form-label.article { min-width: 55px; }
        .form-label.specification { min-width: 90px; }
        .form-label.date-acq { min-width: 65px; }
        .form-label.end-user { min-width: 65px; }
        .form-label.amount { min-width: 35px; }
        .form-line {
          flex: 1;
          border-bottom: 2px solid #000;
          height: 18px;
          margin: 0 8px;
          min-width: 50px;
        }
        .form-line.short {
          flex: 1;
          border-bottom: 2px solid #000;
          height: 18px;
          margin: 0 8px;
          min-width: 50px;
        }
        .signature-section {
          text-align: center;
          margin-top: 35px;
          padding-top: 15px;
        }
        .signature-line {
          width: 180px;
          border-bottom: 2px solid #000;
          margin: 0 auto 8px;
          height: 18px;
        }
        .signature-label {
          font-size: 11px;
          font-weight: bold;
          color: #000;
        }
        @media print {
          body { 
            margin: 0; 
            padding: 15px; 
          }
          .asset-label { 
            border: 4px solid #dc3545; 
            max-width: 100%;
          }
        }
      </style>
    </head>
    <body>
      <div class="asset-label">
        <div class="header-section">
          <div class="logo">
            <img src="assets/logo/bsutneu.png" alt="BSU Logo" onerror="this.style.display='none';">
          </div>
          <div class="university-name">BATANGAS STATE UNIVERSITY</div>
          <div class="qr-section">
            ${qrCodeSrc ? `<img src="${qrCodeSrc}" alt="QR Code" class="qr-code">` : '<div style="width: 120px; height: 120px; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px;">QR Code</div>'}
          </div>
          <div class="asset-tag-section">
            ${assetTag}
          </div>
        </div>
        
        <div class="form-content">
          <div class="form-section">
            <div class="form-row top-section">
              <span class="form-label property-no">Property No:</span>
              <div class="form-line"></div>
            </div>
            
            <div class="form-row top-section">
              <span class="form-label article">Article:</span>
              <div class="form-line">${equipmentType || 'Desktop Computer'}</div>
            </div>
            
            <div class="form-row top-section">
              <span class="form-label specification">Specification:</span>
              <div class="form-line">${processor || specifications || ''}</div>
            </div>
            
            <div class="form-row bottom-section">
              <span class="form-label date-acq">Date Acq:</span>
              <div class="form-line">${dateAcquired || ''}</div>
              <span class="form-label amount">Amt:</span>
              <div class="form-line"></div>
            </div>
            
            <div class="form-row bottom-section">
              <span class="form-label end-user">End User:</span>
              <div class="form-line">${assignedPerson || ''}</div>
            </div>
          </div>
          
          <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-label">Supply Officer</div>
          </div>
        </div>
      </div>
    </body>
    </html>
  `;
  
  // Write the HTML to the print window
  printWindow.document.write(printHTML);
  printWindow.document.close();
  
  // Wait for images to load, then print
  let imagesLoaded = 0;
  const totalImages = 2; // BSU logo + QR code
  
  function checkImagesAndPrint() {
    imagesLoaded++;
    if (imagesLoaded >= totalImages || imagesLoaded >= 1) { // Print even if one image fails
      setTimeout(() => {
        printWindow.print();
        printWindow.close();
      }, 1000);
    }
  }
  
  // Handle image loading
  const bsuLogo = printWindow.document.querySelector('.logo img');
  const qrCode = printWindow.document.querySelector('.qr-code');
  
  if (bsuLogo) {
    bsuLogo.onload = checkImagesAndPrint;
    bsuLogo.onerror = checkImagesAndPrint;
  } else {
    checkImagesAndPrint();
  }
  
  if (qrCode) {
    qrCode.onload = checkImagesAndPrint;
    qrCode.onerror = checkImagesAndPrint;
  } else {
    checkImagesAndPrint();
  }
}


// ✅ Real Delete
function deleteFromModal() {
  if (!currentAssetTag || !currentType) {
    alert("No equipment selected.");
    return;
  }
  if (confirm("Are you sure you want to delete this equipment?")) {
    // Create a form and submit via POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'delete_equipment.php';

    const inputAsset = document.createElement('input');
    inputAsset.type = 'hidden';
    inputAsset.name = 'asset_tag';
    inputAsset.value = currentAssetTag;
    form.appendChild(inputAsset);

    const inputType = document.createElement('input');
    inputType.type = 'hidden';
    inputType.name = 'type';
    inputType.value = currentType;
    form.appendChild(inputType);

    document.body.appendChild(form);
    form.submit();
  }
}

// ✅ Print All Labels
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
    const equipmentType = row.dataset.type || row.dataset.equipment || 'Equipment';
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
              <img src="assets/logo/bsutneu.png" alt="BSU Logo" style="
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
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Intercept logout button and show confirm modal
  document.addEventListener('click', function(e) {
    const logoutLink = e.target.closest('a[href="logout.php"]');
    if (!logoutLink) return;
    e.preventDefault();
    let modalEl = document.getElementById('logoutConfirmModal');
    if (!modalEl) {
      // Create modal lazily if not present
      const wrapper = document.createElement('div');
      wrapper.innerHTML = '\n<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-dialog-centered">\n    <div class="modal-content">\n      <div class="modal-header">\n        <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> Confirm Logout</h5>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body">Are you sure you want to log out?</div>\n      <div class="modal-footer">\n        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\n        <button type="button" class="btn btn-danger confirm-logout">Logout</button>\n      </div>\n    </div>\n  </div>\n</div>\n';
      document.body.appendChild(wrapper.firstElementChild);
      modalEl = document.getElementById('logoutConfirmModal');
    }
    if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
      window.location.href = logoutLink.href;
      return;
    }
    const confirmBtn = modalEl.querySelector('.confirm-logout');
    if (confirmBtn) {
      confirmBtn.onclick = function() { window.location.href = logoutLink.href; };
    }
    new bootstrap.Modal(modalEl).show();
  });
});
</script>

</body>
</html>
