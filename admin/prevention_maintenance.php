<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prevention Maintenance Plan Creation - BSU Inventory Management System</title>
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
            <div class="col-md-9 col-lg-10 p-4">
                <h2><i class="fas fa-calendar-check"></i> Prevention Maintenance Plan Creation</h2>
                <div class="card p-4 mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This page is for creating preventive maintenance plans for equipment.
                    </div>
                    <p class="text-muted">Prevention maintenance plan creation functionality will be implemented here.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

