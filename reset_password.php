<?php
require_once 'includes/session.php';
require_once 'includes/db.php';

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

// Check if user completed OTP verification
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header('Location: forgot_password.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } else if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $password)) {
        $error = 'Password must be at least 8 characters with one uppercase, one lowercase, one digit, and one special character.';
    } else if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $email = $_SESSION['reset_email'];
        
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?');
        $stmt->bind_param('ss', $hashed_password, $email);
        
        if ($stmt->execute()) {
            // Delete the used OTP
            $stmt2 = $conn->prepare('DELETE FROM password_reset_otps WHERE email = ?');
            $stmt2->bind_param('s', $email);
            $stmt2->execute();
            $stmt2->close();
            
            // Clear session variables
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            
            $message = 'Password updated successfully. You can now login with your new password.';
            
            // Redirect to login after 3 seconds
            header('refresh:3;url=index.php');
        } else {
            $error = 'Failed to update password. Please try again.';
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
    <title>Reset Password - BSU Inventory Management System</title>
    <link rel="icon" href="images/bsutneu.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('images/BSU.jpg') center/cover no-repeat fixed;
        }

        .auth-modal {
            max-width: 500px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        .auth-header {
            background: #dc3545;
            color: #ffffff;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-header h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .auth-body { padding: 24px 26px 26px 26px; background: #ffffff; }

        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 14px;
        }
        .alert-danger { background: #fde8e8; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

        .form-label { font-weight: 600; color: #333; margin-bottom: 8px; display: block; }

        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f3f4f6;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .input-group .input-icon { padding: 12px 14px; color: #6b7280; }
        .input-group input {
            border: none; outline: none; background: transparent; padding: 12px 14px; font-size: 14px; width: 100%;
        }

        .btn-submit {
            width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #dc3545; color: #ffffff;
            border: none; border-radius: 10px; padding: 12px 16px; font-weight: 700; cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-submit:hover { background: #c82333; }

        .back-link { text-align: center; margin-top: 14px; }
        .back-link a { color: #ffffff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .email-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="auth-modal" role="dialog" aria-modal="true">
        <div class="auth-header">
            <i class="fas fa-key"></i>
            <h5>Reset Password</h5>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (!$message): ?>
                <div class="email-info">
                    <i class="fas fa-user"></i> Resetting password for:<br>
                    <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong>
                </div>

                <form method="POST" action="">
                    <label for="password" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <div id="password-strength" class="password-strength"></div>

                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <div id="password-match" class="password-strength"></div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </form>
            <?php else: ?>
                <div class="back-link">
                    <a href="index.php"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('at least 8 characters');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('lowercase letters');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('uppercase letters');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('numbers');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('special characters');
            
            if (strength < 2) {
                strengthDiv.innerHTML = '<span class="strength-weak">Weak password. Add: ' + feedback.join(', ') + '</span>';
            } else if (strength < 4) {
                strengthDiv.innerHTML = '<span class="strength-medium">Medium strength. Consider adding: ' + feedback.join(', ') + '</span>';
            } else {
                strengthDiv.innerHTML = '<span class="strength-strong">Strong password!</span>';
            }
        });
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong">Passwords match!</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">Passwords do not match</span>';
            }
        });
    </script>
</body>
</html>
