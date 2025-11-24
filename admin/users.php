<?php
require_once "../includes/session.php";
require_once "../includes/db.php";

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit();
}

$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Get role filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $toDelete = (int)$_POST['user_id'];
        // Get user info before deletion for logging
        $userInfoStmt = $conn->prepare("SELECT full_name, email, role FROM users WHERE id = ?");
        $userInfoStmt->bind_param("i", $toDelete);
        $userInfoStmt->execute();
        $userInfo = $userInfoStmt->get_result()->fetch_assoc();
        $userInfoStmt->close();
        
        // Prevent deleting yourself
        if ($toDelete == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $toDelete);
            if ($stmt->execute()) {
                include '../logger.php';
                logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Delete User', 'Deleted user: ' . ($userInfo['email'] ?? 'ID ' . $toDelete) . ' (' . ($userInfo['role'] ?? 'unknown') . ')');
                $success = 'User deleted successfully!';
            } else {
                $error = 'Failed to delete user.';
            }
            $stmt->close();
        }
    } else {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $phone_number = trim($_POST['phone_number']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($full_name) || empty($email) || empty($phone_number) || empty($password)) {
            $error = 'Please fill in all fields';
        } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
            $error = 'Phone number must be exactly 11 digits starting with 09';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{6,}$/', $password)) {
            $error = 'Password must contain at least one uppercase, one lowercase, one digit, and one special character';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = 'Email address already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, phone_number, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $full_name, $email, $role, $phone_number, $hashed_password);

                if ($stmt->execute()) {
                    include '../logger.php';
                    $roleDisplay = ucwords(str_replace('_', ' ', $role));
                    logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Add User', 'Added ' . $roleDisplay . ': ' . $email);
                    header('Location: users.php?success=' . urlencode($roleDisplay . ' account registered successfully!'));
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
            $stmt->close();
        }
    }
}

// Build query with role filter
if ($role_filter === 'all') {
    $query = "SELECT id, full_name, email, role, phone_number, created_at FROM users ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT id, full_name, email, role, phone_number, created_at FROM users WHERE role = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $role_filter);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get counts for each role
$countsQuery = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$countsResult = $conn->query($countsQuery);
$roleCounts = ['admin' => 0, 'technician' => 0, 'department_admin' => 0, 'depadmin' => 0];
while ($row = $countsResult->fetch_assoc()) {
    $roleCounts[$row['role']] = (int)$row['count'];
}
$totalUsers = array_sum($roleCounts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Accounts - BSU Inventory Management System</title>
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
                        <h2 class="mb-2"><i class="fas fa-user-shield"></i> User Accounts</h2>
                        <p class="text-muted mb-0">Manage all registered user accounts</p>
                    </div>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Role Filter and Statistics -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <label class="form-label fw-bold mb-2">Filter by Role</label>
                                <div class="btn-group w-100" role="group">
                                    <a href="?role=all" class="btn <?= $role_filter === 'all' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                        All (<?= $totalUsers ?>)
                                    </a>
                                    <a href="?role=admin" class="btn <?= $role_filter === 'admin' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                        Admin (<?= $roleCounts['admin'] ?>)
                                    </a>
                                    <a href="?role=technician" class="btn <?= $role_filter === 'technician' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                        Technician (<?= $roleCounts['technician'] ?>)
                                    </a>
                                    <a href="?role=department_admin" class="btn <?= $role_filter === 'department_admin' ? 'btn-danger' : 'btn-outline-danger' ?>">
                                        Department Admin (<?= $roleCounts['department_admin'] + $roleCounts['depadmin'] ?>)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <i class="fas fa-users"></i> Registered Users 
                        <?php if ($role_filter !== 'all'): ?>
                            <span class="badge bg-light text-dark ms-2"><?= ucwords(str_replace('_', ' ', $role_filter)) ?> only</span>
                        <?php endif; ?>
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
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle"></i> No users found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['full_name']); ?></td>
                                            <td><?= htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php
                                                $roleDisplay = ucwords(str_replace('_', ' ', $user['role']));
                                                $badgeClass = 'bg-secondary';
                                                if ($user['role'] === 'admin') $badgeClass = 'bg-danger';
                                                elseif ($user['role'] === 'technician') $badgeClass = 'bg-primary';
                                                elseif ($user['role'] === 'department_admin' || $user['role'] === 'depadmin') $badgeClass = 'bg-success';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($roleDisplay); ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="user_id" value="<?= (int)$user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">Current User</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Register New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="username@g.batstate-u.edu.ph" required>
                                <small class="form-text text-muted">Must be from @g.batstate-u.edu.ph</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control" 
                                       placeholder="09123456789" maxlength="11" required>
                                <small class="form-text text-muted">Must be exactly 11 digits starting with 09</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="">Select Role...</option>
                                    <option value="admin">Admin</option>
                                    <option value="technician">Technician</option>
                                    <option value="department_admin">Department Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="passwordInput" name="password" class="form-control" required minlength="6">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#passwordInput" aria-label="Show password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Must contain uppercase, lowercase, number, and special character</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="confirmPasswordInput" name="confirm_password" class="form-control" required minlength="6">
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#confirmPasswordInput" aria-label="Show password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
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
            
            // Email validation
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    const email = this.value;
                    const isValid = email.endsWith('@g.batstate-u.edu.ph');
                    this.setCustomValidity(isValid ? '' : 'Email must be from @g.batstate-u.edu.ph');
                });
            }
            
            // Phone number validation
            const phoneInput = document.querySelector('input[name="phone_number"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    const phone = this.value;
                    const isValid = /^09\d{9}$/.test(phone);
                    this.setCustomValidity(isValid ? '' : 'Phone number must be exactly 11 digits starting with 09');
                });
            }
            
            // Password validation
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const hasUpper = /[A-Z]/.test(password);
                    const hasLower = /[a-z]/.test(password);
                    const hasNumber = /\d/.test(password);
                    const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password);
                    const isValid = password.length >= 6 && hasUpper && hasLower && hasNumber && hasSpecial;
                    this.setCustomValidity(isValid ? '' : 'Password must contain at least one uppercase, one lowercase, one number, and one special character');
                });
            }
            
            // Confirm password validation
            const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
            if (confirmPasswordInput && passwordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    const password = passwordInput.value;
                    const confirmPassword = this.value;
                    const isValid = password === confirmPassword;
                    this.setCustomValidity(isValid ? '' : 'Passwords do not match');
                });
            }

            // Password visibility toggles
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetInput = document.querySelector(this.dataset.target);
                    if (!targetInput) return;
                    const isHidden = targetInput.type === 'password';
                    targetInput.type = isHidden ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye', !isHidden);
                        icon.classList.toggle('fa-eye-slash', isHidden);
                    }
                    this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                });
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
