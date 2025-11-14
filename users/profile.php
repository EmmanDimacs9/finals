<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone_number)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
        $error = 'Phone number must be exactly 11 digits starting with 09.';
    } else {
        // Check if email already exists for other users
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();
        if ($email_check->get_result()->num_rows > 0) {
            $error = 'Email address already exists.';
        } else {
            // Update basic information
            $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone_number = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $full_name, $email, $phone_number, $user_id);
            
            if ($update_stmt->execute()) {
                // Update session
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                
                $success = 'Profile updated successfully!';
                
                // Refresh user data
                $stmt->execute();
                $user = $result->fetch_assoc();
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
    
    // Handle password change if provided
    if (!empty($current_password) && !empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $new_password)) {
            $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $password_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($password_stmt->execute()) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Failed to update password.';
            }
        }
    }
}

$page_title = 'Profile';
require_once 'header.php';
?>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div style="background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 30px;">
        <h1 style="color: #dc3545; margin-bottom: 30px;">
            <i class="fas fa-user-edit"></i> Edit Profile
        </h1>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label for="full_name" style="display: block; margin-bottom: 5px; font-weight: 600;">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;" required>
                </div>
                
                <div>
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;" required>
                </div>
                
                <div>
                    <label for="phone_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;" required maxlength="11" pattern="^09\d{9}$" placeholder="09123456789">
                    <small style="color: #6c757d;">Must be exactly 11 digits starting with 09</small>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Role</label>
                    <input type="text" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; background: #f8f9fa;" readonly>
                </div>
            </div>
            
            <div style="text-align: center; margin: 20px 0;">
                <button type="submit" style="background: #dc3545; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
        
        <hr style="margin: 30px 0;">
        
        <h2 style="color: #dc3545; margin-bottom: 20px;">
            <i class="fas fa-key"></i> Change Password
        </h2>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label for="current_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Current Password</label>
                    <input type="password" id="current_password" name="current_password" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;">
                </div>
                
                <div>
                    <label for="new_password" style="display: block; margin-bottom: 5px; font-weight: 600;">New Password</label>
                    <input type="password" id="new_password" name="new_password" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;" minlength="8">
                    <small style="color: #6c757d;">Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                </div>
                
                <div>
                    <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           style="width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px;" minlength="8">
                </div>
                
                <div style="display: flex; align-items: end;">
                    <button type="submit" style="background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </div>
        </form>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 