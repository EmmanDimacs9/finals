<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $full_name = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $phone_number = trim($_POST['phone_number']);
                
                // Validation
                if (empty($full_name) || empty($email) || empty($phone_number)) {
                    $error = 'Please fill in all required fields.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                } elseif (!preg_match('/@g\.batstate-u\.edu\.ph$/', $email)) {
                    $error = 'Email must be from @g.batstate-u.edu.ph';
                } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
                    $error = 'Phone number must be exactly 11 digits starting with 09';
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $stmt->bind_param("si", $email, $user_id);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        $error = 'Email address already exists.';
                    } else {
                        // Handle profile image upload
                        $profile_image_path = null; // Initialize as null
                        
                        // Get current profile image from database first
                        $current_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
                        $current_stmt->bind_param("i", $user_id);
                        $current_stmt->execute();
                        $current_result = $current_stmt->get_result();
                        if ($current_result->num_rows > 0) {
                            $current_user = $current_result->fetch_assoc();
                            $profile_image_path = $current_user['profile_image'];
                        }
                        $current_stmt->close();
                        
                        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                            $upload_dir = '../uploads/profiles/';
                            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                            $max_size = 2 * 1024 * 1024; // 2MB
                            
                            $file_info = $_FILES['profile_image'];
                            
                            // Validate file type
                            if (!in_array($file_info['type'], $allowed_types)) {
                                $error = 'Please upload a valid image file (JPEG, PNG, or GIF)';
                            }
                            // Validate file size
                            elseif ($file_info['size'] > $max_size) {
                                $error = 'Image size must be less than 2MB';
                            }
                            // Validate file extension
                            elseif (!in_array(strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
                                $error = 'Invalid file extension. Please upload a valid image file.';
                            }
                            else {
                                // Generate unique filename
                                $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
                                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                                $upload_path = $upload_dir . $new_filename;
                                
                                // Create directory if it doesn't exist
                                if (!file_exists($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                
                                // Move uploaded file
                                if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                                    // Delete old profile image if it exists
                                    if ($profile_image_path && file_exists($upload_dir . $profile_image_path)) {
                                        unlink($upload_dir . $profile_image_path);
                                    }
                                    $profile_image_path = $new_filename;
                                } else {
                                    $error = 'Failed to upload image. Please try again.';
                                }
                            }
                        }
                        
                        if (empty($error)) {
                            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, profile_image = ? WHERE id = ?");
                            $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $profile_image_path, $user_id);
                            
                            if ($stmt->execute()) {
                                $_SESSION['user_name'] = $full_name;
                                $_SESSION['user_email'] = $email;
                                $_SESSION['profile_image'] = $profile_image_path; // Always update session, even if null
                                
                                // Check if profile image was uploaded
                                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                                    $success = 'Profile and image updated successfully!';
                                    // Refresh the page to update profile images in dropdown
                                    echo '<script>setTimeout(function(){ window.location.reload(); }, 1500);</script>';
                                } else {
                                    $success = 'Profile updated successfully!';
                                }
                            } else {
                                $error = 'Failed to update profile.';
                            }
                        }
                    }
                }
                $stmt->close();
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'Password must be at least 8 characters long.';
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $new_password)) {
                    $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character.';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'Failed to change password.';
                    }
                }
                $stmt->close();
                break;
        }
    }
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
// Get user statistics
$stats_query = "
    SELECT 
        ( 
            SELECT COUNT(*) FROM (
                SELECT assigned_person FROM desktop WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM laptops WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM printers WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM accesspoint WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM `switch` WHERE assigned_person = ?
                UNION ALL
                SELECT assigned_person FROM telephone WHERE assigned_person = ?
            ) AS eq
        ) AS equipment_count,
        (SELECT COUNT(*) FROM tasks WHERE assigned_to = ?) AS task_count,
        (SELECT COUNT(*) FROM history WHERE user_id = ?) AS maintenance_count
";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("ssssssii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();


