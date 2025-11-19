<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
requireLogin();

// Fetch logs
$result = $conn->query("SELECT * FROM admin_logs ORDER BY created_at DESC");
$logs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - BSU Inventory Management System</title>
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
        .page-item.active .page-link { background-color: #dc3545 !important; border-color: #dc3545 !important; }
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
                <h2><i class="fas fa-clipboard-list"></i> System Logs</h2>

                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-list"></i> Logs
                    </div>
                    <div class="card-body">
                        <table id="logsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?></td>
                                        <td><?= htmlspecialchars($log['action']); ?></td>
                                        <td><?= htmlspecialchars($log['description']); ?></td>
                                        <td><?= htmlspecialchars($log['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ DataTables + Export -->
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
    $('#logsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success', text: '<i class="fas fa-file-excel"></i> Excel' },
            { extend: 'pdfHtml5', className: 'btn btn-danger', text: '<i class="fas fa-file-pdf"></i> PDF' },
            { extend: 'print', className: 'btn btn-secondary', text: '<i class="fas fa-print"></i> Print' }
        ],
        order: [[3, 'desc']] // ✅ correct index for "Date & Time" column
    });
});
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
