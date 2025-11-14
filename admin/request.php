<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

// First, ensure the requests table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS `requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `form_type` varchar(255) NOT NULL,
    `form_data` longtext DEFAULT NULL,
    `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `form_type` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$conn->query($createTableQuery);

// Check if form_data column exists, if not add it
$checkColumnQuery = "SHOW COLUMNS FROM `requests` LIKE 'form_data'";
$columnResult = $conn->query($checkColumnQuery);

if ($columnResult->num_rows == 0) {
    $addColumnQuery = "ALTER TABLE `requests` ADD COLUMN `form_data` longtext DEFAULT NULL AFTER `form_type`";
    $conn->query($addColumnQuery);
}

// Fetch all requests with user information
$query = "SELECT r.*, u.full_name FROM requests r 
          LEFT JOIN users u ON r.user_id = u.id 
          ORDER BY r.created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("❌ Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - BSU Inventory Management System</title>
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
        .btn-view { background-color: #ffc107; color: black; border: none; }
        .btn-approve { background-color: #6c757d; color: white; border: none; }
        .btn-reject { background-color: #dc3545; color: white; border: none; }
        .btn-view:hover { background-color: #e0a800 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-approve:hover { background-color: #5a6268 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-reject:hover { background-color: #c82333 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-outline-danger:hover { background-color: #dc3545 !important; color: white !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-action { transition: all 0.3s ease; }
        .page-item.active .page-link { background-color: #dc3545 !important; border-color: #dc3545 !important; }
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
                <h2><i class="fas fa-envelope"></i> Requests for Approval</h2>
                <div class="card p-4 mt-3">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="requestTable" class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Form Type</th>
                                        <th>Status</th>
                                        <th>Date Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown User') ?></td>
                                            <td><?= htmlspecialchars($row['form_type']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($row['status']) {
                                                    'Approved' => 'bg-success text-white',
                                                    'Rejected' => 'bg-danger text-white',
                                                    default => 'bg-warning text-dark'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                                            <td>
                                                <!-- View Button -->
                                                <a href="view_request.php?id=<?= $row['id'] ?>" class="btn btn-view btn-sm btn-action">
                                                    <i class="fas fa-eye"></i> View
                                                </a>

                                                <!-- Approve Button -->
                                                <a href="approve_request.php?id=<?= $row['id'] ?>" class="btn btn-approve btn-sm btn-action" onclick="return confirm('Approve this request?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>

                                                <!-- Reject Button -->
                                                <a href="reject_request.php?id=<?= $row['id'] ?>" class="btn btn-reject btn-sm btn-action" onclick="return confirm('Reject this request?');">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>

                                                <!-- Delete Button -->
                                                <a href="delete_request.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm btn-action" onclick="return confirm('Are you sure you want to delete this request? This action cannot be undone.');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No requests found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#requestTable').DataTable();
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