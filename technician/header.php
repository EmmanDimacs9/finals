<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../landing.php');
    exit();
}

// Normalize profile image URL regardless of how it's stored (filename vs path)
$profileImageUrl = '';
if (!empty($_SESSION['profile_image'])) {
    $pi = $_SESSION['profile_image'];
    if (preg_match('/^https?:\/\//', $pi)) {
        // Absolute URL stored
        $profileImageUrl = $pi;
    } elseif (strpos($pi, 'uploads/') === 0) {
        // Path from web root, add one level up since we're in technician/
        $profileImageUrl = '../' . $pi;
    } elseif (strpos($pi, '../uploads/') === 0 || strpos($pi, '/uploads/') === 0) {
        // Already a relative path
        $profileImageUrl = $pi;
    } else {
        // Likely only the filename stored (technician uploader behavior)
        $profileImageUrl = '../uploads/profiles/' . $pi;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ICT Service Portal</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
            --gray-color: #6c757d;
            --blue-color: #007bff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Header Navigation */
        .header-nav {
            background: linear-gradient(90deg, #dc3545 0%, #343a40 100%);
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-brand {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
        }
        
        .header-brand i {
            margin-right: 8px;
        }
        
        .header-user {
            color: white;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            position: relative;
            gap: 15px;
        }
        
        .header-user i {
            margin-right: 5px;
        }
        
        /* Quick Actions Lightning Icon */
        .quick-actions-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .quick-actions-trigger {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .quick-actions-trigger:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        
        .quick-actions-trigger i {
            font-size: 1.3rem;
            color: white;
            margin: 0;
        }
        
        .quick-actions-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            min-width: 220px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.08);
            margin-top: 8px;
        }
        
        .quick-actions-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .quick-action-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .quick-action-item:last-child {
            border-bottom: none;
        }
        
        .quick-action-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #dc3545;
        }
        
        .quick-action-item i {
            width: 20px;
            margin-right: 12px;
            color: #666;
            font-size: 1rem;
        }
        
        .quick-action-item:hover i {
            color: #dc3545;
        }
        
        .quick-action-item span {
            flex: 1;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        /* Statistics Modal Styling */
        .stat-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 25px 15px;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card-blue i {
            color: #007bff;
        }
        
        .stat-card-yellow i {
            color: #ffc107;
        }
        
        .stat-card-green i {
            color: #28a745;
        }
        
        .stat-card h3 {
            font-weight: 700;
            color: #212529;
            margin: 0;
        }
        
        .stat-card small {
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .profile-trigger {
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background-color 0.2s ease;
            width: 40px;
            height: 40px;
        }
        
        .profile-trigger:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .profile-picture-small {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            min-width: 280px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
        }
        
        .dropdown-profile-picture {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 3px solid #e9ecef;
        }
        
        .dropdown-user-info h6 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }
        
        .dropdown-user-info p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
            color: #333;
        }
        
        .dropdown-item i {
            width: 20px;
            margin-right: 15px;
            color: #666;
            font-size: 1.1rem;
        }
        
        .dropdown-item span {
            flex: 1;
            font-weight: 500;
        }
        
        .dropdown-item .chevron {
            color: #999;
            font-size: 0.8rem;
        }
        
        .dropdown-item.logout {
            color: #dc3545;
        }
        
        .dropdown-item.logout:hover {
            background-color: #fff5f5;
            color: #dc3545;
        }
        
        .dropdown-item.logout i {
            color: #dc3545;
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
        
        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
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
        
        .main-content {
            padding: 20px;
            margin-bottom: 80px; /* Space for footer nav */
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-bottom: 20px;
            }
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
        
        /* Footer Navigation */
        .footer-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            border-top: 2px solid #e9ecef;
            z-index: 1000;
            padding: 12px 0 8px 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 100%;
            margin: 0 auto;
            padding: 0 10px;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #6c757d;
            font-size: 0.7rem;
            font-weight: 500;
            padding: 6px 8px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 50px;
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-radius: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 3px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 2;
        }
        
        .nav-item span {
            font-size: 0.65rem;
            line-height: 1.1;
            font-weight: 600;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover {
            color: #dc3545;
            transform: translateY(-2px);
        }
        
        .nav-item:hover::before {
            opacity: 1;
        }
        
        .nav-item:hover i {
            transform: scale(1.1);
            color: #dc3545;
        }
        
        .nav-item.active {
            color: #dc3545;
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.08) 100%);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
        }
        
        .nav-item.active::before {
            opacity: 1;
        }
        
        .nav-item.active i {
            transform: scale(1.15);
            color: #dc3545;
        }
        
        .nav-item.active span {
            color: #dc3545;
            font-weight: 700;
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: #dc3545;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
        }
        
        .nav-item:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <nav class="header-nav">
        <div class="header-container">
            <a href="kanban.php" class="header-brand">
                <img src="../images/User icon.png" alt="ICT Service Portal Logo" style="width: 40px; height: 40px; margin-right: 12px;">
                ICT Service Portal
            </a>
            <div class="header-user">
                <!-- Quick Actions Lightning Icon -->
                <div class="quick-actions-dropdown">
                    <div class="quick-actions-trigger" onclick="toggleQuickActions()" title="Quick Actions">
                        <i class="fas fa-bolt"></i>
                    </div>
                    
                    <div class="quick-actions-menu" id="quickActionsMenu">
                        <a href="#" class="quick-action-item" onclick="openStatisticsModal(); return false;">
                            <i class="fas fa-chart-pie"></i>
                            <span>My Statistics</span>
                        </a>
                        <a href="mytasks.php" class="quick-action-item">
                            <i class="fas fa-tasks"></i>
                            <span>My Tasks</span>
                        </a>
                        <a href="inventory.php" class="quick-action-item">
                            <i class="fas fa-boxes"></i>
                            <span>Inventory</span>
                        </a>
                        <a href="qr.php" class="quick-action-item">
                            <i class="fas fa-qrcode"></i>
                            <span>QR Scanner</span>
                        </a>
                        <a href="reports.php" class="quick-action-item">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                        <a href="history.php" class="quick-action-item">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </div>
                </div>
                
                <div class="profile-dropdown">
                    <div class="profile-trigger" onclick="toggleDropdown()">
                        <img src="<?php echo !empty($profileImageUrl) ? $profileImageUrl : 'https://via.placeholder.com/32x32/6c757d/ffffff?text=' . substr($_SESSION['user_name'], 0, 1); ?>" 
                             alt="Profile" class="profile-picture-small">
                    </div>
                    
                    <div class="dropdown-menu" id="profileDropdown">
                        <div class="dropdown-header">
                            <img src="<?php echo !empty($profileImageUrl) ? $profileImageUrl : 'https://via.placeholder.com/60x60/6c757d/ffffff?text=' . substr($_SESSION['user_name'], 0, 1); ?>" 
                                 alt="Profile" class="dropdown-profile-picture">
                            <div class="dropdown-user-info">
                                <h6><?php echo $_SESSION['user_name']; ?></h6>
                                <p><?php echo $_SESSION['user_email']; ?></p>
                            </div>
                        </div>
                        
                        <a href="#" class="dropdown-item" onclick="openEditProfile()">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        
                        <a href="#" class="dropdown-item" onclick="openSettings()">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user"></i> Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="editProfileForm" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <!-- Profile Image Section -->
                    <div class="profile-image-section mb-4">
                        <h6 class="section-title">Profile Image</h6>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="profile-image-preview">
                                    <img id="profileImagePreview" 
                                         src="<?php echo !empty($profileImageUrl) ? $profileImageUrl : 'https://via.placeholder.com/100x100/6c757d/ffffff?text=' . substr($_SESSION['user_name'], 0, 1); ?>" 
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
                                       value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" 
                                       placeholder="username@g.batstate-u.edu.ph" required>
                                <div class="form-text">Must be from @g.batstate-u.edu.ph</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone_number" 
                                       placeholder="09123456789" maxlength="11" required>
                                <div class="form-text">Must be exactly 11 digits starting with 09</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($_SESSION['user_role'] ?? 'Technician'); ?>" readonly>
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
                <form method="POST" id="changePasswordForm" action="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                            <div class="form-text">Must contain uppercase, number, and special character</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
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
    
<!-- Logout Confirm Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-sign-out-alt"></i> Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger confirm-logout">Logout</button>
            </div>
        </div>
    </div>
    </div>
<!-- End Logout Confirm Modal -->

<!-- My Statistics Modal -->
<div class="modal fade" id="statisticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-pie"></i> My Statistics
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row text-center g-3">
                    <div class="col-6">
                        <div class="stat-card stat-card-blue">
                            <i class="fas fa-desktop fa-3x mb-3"></i>
                            <h3 class="mb-2" id="stat-equipment">0</h3>
                            <small class="text-muted">Equipment Assigned</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card stat-card-blue">
                            <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                            <h3 class="mb-2" id="stat-tasks">0</h3>
                            <small class="text-muted">Tasks Assigned</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card stat-card-yellow">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <h3 class="mb-2" id="stat-maintenance">0</h3>
                            <small class="text-muted">Maintenance Records</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card stat-card-green">
                            <i class="fas fa-calendar fa-3x mb-3"></i>
                            <h3 class="mb-2" id="stat-month"><?php echo date('M Y'); ?></h3>
                            <small class="text-muted">Current Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End My Statistics Modal -->

    <script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        const quickActionsMenu = document.getElementById('quickActionsMenu');
        dropdown.classList.toggle('show');
        // Close quick actions menu when opening profile dropdown
        if (dropdown.classList.contains('show')) {
            quickActionsMenu.classList.remove('show');
        }
    }
    
    function toggleQuickActions() {
        const quickActionsMenu = document.getElementById('quickActionsMenu');
        const profileDropdown = document.getElementById('profileDropdown');
        quickActionsMenu.classList.toggle('show');
        // Close profile dropdown when opening quick actions menu
        if (quickActionsMenu.classList.contains('show')) {
            profileDropdown.classList.remove('show');
        }
    }
    
    function openStatisticsModal() {
        // Close quick actions menu
        document.getElementById('quickActionsMenu').classList.remove('show');
        
        // Fetch statistics
        fetch('get_statistics.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stat-equipment').textContent = data.equipment_count || 0;
                    document.getElementById('stat-tasks').textContent = data.task_count || 0;
                    document.getElementById('stat-maintenance').textContent = data.maintenance_count || 0;
                } else {
                    // Set defaults if fetch fails
                    document.getElementById('stat-equipment').textContent = '0';
                    document.getElementById('stat-tasks').textContent = '0';
                    document.getElementById('stat-maintenance').textContent = '0';
                }
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('statisticsModal'));
                modal.show();
            })
            .catch(err => {
                console.error('Error fetching statistics:', err);
                // Set defaults on error
                document.getElementById('stat-equipment').textContent = '0';
                document.getElementById('stat-tasks').textContent = '0';
                document.getElementById('stat-maintenance').textContent = '0';
                // Show modal anyway
                const modal = new bootstrap.Modal(document.getElementById('statisticsModal'));
                modal.show();
            });
    }
    
    function openEditProfile() {
        // Close dropdown first
        document.getElementById('profileDropdown').classList.remove('show');
        // Open the edit profile modal
        const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
        modal.show();
    }
    
    function openSettings() {
        // Close dropdown first
        document.getElementById('profileDropdown').classList.remove('show');
        // Open the change password modal
        const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
        modal.show();
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const profileDropdown = document.getElementById('profileDropdown');
        const profileTrigger = document.querySelector('.profile-trigger');
        const quickActionsMenu = document.getElementById('quickActionsMenu');
        const quickActionsTrigger = document.querySelector('.quick-actions-trigger');
        
        // Close profile dropdown if clicking outside
        if (!profileTrigger.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove('show');
        }
        
        // Close quick actions menu if clicking outside
        if (!quickActionsTrigger.contains(event.target) && !quickActionsMenu.contains(event.target)) {
            quickActionsMenu.classList.remove('show');
        }
    });
    
    // Close dropdowns on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('profileDropdown').classList.remove('show');
            document.getElementById('quickActionsMenu').classList.remove('show');
        }
    });
    
    // Image preview function
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
    
    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        // Intercept logout links and show confirm modal
        document.addEventListener('click', function(e) {
            const logoutLink = e.target.closest('a[href="logout.php"]');
            if (!logoutLink) return;
            e.preventDefault();
            const modalEl = document.getElementById('logoutConfirmModal');
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                window.location.href = logoutLink.href;
                return;
            }
            const confirmBtn = modalEl.querySelector('.confirm-logout');
            if (confirmBtn) {
                confirmBtn.onclick = function() { window.location.href = logoutLink.href; };
            }
            new bootstrap.Modal(modalEl).show();
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
        
        // Change Password Modal validation
        const changePasswordForm = document.getElementById('changePasswordForm');
        if (changePasswordForm) {
            const newPasswordInputModal = changePasswordForm.querySelector('input[name="new_password"]');
            const confirmPasswordInput = changePasswordForm.querySelector('input[name="confirm_password"]');
            
            // New password validation in modal
            if (newPasswordInputModal) {
                newPasswordInputModal.addEventListener('input', function() {
                    const password = this.value;
                    const hasUpper = /[A-Z]/.test(password);
                    const hasNumber = /\d/.test(password);
                    const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                    const isValid = password.length >= 6 && hasUpper && hasNumber && hasSpecial;
                    this.setCustomValidity(isValid ? '' : 'Password must contain at least one uppercase letter, one number, and one special character');
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