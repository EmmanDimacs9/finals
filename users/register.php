<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($role) || empty($phone_number) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
        $error = 'Phone number must be exactly 11 digits starting with 09.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character.';
    } elseif (!in_array($role, ['admin', 'technician'])) {
        $error = 'Please select a valid role.';
    } else {
        // Use the main BSU database connection
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address already exists.';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_stmt = $conn->prepare("
                INSERT INTO users (full_name, email, role, phone_number, password, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert_stmt->bind_param("sssss", $full_name, $email, $role, $phone_number, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;
                
                // Log the registration activity
                $registration_time = date('Y-m-d H:i:s');
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                
                // Insert registration record
                $log_stmt = $conn->prepare("
                    INSERT INTO user_logins (user_id, login_time, ip_address, user_agent, source) 
                    VALUES (?, ?, ?, ?, 'users_folder_registration')
                ");
                $log_stmt->bind_param("isss", $user_id, $registration_time, $ip_address, $user_agent);
                $log_stmt->execute();
                
                $success = 'Registration successful! You can now login to the BSU system.';
                
                // Clear form data
                $_POST = array();
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BSU User Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #343a40 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        .register-header {
            background: #dc3545;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-body {
            padding: 40px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            margin-bottom: 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .btn-register {
            background: #dc3545;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
        }
        .btn-register:hover {
            background: #c82333;
            color: white;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        small {
            color: #6c757d;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-university"></i> BSU User System
            </div>
            <p class="mb-0">Create New Account</p>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="technician" <?php echo (isset($_POST['role']) && $_POST['role'] === 'technician') ? 'selected' : ''; ?>>Technician</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" class="form-control" required 
                           maxlength="11" pattern="^09\d{9}$" placeholder="09123456789"
                           value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                    <small>Must be exactly 11 digits starting with 09</small>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required 
                           minlength="8">
                    <small>Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character.</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                           minlength="8">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus"></i> Register for BSU System
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Already have an account? <a href="login.php" style="color: #dc3545;">Login here</a></p>
                <p class="mt-2"><a href="../landing.php" style="color: #6c757d;"><i class="fas fa-arrow-left"></i> Back to Main System</a></p>
                <p class="mt-2"><a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Logout</a></p>
            </div>
        </div>
    </div>
</body>
</html> 