$page_title = 'Profile';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-user-circle"></i> My Profile</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Statistics -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> My Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="stat-item">
                                        <i class="fas fa-desktop fa-2x text-primary mb-2"></i>
                                        <h4 class="mb-1"><?php echo $stats['equipment_count']; ?></h4>
                                        <small class="text-muted">Equipment Assigned</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-item">
                                        <i class="fas fa-tasks fa-2x text-info mb-2"></i>
                                        <h4 class="mb-1"><?php echo $stats['task_count']; ?></h4>
                                        <small class="text-muted">Tasks Assigned</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-item">
                                        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                                        <h4 class="mb-1"><?php echo $stats['maintenance_count']; ?></h4>
                                        <small class="text-muted">Maintenance Records</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-item">
                                        <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                                        <h4 class="mb-1"><?php echo date('M Y'); ?></h4>
                                        <small class="text-muted">Current Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Quick Actions -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="kanban.php" class="btn btn-outline-primary">
                                    <i class="fas fa-tasks"></i> View All Tasks
                                </a>
                                <a href="mytasks.php" class="btn btn-outline-info" style="display:none">
                                    <i class="fas fa-list"></i> My Assigned Tasks
                                </a>
                                <a href="qr.php" class="btn btn-outline-success">
                                    <i class="fas fa-qrcode"></i> Scan QR Code
                                </a>
                                <a href="history.php" class="btn btn-outline-warning">
                                    <i class="fas fa-history"></i> Equipment History
                                </a>
                                <a href="logout.php" class="btn btn-outline-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user"></i> Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="editProfileForm">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <!-- Profile Image Section -->
                    <div class="profile-image-section mb-4">
                        <h6 class="section-title">Profile Image</h6>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="profile-image-preview">
                                    <img id="profileImagePreview" 
                                         src="<?php echo !empty($user['profile_image']) ? '../uploads/profiles/' . $user['profile_image'] : 'https://via.placeholder.com/100x100/6c757d/ffffff?text=' . substr($user['full_name'], 0, 1); ?>" 
                                         alt="Profile Picture" class="rounded-circle">
                                </div>
                            </div>
                            <div class="col">
                                <div class="upload-section">
                                    <label class="form-label">Upload New Image</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="profile_image" id="profileImageInput" 
                                               accept="image/jpeg,image/png,image/gif" onchange="previewImage(this)">
                                        <label class="input-group-text" for="profileImageInput">Choose File</label>
                                    </div>
                                    <div class="form-text">Max size: 2MB. Supported formats: JPG, PNG, GIF</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="section-divider">

                    <!-- Personal Information Section -->
                    <div class="personal-info-section mb-4">
                        <h6 class="section-title">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       placeholder="username@g.batstate-u.edu.ph" required>
                                <div class="form-text">Must be from @g.batstate-u.edu.ph</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone_number" 
                                       value="<?php echo htmlspecialchars($user['phone_number']); ?>" 
                                       placeholder="09123456789" maxlength="11" pattern="^09\d{9}$" required>
                                <div class="form-text">Must be exactly 11 digits starting with 09</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-lock"></i> Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="changePasswordForm">
                    <input type="hidden" name="action" value="change_password">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                            <div class="form-text">Must be at least 8 characters with uppercase, lowercase, number, and special character</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.stat-item {
    padding: 15px;
    border-radius: 8px;
    background-color: #f8f9fa;
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-2px);
}

.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-control:read-only {
    background-color: #f8f9fa;
}

.form-text {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Modal Styling */
.section-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    font-size: 1rem;
}

.section-divider {
    margin: 25px 0;
    border-color: #e9ecef;
}

.profile-image-preview img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border: 3px solid #e9ecef;
}

.upload-section .form-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 5px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    cursor: pointer;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #000;
}

/* Change Password Modal Styling */
#changePasswordModal .modal-body {
    padding: 30px;
}

#changePasswordModal .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

#changePasswordModal .form-control {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 12px 15px;
    font-size: 1rem;
}

#changePasswordModal .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#changePasswordModal .btn-warning {
    padding: 12px 24px;
    font-weight: 600;
    border-radius: 6px;
}

#changePasswordModal .modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 20px 30px;
    background-color: #f8f9fa;
}
</style>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, or GIF)');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.querySelector('input[name="email"]');
    const phoneInput = document.querySelector('input[name="phone_number"]');
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    
    // Email validation
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value;
            const isValid = email.endsWith('@g.batstate-u.edu.ph');
            this.setCustomValidity(isValid ? '' : 'Email must be from @g.batstate-u.edu.ph');
        });
    }
    
    // Phone number validation
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const phone = this.value;
            const isValid = /^09\d{9}$/.test(phone);
            this.setCustomValidity(isValid ? '' : 'Phone number must be exactly 11 digits starting with 09');
        });
    }
    
    // Password validation
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
            const isValid = password.length >= 8 && hasUpper && hasLower && hasNumber && hasSpecial;
            this.setCustomValidity(isValid ? '' : 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character');
        });
    }
    
    // Change Password Modal validation
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        const currentPasswordInput = changePasswordForm.querySelector('input[name="current_password"]');
        const newPasswordInputModal = changePasswordForm.querySelector('input[name="new_password"]');
        const confirmPasswordInput = changePasswordForm.querySelector('input[name="confirm_password"]');
        
        // New password validation in modal
        if (newPasswordInputModal) {
            newPasswordInputModal.addEventListener('input', function() {
                const password = this.value;
                const hasUpper = /[A-Z]/.test(password);
                const hasLower = /[a-z]/.test(password);
                const hasNumber = /\d/.test(password);
                const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                const isValid = password.length >= 8 && hasUpper && hasLower && hasNumber && hasSpecial;
                this.setCustomValidity(isValid ? '' : 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character');
            });
        }
        
        // Confirm password validation
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = newPasswordInputModal.value;
                const confirmPassword = this.value;
                const isValid = password === confirmPassword;
                this.setCustomValidity(isValid ? '' : 'Passwords do not match');
            });
        }
    }
});
</script>

<?php require_once 'footer.php'; ?> 