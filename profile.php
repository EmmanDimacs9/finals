<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Handle profile image upload
    $profile_image_path = $user['profile_image'] ?? null; // Keep existing image if no new upload
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = 'uploads/profile_images/';
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
            $new_filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                // Delete old profile image if it exists
                if ($profile_image_path && file_exists($profile_image_path)) {
                    unlink($profile_image_path);
                }
                $profile_image_path = $upload_path;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone_number)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/@g\.batstate-u\.edu\.ph$/', $email)) {
        $error = 'Email must be from @g.batstate-u.edu.ph';
    } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
        $error = 'Phone number must be exactly 11 digits starting with 09';
    } else {
        // Check if email already exists for another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email address already exists';
        } else {
            // Update basic information including profile image
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, profile_image = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $profile_image_path, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                
                // Check if profile image was uploaded
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    $message = 'Profile and image updated successfully!';
                } else {
                    $message = 'Profile updated successfully!';
                }
                
                // Update user data for display
                $user['full_name'] = $full_name;
                $user['email'] = $email;
                $user['phone_number'] = $phone_number;
                $user['profile_image'] = $profile_image_path;
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
    
    // Handle password change
    if (!empty($current_password) && !empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters long';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $new_password)) {
            $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BSU Inventory Management System</title>
    <link rel="icon" href="images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            min-height: calc(100vh - 76px);
        }
        
        .sidebar .nav-link {
            color: var(--secondary-color);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        
        .modern-profile-card {
            border: none;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .user-info-section {
            padding: 30px;
            background: white;
        }
        
        .profile-image-container {
            margin-right: 20px;
        }
        
        .profile-image-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f8f9fa;
        }
        
        .profile-image-placeholder-large {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #e9ecef;
        }
        
        .profile-image-placeholder-large i {
            font-size: 2rem;
            color: #6c757d;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 5px;
        }
        
        .user-email {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        .profile-separator {
            height: 1px;
            background-color: #e9ecef;
            margin: 0;
        }
        
        .profile-navigation {
            background: white;
        }
        
        .nav-item {
            display: block;
            text-decoration: none;
            color: #343a40;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .nav-item:last-child {
            border-bottom: none;
        }
        
        .nav-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #343a40;
        }
        
        .nav-item.active {
            background-color: #f8f9fa;
        }
        
        .nav-item-content {
            display: flex;
            align-items: center;
            padding: 20px 30px;
        }
        
        .nav-icon {
            font-size: 1.2rem;
            color: #6c757d;
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        
        .nav-text {
            flex: 1;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .nav-arrow {
            font-size: 0.9rem;
            color: #adb5bd;
        }
        
        .logout-item .nav-icon {
            color: #dc3545;
        }
        
        .logout-item:hover .nav-icon {
            color: #c82333;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
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
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="equipment.php" class="nav-link">
                                <i class="fas fa-laptop"></i> Equipment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="departments.php" class="nav-link">
                                <i class="fas fa-building"></i> Departments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="maintenance.php" class="nav-link">
                                <i class="fas fa-tools"></i> Maintenance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="tasks.php" class="nav-link">
                                <i class="fas fa-tasks"></i> Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="reports.php" class="nav-link">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="users.php" class="nav-link">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-circle"></i> My Profile</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="card modern-profile-card">
                            <!-- User Information Section -->
                            <div class="user-info-section">
                                <div class="d-flex align-items-center">
                                    <div class="profile-image-container">
                                        <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image-large">
                                        <?php else: ?>
                                            <div class="profile-image-placeholder-large">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-details">
                                        <h4 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Separator -->
                            <div class="profile-separator"></div>
                            
                            <!-- Navigation Items -->
                            <div class="profile-navigation">
                                <a href="#" class="nav-item active" data-bs-toggle="modal" data-bs-target="#profileModal">
                                    <div class="nav-item-content">
                                        <i class="fas fa-user nav-icon"></i>
                                        <span class="nav-text">My Profile</span>
                                        <i class="fas fa-chevron-right nav-arrow"></i>
                                    </div>
                                </a>
                                
                                <a href="#" class="nav-item" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                    <div class="nav-item-content">
                                        <i class="fas fa-cog nav-icon"></i>
                                        <span class="nav-text">Settings</span>
                                        <i class="fas fa-chevron-right nav-arrow"></i>
                                    </div>
                                </a>
                                
                                <a href="logout.php" class="nav-item logout-item">
                                    <div class="nav-item-content">
                                        <i class="fas fa-sign-out-alt nav-icon"></i>
                                        <span class="nav-text">Log Out</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="fas fa-user-circle"></i> Edit Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="mb-3">Profile Image</h6>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="rounded-circle profile-image-large">
                                        <?php else: ?>
                                            <div class="profile-image-placeholder-large">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <label class="form-label">Upload New Image</label>
                                        <input type="file" class="form-control" name="profile_image" accept="image/*" id="profileImageInput" onchange="previewImage(this)">
                                        <small class="text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                                        <div id="imagePreview" class="mt-2" style="display: none;">
                                            <small class="text-success">Preview:</small>
                                            <img id="previewImg" src="" alt="Preview" class="rounded-circle profile-image-large mt-1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="username@g.batstate-u.edu.ph" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" placeholder="09123456789" maxlength="11" pattern="^09\d{9}$" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h6 class="mb-3">Change Password</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" placeholder="Enter current password">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" placeholder="Enter strong password" minlength="8">
                                <small class="text-muted">Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm new password" minlength="8">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="profileForm" class="btn btn-primary" id="updateBtn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="settingsModalLabel">
                        <i class="fas fa-cog"></i> Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">User ID</label>
                            <input type="text" class="form-control" value="<?php echo $user['id']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Updated</label>
                            <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['updated_at'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Security Tips</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> Use a strong password
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> Never share your credentials
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> Logout when done
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i> Keep your information updated
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        // Show success message for image upload
        <?php if ($message && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Scroll to top to show success message
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        <?php endif; ?>
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Handle form submission with loading state
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const updateBtn = document.getElementById('updateBtn');
            const originalText = updateBtn.innerHTML;
            
            // Show loading state
            updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            updateBtn.disabled = true;
            
            // Re-enable button after 10 seconds (in case of slow response)
            setTimeout(function() {
                updateBtn.innerHTML = originalText;
                updateBtn.disabled = false;
            }, 10000);
        });
        
        // Form validation
        const emailInput = document.querySelector('input[name="email"]');
        const phoneInput = document.querySelector('input[name="phone_number"]');
        const newPasswordInput = document.querySelector('input[name="new_password"]');
        const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
        
        // Email validation
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
            
            emailInput.addEventListener('input', function() {
                clearError(this);
            });
        }
        
        // Phone number validation
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                validatePhone(this);
            });
            
            phoneInput.addEventListener('input', function() {
                // Only allow numbers and limit to 11 digits
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11);
                clearError(this);
            });
        }
        
        // Password validation
        if (newPasswordInput) {
            newPasswordInput.addEventListener('blur', function() {
                validatePassword(this);
            });
            
            newPasswordInput.addEventListener('input', function() {
                clearError(this);
                updatePasswordStrength(this);
            });
        }
        
        // Confirm password validation
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('blur', function() {
                validateConfirmPassword(this);
            });
            
            confirmPasswordInput.addEventListener('input', function() {
                clearError(this);
            });
        }
        
        // Form submission validation
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (!validateEmail(emailInput)) isValid = false;
                if (!validatePhone(phoneInput)) isValid = false;
                if (newPasswordInput.value && !validatePassword(newPasswordInput)) isValid = false;
                if (confirmPasswordInput.value && !validateConfirmPassword(confirmPasswordInput)) isValid = false;
                
                if (!isValid) {
                    e.preventDefault();
                    showError('Please fix the validation errors before submitting');
                }
            });
        }
        
        function validateEmail(input) {
            const email = input.value.trim();
            const emailRegex = /^[^\s@]+@g\.batstate-u\.edu\.ph$/;
            
            if (email && !emailRegex.test(email)) {
                showFieldError(input, 'Email must be from @g.batstate-u.edu.ph');
                return false;
            } else {
                clearError(input);
                return true;
            }
        }
        
        function validatePhone(input) {
            const phone = input.value.trim();
            const phoneRegex = /^09\d{9}$/;
            
            if (phone && !phoneRegex.test(phone)) {
                showFieldError(input, 'Phone number must be exactly 11 digits long, starting with 09');
                return false;
            } else {
                clearError(input);
                return true;
            }
        }
        
        function validatePassword(input) {
            const password = input.value;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/;
            
            if (password && !passwordRegex.test(password)) {
                showFieldError(input, 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character');
                return false;
            } else {
                clearError(input);
                return true;
            }
        }
        
        function validateConfirmPassword(input) {
            const password = newPasswordInput.value;
            const confirmPassword = input.value;
            
            if (confirmPassword && password !== confirmPassword) {
                showFieldError(input, 'Passwords do not match');
                return false;
            } else {
                clearError(input);
                return true;
            }
        }
        
        function updatePasswordStrength(input) {
            const password = input.value;
            const strengthIndicator = document.getElementById('password-strength') || createStrengthIndicator(input);
            
            let strength = 0;
            let strengthText = '';
            let strengthColor = '';
            
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Very Weak';
                    strengthColor = '#dc3545';
                    break;
                case 2:
                    strengthText = 'Weak';
                    strengthColor = '#fd7e14';
                    break;
                case 3:
                    strengthText = 'Medium';
                    strengthColor = '#ffc107';
                    break;
                case 4:
                    strengthText = 'Strong';
                    strengthColor = '#28a745';
                    break;
            }
            
            strengthIndicator.textContent = `Password Strength: ${strengthText}`;
            strengthIndicator.style.color = strengthColor;
        }
        
        function createStrengthIndicator(input) {
            const strengthDiv = document.createElement('div');
            strengthDiv.id = 'password-strength';
            strengthDiv.style.fontSize = '12px';
            strengthDiv.style.marginTop = '5px';
            strengthDiv.style.fontWeight = '500';
            input.parentNode.appendChild(strengthDiv);
            return strengthDiv;
        }
        
        function showFieldError(input, message) {
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            
            // Remove existing error message
            const existingError = input.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
            
            input.parentNode.appendChild(errorDiv);
        }
        
        function clearError(input) {
            input.style.borderColor = '';
            input.style.boxShadow = '';
            
            const errorDiv = input.parentNode.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        function showError(message) {
            // Remove existing error messages
            const existingError = document.querySelector('.alert-danger');
            if (existingError) {
                existingError.remove();
            }
            
            // Create new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
            
            // Insert error message at the top of the modal body
            const modalBody = document.querySelector('.modal-body');
            modalBody.insertBefore(errorDiv, modalBody.firstChild);
        }
        
        // Handle modal close and refresh page on successful update
        document.getElementById('profileModal').addEventListener('hidden.bs.modal', function() {
            // Check if there was a successful update (you can add a flag in PHP)
            <?php if ($message): ?>
                // Refresh the page to show updated profile
                setTimeout(function() {
                    window.location.reload();
                }, 100);
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