<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone_number) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/@g\.batstate-u\.edu\.ph$/', $email)) {
        $error = 'Email must be from @g.batstate-u.edu.ph domain';
    } elseif (!preg_match('/^09\d{9}$/', $phone_number)) {
        $error = 'Phone number must be exactly 11 digits starting with 09';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email address already exists';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, role, phone_number, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $role, $phone_number, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSU Inventory Management System - Register</title>
    <link rel="icon" href="images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #343a40 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
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
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
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
        }
        .btn-register:hover {
            background: #c82333;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #dc3545;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-user-plus"></i> Register</h2>
            <p class="mb-0">BSU Inventory Management System</p>
        </div>
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="username@g.batstate-u.edu.ph" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required maxlength="11" pattern="^09\d{9}$" placeholder="09123456789">
                        </div>
                        <small class="text-muted">Must be exactly 11 digits starting with 09</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="technician">Technician</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        </div>
                        <small class="text-muted">Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="login-link">
                <p>Already have an account? <a href="landing.php">Login here</a></p>
                <p class="mt-2"><a href="landing.php" class="text-muted"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 