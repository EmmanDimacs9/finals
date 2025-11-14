<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Handle maintenance operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $equipment_id = $_POST['equipment_id'];
                $technician_id = $_POST['technician_id'];
                $maintenance_type = $_POST['maintenance_type'];
                $description = trim($_POST['description']);
                $cost = $_POST['cost'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];

                $stmt = $conn->prepare("INSERT INTO maintenance_records 
    (equipment_id, technician_id, maintenance_type, description, cost, start_date, end_date, status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$status = 'scheduled'; 

$stmt->bind_param("iissdsss", 
    $equipment_id, 
    $technician_id, 
    $maintenance_type, 
    $description, 
    $cost, 
    $start_date, 
    $end_date, 
    $status
);

if ($stmt->execute()) {
    $message = 'Maintenance record added successfully!';
} else {
    $error = 'Failed to add maintenance record.';
}

                $stmt->close();
                break;

            case 'update':
                $id = $_POST['id'];
                $equipment_id = $_POST['equipment_id'];
                $technician_id = $_POST['technician_id'];
                $maintenance_type = $_POST['maintenance_type'];
                $description = trim($_POST['description']);
                $cost = $_POST['cost'];
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $status = $_POST['status'];

                $stmt = $conn->prepare("UPDATE maintenance_records 
                    SET equipment_id = ?, technician_id = ?, maintenance_type = ?, description = ?, cost = ?, start_date = ?, end_date = ?, status = ? 
                    WHERE id = ?");
                $stmt->bind_param("iissdsssi", $equipment_id, $technician_id, $maintenance_type, $description, $cost, $start_date, $end_date, $status, $id);

                if ($stmt->execute()) {
                    $message = 'Maintenance record updated successfully!';
                } else {
                    $error = 'Failed to update maintenance record.';
                }
                $stmt->close();
                break;

            case 'delete':
                $id = $_POST['id'];
                $stmt = $conn->prepare("DELETE FROM maintenance_records WHERE id = ?");
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    $message = 'Maintenance record deleted successfully!';
                } else {
                    $error = 'Failed to delete maintenance record.';
                }
                $stmt->close();
                break;
        }
    }
}

// Get maintenance records
$maintenance_records = $conn->query("
    SELECT mr.*, u.full_name AS technician_name
    FROM maintenance_records mr
    LEFT JOIN users u ON mr.technician_id = u.id
    ORDER BY mr.created_at DESC
");

// For dropdowns (equipments list is assumed to be stored elsewhere or in maintenance_records only)
$equipment_list = $conn->query("SELECT DISTINCT id, CONCAT('Equipment #', id) AS name FROM maintenance_records ORDER BY id DESC");
$technicians = $conn->query("SELECT id, full_name FROM users WHERE role = 'technician' ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - BSU Inventory Management System</title>
    <link rel="icon" href="assets/logo/bsutneu.png" type="image/png">
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
                    <h2><i class="fas fa-tools"></i> Maintenance Management</h2>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                        <i class="fas fa-plus"></i> Schedule Maintenance
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Maintenance Records Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
						<table id="maintenanceTable" class="table table-striped table-bordered">
                            
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Equipment</th>
                                        <th>Technician</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Cost</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($record = $maintenance_records->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $record['id']; ?></td>
                                            <td><?php echo htmlspecialchars($record['equipment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($record['technician_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['maintenance_type']); ?></td>
                                            <td><?php echo htmlspecialchars($record['status']); ?></td>
                                            <td><?php echo $record['start_date']; ?></td>
                                            <td><?php echo $record['end_date']; ?></td>
                                            <td>₱<?php echo number_format($record['cost'], 2); ?></td>
                                            <td><?php echo $record['remarks']; ?></td>
                                            
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

    <!-- Add Maintenance Modal -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Maintenance</h5>
                    <button type="button" class="btn-danger" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Equipment ID</label>
                            <input type="number" class="form-control" name="equipment_id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Technician</label>
                            <select class="form-control" name="technician_id" required>
                                <option value="">Select Technician</option>
                                <?php while ($tech = $technicians->fetch_assoc()): ?>
                                    <option value="<?php echo $tech['id']; ?>"><?php echo $tech['full_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maintenance Type</label>
                            <select class="form-control" name="maintenance_type" required>
                                <option value="preventive">Preventive</option>
                                <option value="corrective">Corrective</option>
                                <option value="upgrade">Upgrade</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estimated Cost</label>
                            <input type="number" step="0.01" class="form-control" name="cost">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function deleteMaintenance(id) {
        if (confirm('Are you sure you want to delete this maintenance record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    $('#maintenanceTable').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success', text: '<i class="fas fa-file-excel"></i> Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-danger', text: '<i class="fas fa-file-pdf"></i> PDF' },
            { extend: 'print', className: 'btn btn-secondary', text: '<i class="fas fa-print"></i> Print' }
        ],
        order: [[1, 'desc']]
    });
});
</script>

<style>
.dataTables_filter {
    text-align: right !important;
}
</style>
<script>
// Logout confirmation
document.addEventListener('click', function(e) {
    const logoutLink = e.target.closest('a[href="logout.php"]');
    if (!logoutLink) return;
    e.preventDefault();
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = logoutLink.href;
    }
});
</script>

</body>
</html>
