<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit();
}

// ✅ Ensure all admin roles exist in role enum
$checkEnumQuery = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'role'";
$result = $conn->query($checkEnumQuery);
if ($result && $row = $result->fetch_assoc()) {
    $enumStr = $row['COLUMN_TYPE'];
    // Check if all roles exist
    $hasAllRoles = strpos($enumStr, "'depadmin'") !== false || strpos($enumStr, "'department_admin'") !== false;
    if (!$hasAllRoles || strpos($enumStr, "'admin'") === false) {
        $conn->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin','technician','department_admin','depadmin') NOT NULL DEFAULT 'technician'");
    }
}

$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete admin
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $toDelete = (int)$_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role IN ('admin', 'depadmin', 'department_admin')");
        $stmt->bind_param("i", $toDelete);
        if ($stmt->execute()) {
            include '../logger.php';
            logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Delete Admin', 'Deleted admin ID ' . $toDelete);
            $success = 'Admin deleted successfully';
        } else {
            $error = 'Failed to delete admin';
        }
        $stmt->close();
    } else {
        // Add admin
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $phone_number = trim($_POST['phone_number']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($full_name) || empty($email) || empty($phone_number) || empty($password)) {
            $error = 'Please fill in all fields';
        } elseif (!in_array($role, ['admin', 'depadmin', 'department_admin'])) {
            $error = 'Invalid role selected';
        } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
            $error = 'Phone number must be exactly 11 digits starting with 09';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $password)) {
            $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = 'Email address already exists';
                $stmt->close();
            } else {
                $stmt->close(); // Close the SELECT statement
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, phone_number, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $full_name, $email, $role, $phone_number, $hashed_password);

                if ($stmt->execute()) {
                    include '../logger.php';
                    logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Add Admin', 'Added admin ' . $email);
                    $stmt->close();
                    // Redirect to prevent form resubmission
                    header('Location: admin_accounts.php?success=Account registered successfully!');
                    exit();
                } else {
                    $error = 'Registration failed: ' . $conn->error . '. Please try again.';
                    $stmt->close();
                }
            }
        }
    }
}

// Fetch admins
$result = $conn->query("SELECT id, full_name, email, role, phone_number, created_at FROM users WHERE role IN ('admin', 'depadmin', 'department_admin') ORDER BY created_at DESC");
$admins = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Accounts - BSU Inventory Management System</title>
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
            <div class="col-md-9 col-lg-10 main-content">
                <h2><i class="fas fa-user-shield"></i> Admin Accounts</h2>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Add Button -->
                <div class="mb-3 text-end">
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-user-plus"></i> Add Admin
                    </button>
                </div>

                <!-- Admin Table -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-users"></i> Registered Admins
                    </div>
                    <div class="card-body">
                        <table id="adminsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Phone</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['full_name']); ?></td>
                                        <td><?= htmlspecialchars($a['email']); ?></td>
                                        <td><?= htmlspecialchars($a['role']); ?></td>
                                        <td><?= htmlspecialchars($a['phone_number']); ?></td>
                                        <td><?= htmlspecialchars($a['created_at']); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this admin?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= (int)$a['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Register New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success; ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" required maxlength="11" pattern="^09\d{9}$" placeholder="09123456789">
                                <small class="text-muted">Must be exactly 11 digits starting with 09</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role" class="form-control" required>
                                    <option value="admin">Admin</option>
                                    <option value="department_admin">Department Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-user-plus"></i> Register</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#adminsTable').DataTable({
                searching: true,
                paging: true,
                info: false,
                ordering: true
            });

            // Auto-close modal if there's a success message
            <?php if ($success): ?>
                $('#addAdminModal').modal('hide');
            <?php endif; ?>
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
