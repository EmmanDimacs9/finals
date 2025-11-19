<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$error = '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
        $toDelete = (int)$_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'technician'");
        $stmt->bind_param("i", $toDelete);
        if ($stmt->execute()) {
            include '../logger.php';
            logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Delete Technician', 'Deleted technician ID ' . $toDelete);
            $success = 'Technician deleted successfully!';
        } else {
            $error = 'Failed to delete technician.';
        }
        $stmt->close();
    } else {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = 'technician'; // Only technicians can be created
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
                    logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Add Technician', 'Added technician ' . $email);
                    $stmt->close();
                    // Redirect to prevent form resubmission
                    header('Location: users.php?success=Technician account registered successfully!');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
            $stmt->close();
        }
    }
}

// Fetch technicians
$result = $conn->query("SELECT id, full_name, email, role, phone_number, created_at FROM users WHERE role = 'technician' ORDER BY created_at DESC");
$technicians = $result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Account Management';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-user-shield text-danger"></i> ICT Account Management
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Create and manage ICT staff accounts
                    </p>
                </div>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addTechnicianModal">
                    <i class="fas fa-user-plus"></i> Add Technician
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

            <!-- Technicians Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-users"></i> Registered Technicians
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
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
                                <?php if (empty($technicians)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No technicians registered yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($technicians as $tech): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tech['full_name']); ?></td>
                                            <td><?= htmlspecialchars($tech['email']); ?></td>
                                            <td><span class="badge bg-danger"><?= htmlspecialchars($tech['role']); ?></span></td>
                                            <td><?= htmlspecialchars($tech['phone_number']); ?></td>
                                            <td><?= date('M d, Y', strtotime($tech['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this technician?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= (int)$tech['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
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
</div>

<!-- Add Technician Modal -->
<div class="modal fade" id="addTechnicianModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Register New Technician</h5>
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
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="Technician" readonly>
                            <input type="hidden" name="role" value="technician">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="form-text text-muted">Must contain uppercase, lowercase, number, and special character</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

<style>
.card {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: none;
}

.card-header {
    border-radius: 15px 15px 0 0;
    font-weight: 600;
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-header {
    border-radius: 15px 15px 0 0;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>

<?php require_once 'footer.php'; ?>


