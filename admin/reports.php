<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$departments = $conn->query("SELECT * FROM departments ORDER BY name");
$categories = $conn->query("SELECT * FROM equipment_categories ORDER BY name");

// Get the base URL for PDFS folder - use relative path from admin folder
$pdfs_path = '../PDFS/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - BSU Inventory Management System</title>
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
        
        .list-group-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .list-group-item i {
            font-size: 1.5rem;
        }

        .card-header {
            border-radius: 15px 15px 0 0;
            font-weight: 600;
            padding: 20px;
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
                    <div>
                        <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                            <i class="fas fa-chart-bar text-danger"></i> Reports & Analytics
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> Generate comprehensive reports for equipment, maintenance, and service requests
                        </p>
                    </div>
                </div>

                <!-- Report Types -->
                <div class="row">
                    <!-- System Data Reports -->
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header text-white text-center" style="background-color: #dc3545;">
                                <i class="fas fa-database"></i> System Data Reports
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <button class="list-group-item list-group-item-action" onclick="showReportModal('inventory')">
                                        <i class="fas fa-list text-primary me-2"></i>
                                        <div class="d-inline-block text-start">
                                            <strong>Complete Inventory Report</strong><br>
                                            <small>Generate a comprehensive list of all equipment with detailed information.</small>
                                        </div>
                                    </button>

                                    <button class="list-group-item list-group-item-action" onclick="showReportModal('department')">
                                        <i class="fas fa-building text-info me-2"></i>
                                        <div class="d-inline-block text-start">
                                            <strong>Department Analysis Report</strong><br>
                                            <small>Equipment distribution and analysis by department.</small>
                                        </div>
                                    </button>

                                    <button class="list-group-item list-group-item-action" onclick="showReportModal('maintenance')">
                                        <i class="fas fa-tools text-warning me-2"></i>
                                        <div class="d-inline-block text-start">
                                            <strong>Maintenance & Status Report</strong><br>
                                            <small>Maintenance records, schedules, and equipment status reports.</small>
                                        </div>
                                    </button>

                                    <button class="list-group-item list-group-item-action" onclick="showReportModal('incomplete')">
                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                        <div class="d-inline-block text-start">
                                            <strong>Incomplete Items Report</strong><br>
                                            <small>Equipment with missing or incomplete information.</small>
                                        </div>
                                    </button>

                                    <button class="list-group-item list-group-item-action" onclick="showReportModal('acquisition')">
                                        <i class="fas fa-calendar-alt text-secondary me-2"></i>
                                        <div class="d-inline-block text-start">
                                            <strong>Acquisition Timeline Report</strong><br>
                                            <small>Equipment acquisition timeline and purchase history.</small>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Report Filter Modals -->
<!-- Complete Inventory Report Modal -->
<div class="modal fade" id="inventoryReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-list"></i> Complete Inventory Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../PDFS/complete_inventory.php" target="_blank">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            <?php while ($dept = $departments->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Equipment Category</label>
                        <select name="equipment_category" class="form-select">
                            <option value="">All Categories</option>
                            <?php 
                            $categories->data_seek(0); // Reset pointer
                            while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-file-pdf"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Department Analysis Report Modal -->
<div class="modal fade" id="departmentReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-building"></i> Department Analysis Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../PDFS/department_report.php" target="_blank">
                <div class="modal-body">
                    <p class="text-muted">Generate a report showing equipment distribution by department.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-file-pdf"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Maintenance & Status Report Modal -->
<div class="modal fade" id="maintenanceReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-tools"></i> Maintenance & Status Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../PDFS/maintenance_report.php" target="_blank">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <p class="text-muted small">Generate maintenance records and equipment status reports.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-file-pdf"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incomplete Items Report Modal -->
<div class="modal fade" id="incompleteReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Incomplete Items Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../PDFS/incomplete_report.php" target="_blank">
                <div class="modal-body">
                    <p class="text-muted">Generate a report showing equipment with missing or incomplete information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Acquisition Timeline Report Modal -->
<div class="modal fade" id="acquisitionReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-calendar-alt"></i> Acquisition Timeline Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../PDFS/acquisition_report.php" target="_blank">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <p class="text-muted small">Generate equipment acquisition timeline and purchase history.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-file-pdf"></i> Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showReportModal(reportType) {
    const modalMap = {
        'inventory': 'inventoryReportModal',
        'department': 'departmentReportModal',
        'maintenance': 'maintenanceReportModal',
        'incomplete': 'incompleteReportModal',
        'acquisition': 'acquisitionReportModal'
    };

    const modalId = modalMap[reportType];
    if (modalId) {
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }
}
</script>
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
