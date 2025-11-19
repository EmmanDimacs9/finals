<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

// Fetch all user accounts (all roles)
$usersQuery = "SELECT id, full_name, email, role, phone_number, created_at FROM users ORDER BY created_at DESC";
$usersResult = $conn->query($usersQuery);

if (!$usersResult) {
    die('❌ Query Error: ' . $conn->error);
}

// Fetch counts per role for quick stats
$roleCountsQuery = "SELECT role, COUNT(*) as total FROM users GROUP BY role";
$roleCountsResult = $conn->query($roleCountsQuery);
$roleCounts = [
    'admin' => 0,
    'technician' => 0,
    'department_admin' => 0,
    'depadmin' => 0
];
$totalAccounts = 0;

if ($roleCountsResult) {
    while ($row = $roleCountsResult->fetch_assoc()) {
        $role = $row['role'];
        $roleCounts[$role] = (int)$row['total'];
        $totalAccounts += (int)$row['total'];
    }
} else {
    $totalAccounts = $usersResult->num_rows;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Accounts - BSU Inventory Management System</title>
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
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .badge-status { font-size: 0.85rem; }
    </style>
</head>
<body>
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
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 p-4">
                <h2><i class="fas fa-users"></i> All User Accounts</h2>

                <!-- Stats -->
                <div class="row mt-3">
                    <div class="col-md-3 mb-3">
                        <div class="card text-center p-3">
                            <h4><?= $totalAccounts ?></h4>
                            <p class="text-muted mb-0">Total Accounts</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center p-3">
                            <h4><?= $roleCounts['admin'] ?></h4>
                            <p class="text-muted mb-0">Admins</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center p-3">
                            <h4><?= $roleCounts['technician'] ?></h4>
                            <p class="text-muted mb-0">Technicians</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center p-3">
                            <h4><?= $roleCounts['department_admin'] + $roleCounts['depadmin'] ?></h4>
                            <p class="text-muted mb-0">Department Admins</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h5 class="mb-0">Accounts Table</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label for="roleFilter" class="mb-0"><strong>Filter by Role:</strong></label>
                        <select id="roleFilter" class="form-select" style="width: 220px;">
                            <option value="">All Roles</option>
                            <option value="Admin">Admin</option>
                            <option value="Technician">Technician</option>
                            <option value="Department Admin">Department Admin</option>
                            <option value="Depadmin">Depadmin</option>
                        </select>
                    </div>
                </div>

                <div class="card p-4 mt-3">
                    <?php if ($usersResult->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="accountCreationTable" class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Phone</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $usersResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars(ucwords(str_replace('_',' ', $row['role']))) ?></span></td>
                                            <td><?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-pen"></i> Manage
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No user accounts found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#accountCreationTable').DataTable();

            $('#roleFilter').on('change', function () {
                const value = this.value;
                if (!value) {
                    table.column(3).search('').draw();
                } else {
                    const regex = $.fn.dataTable.util.escapeRegex(value);
                    table.column(3).search(regex, true, false).draw();
                }
            });
        });
    </script>
</body>
</html>

