<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

$message = '';
$error = '';

// Handle Add Account form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_account') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($role) || empty($password)) {
        $error = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
        $error = "Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@$!%*?&).";
    } else {
        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $emailResult = $checkEmailStmt->get_result();
        
        if ($emailResult->num_rows > 0) {
            $error = "Email address already exists.";
        } else {
            // Hash password and insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, role, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $insertStmt->bind_param("sssss", $full_name, $email, $phone_number, $role, $hashed_password);
            
            if ($insertStmt->execute()) {
                $message = "Account created successfully for " . htmlspecialchars($full_name) . "!";
                
                // Log the action
                if (function_exists('addLog')) {
                    addLog($conn, $_SESSION['user_id'], "Created new user account: $email ($role)");
                }
            } else {
                $error = "Failed to create account: " . $conn->error;
            }
            $insertStmt->close();
        }
        $checkEmailStmt->close();
    }
}

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

                <!-- Success/Error Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h5 class="mb-0">Accounts Table</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                            <i class="fas fa-user-plus"></i> Add Account
                        </button>
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

    <!-- Add Account Modal -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addAccountForm">
                    <input type="hidden" name="action" value="add_account">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountModalLabel">
                            <i class="fas fa-user-plus"></i> Add New Account
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="technician">Technician</option>
                                    <option value="department_admin">Department Admin</option>
                                </select>
                                <div class="form-text">
                                    <small><strong>Technician:</strong> Can manage equipment and maintenance tasks</small><br>
                                    <small><strong>Department Admin:</strong> Can manage department equipment and submit requests</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <div class="form-text">
                                    <small>Password must contain:</small><br>
                                    <small>• At least 8 characters</small><br>
                                    <small>• 1 uppercase letter (A-Z)</small><br>
                                    <small>• 1 lowercase letter (a-z)</small><br>
                                    <small>• 1 number (0-9)</small><br>
                                    <small>• 1 special character (@$!%*?&)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                <div id="password-strength" class="form-text mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> The new user will receive their login credentials and can change their password after first login.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </div>
                </form>
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

            // Password strength validation function
            function validatePassword(password) {
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /\d/.test(password),
                    special: /[@$!%*?&]/.test(password)
                };
                
                return requirements;
            }

            // Real-time password strength indicator
            $('#password').on('keyup', function() {
                const password = $(this).val();
                const requirements = validatePassword(password);
                const strengthDiv = $('#password-strength');
                
                let strengthHtml = '<div class="password-requirements">';
                strengthHtml += `<small class="${requirements.length ? 'text-success' : 'text-danger'}">✓ At least 8 characters</small><br>`;
                strengthHtml += `<small class="${requirements.uppercase ? 'text-success' : 'text-danger'}">✓ 1 uppercase letter</small><br>`;
                strengthHtml += `<small class="${requirements.lowercase ? 'text-success' : 'text-danger'}">✓ 1 lowercase letter</small><br>`;
                strengthHtml += `<small class="${requirements.number ? 'text-success' : 'text-danger'}">✓ 1 number</small><br>`;
                strengthHtml += `<small class="${requirements.special ? 'text-success' : 'text-danger'}">✓ 1 special character</small>`;
                strengthHtml += '</div>';
                
                strengthDiv.html(strengthHtml);
                
                // Update input validation state
                const allValid = Object.values(requirements).every(req => req);
                if (password.length > 0) {
                    if (allValid) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                    }
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });

            // Form validation
            $('#addAccountForm').on('submit', function(e) {
                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();
                const requirements = validatePassword(password);
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long!');
                    return false;
                }
                
                const allValid = Object.values(requirements).every(req => req);
                if (!allValid) {
                    e.preventDefault();
                    alert('Password must contain at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@$!%*?&)!');
                    return false;
                }
            });

            // Real-time password confirmation check
            $('#confirm_password').on('keyup', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (confirmPassword && password !== confirmPassword) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Clear form when modal is closed
            $('#addAccountModal').on('hidden.bs.modal', function () {
                $('#addAccountForm')[0].reset();
                $('#confirm_password').removeClass('is-invalid is-valid');
                $('#password').removeClass('is-invalid is-valid');
                $('#password-strength').html('');
            });
        });
    </script>
</body>
</html>

