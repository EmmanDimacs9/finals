<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Use the main BSU database connection
        $stmt = $conn->prepare("SELECT id, full_name, email, role, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables for main BSU system
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Log the login activity to main BSU system
                $login_time = date('Y-m-d H:i:s');
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                
                // Insert login record into main BSU system
                $log_stmt = $conn->prepare("
                    INSERT INTO user_logins (user_id, login_time, ip_address, user_agent, source) 
                    VALUES (?, ?, ?, ?, 'users_folder')
                ");
                $log_stmt->bind_param("isss", $user['id'], $login_time, $ip_address, $user_agent);
                $log_stmt->execute();
                
                // Redirect to users folder dashboard
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
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
    <title>Login - BSU User Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
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
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: #dc3545;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        .btn-login {
            background: #dc3545;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            color: white;
        }
        .btn-login:hover {
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
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-university"></i> BSU User System
            </div>
            <p class="mb-0">Access User Management</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login to BSU System
                    </button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 1rem;">
                <p>Don't have an account? <a href="register.php" style="color: #dc3545;">Register here</a></p>
                <p class="mt-2"><a href="../landing.php" style="color: #6c757d;"><i class="fas fa-arrow-left"></i> Back to Main System</a></p>
                <p class="mt-2"><a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Logout</a></p>
            </div>
        </div>
    </div>
</body>
</html> 