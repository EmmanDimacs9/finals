<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');

            if (empty($full_name) || empty($email) || empty($phone_number)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (!preg_match('/@g\.batstate-u\.edu\.ph$/', $email)) {
                $error = 'Email must be from @g.batstate-u.edu.ph';
            } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
                $error = 'Phone number must be exactly 11 digits starting with 09';
            } else {
                $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check->bind_param("si", $email, $user_id);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $error = 'Email address already exists.';
                } else {
                    $check->close();
                    $profile_image_path = null;
                    $current_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
                    $current_stmt->bind_param("i", $user_id);
                    $current_stmt->execute();
                    $cr = $current_stmt->get_result();
                    if ($cr->num_rows) {
                        $profile_image_path = $cr->fetch_assoc()['profile_image'];
                    }
                    $current_stmt->close();

                    $upload_dir = '../uploads/profiles/';
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
                        $fi = $_FILES['profile_image'];
                        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!in_array($fi['type'], $allowed)) {
                            $error = 'Please upload a valid image (JPEG, PNG, or GIF).';
                        } elseif ($fi['size'] > 2 * 1024 * 1024) {
                            $error = 'Image size must be less than 2MB.';
                        } else {
                            $ext = strtolower(pathinfo($fi['name'], PATHINFO_EXTENSION));
                            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $error = 'Invalid file extension.';
                            } else {
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                $new_name = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                                if (move_uploaded_file($fi['tmp_name'], $upload_dir . $new_name)) {
                                    if ($profile_image_path && file_exists($upload_dir . $profile_image_path)) {
                                        @unlink($upload_dir . $profile_image_path);
                                    }
                                    $profile_image_path = $new_name;
                                } else {
                                    $error = 'Failed to upload image.';
                                }
                            }
                        }
                    }

                    if (empty($error)) {
                        $profile_image_path = $profile_image_path ?? '';
                        $upd = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ?, profile_image = ? WHERE id = ?");
                        $upd->bind_param("ssssi", $full_name, $email, $phone_number, $profile_image_path, $user_id);
                        if ($upd->execute()) {
                            $_SESSION['user_name'] = $full_name;
                            $_SESSION['user_email'] = $email;
                            $_SESSION['profile_image'] = $profile_image_path ?: null;
                            $success = 'Profile updated successfully.';
                            require_once '../logger.php';
                            logAdminAction($user_id, $full_name, 'Profile Update', 'Admin updated profile');
                        } else {
                            $error = 'Failed to update profile.';
                        }
                        $upd->close();
                    }
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!password_verify($current, $row['password'])) {
                $error = 'Current password is incorrect.';
            } elseif ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } elseif (strlen($new) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $new)) {
                $error = 'Password must include uppercase, lowercase, digit, and special character.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $user_id);
                if ($stmt->execute()) {
                    $success = 'Password changed successfully.';
                    require_once '../logger.php';
                    logAdminAction($user_id, $_SESSION['user_name'], 'Password Change', 'Admin changed password');
                } else {
                    $error = 'Failed to change password.';
                }
                $stmt->close();
            }
        }
    }
}

$stmt = $conn->prepare("SELECT id, full_name, email, phone_number, role, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: dashboard.php');
    exit();
}

$avatarUrl = !empty($user['profile_image'])
    ? '../uploads/profiles/' . $user['profile_image']
    : 'https://via.placeholder.com/120x120/6c757d/ffffff?text=' . strtoupper(substr($user['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BSU Inventory System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .sidebar .nav-link { color: var(--secondary-color); margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: #fff; }
        .main-content { padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e9ecef; }
        .section-title { font-weight: 600; color: #333; margin-bottom: 12px; font-size: 1rem; }
        .profile-image-preview img { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid #e9ecef; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto align-items-center">
                <?php include 'navbar_buttons.php'; ?>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <div class="col-md-9 col-lg-10 main-content">
                <h2 class="mb-4"><i class="fas fa-user-circle"></i> My Profile</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Profile" class="profile-avatar">
                            <div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                <p class="text-muted mb-1"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                                <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($user['phone_number'] ?? '—'); ?></p>
                                <p class="mb-0"><span class="badge bg-danger"><?php echo htmlspecialchars(ucfirst($user['role'] ?? 'admin')); ?></span></p>
                            </div>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="fas fa-user-edit"></i> Edit Profile
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="fas fa-lock"></i> Change Password
                                </button>
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
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="modal-body">
                        <div class="mb-4">
                            <h6 class="section-title">Profile Image</h6>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="profile-image-preview">
                                    <img id="profileImagePreview" src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Preview">
                                </div>
                                <div>
                                    <label class="form-label">Upload new image</label>
                                    <input type="file" class="form-control" name="profile_image" accept="image/jpeg,image/png,image/gif" id="profileImageInput">
                                    <div class="form-text">Max 2MB. JPG, PNG, GIF.</div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="section-title">Personal Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="user@g.batstate-u.edu.ph" required>
                                <div class="form-text">Must be @g.batstate-u.edu.ph</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" placeholder="09123456789" maxlength="11" pattern="^09\d{9}$" required>
                                <div class="form-text">11 digits, starting with 09</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(ucfirst($user['role'] ?? 'admin')); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Update Profile</button>
                    </div>
                </form>
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
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="8">
                            <div class="form-text">8+ chars, uppercase, lowercase, number, special character.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-key"></i> Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('profileImageInput')?.addEventListener('change', function(e) {
        var f = e.target.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function() { document.getElementById('profileImagePreview').src = r.result; };
        r.readAsDataURL(f);
    });
    </script>
</body>
</html>
