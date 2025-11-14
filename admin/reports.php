<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

$departments = $conn->query("SELECT * FROM departments ORDER BY name");
$categories = $conn->query("SELECT * FROM equipment_categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - BSU Inventory Management System</title>
    <link rel="icon" href="assets/logo/bsutneu.png" type="image/png">
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
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>
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

                    <button class="list-group-item list-group-item-action" onclick="showReportModal('financial')" style="display:none">
                        <i class="fas fa-dollar-sign text-success me-2"></i>
                        <div class="d-inline-block text-start">
                            <strong>Financial Summary Report</strong><br>
                            <small>Financial analysis including costs, budgets, and expenditure summaries.</small>
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



 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
function showReportModal(reportType) {
    const reportMap = {
        'inventory': 'complete_inventory.php',
        'financial': 'financial_report.php',
        'department': 'department_report.php',
        'maintenance': 'maintenance_report.php',
        'incomplete': 'incomplete_report.php',
        'acquisition': 'acquisition_report.php',
    };

    if (!reportMap[reportType]) return;

    // ✅ Create a temporary form for immediate download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'PDFS/' + reportMap[reportType];
    form.target = '_blank';

    // Optional hidden input for filters (date/department/etc.)
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'report_type';
    input.value = reportType;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
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