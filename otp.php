<?php
require_once 'includes/session.php';
require_once 'includes/db.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// If already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

// Check if user came from forgot password
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

$message = '';
$error = '';

// Check for success message from forgot password
if (isset($_SESSION['email_sent_message'])) {
    $message = $_SESSION['email_sent_message'];
    unset($_SESSION['email_sent_message']); // Clear after displaying
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_code = trim($_POST['otp_code'] ?? '');
    
    if ($otp_code === '') {
        $error = 'Please enter the OTP code.';
    } else if (strlen($otp_code) !== 6 || !is_numeric($otp_code)) {
        $error = 'Please enter a valid 6-digit OTP code.';
    } else {
        $email = $_SESSION['reset_email'];
        
        // Clean up expired OTPs first
        $conn->query('DELETE FROM password_reset_otps WHERE expires_at < NOW()');
        
        // Verify OTP
        $stmt = $conn->prepare('SELECT id FROM password_reset_otps WHERE email = ? AND otp_code = ? AND expires_at > NOW()');
        $stmt->bind_param('ss', $email, $otp_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            // OTP is valid, store verification in session and redirect to reset password
            $_SESSION['otp_verified'] = true;
            header('Location: reset_password.php');
            exit();
        } else {
            $error = 'Invalid or expired OTP code. Please try again.';
        }
        $stmt->close();
    }
}

// Handle resend OTP
if (isset($_POST['resend_otp'])) {
    $email = $_SESSION['reset_email'];
    
    // Generate new 6-digit OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Update OTP in database with new expiration
    $stmt = $conn->prepare('UPDATE password_reset_otps SET otp_code = ?, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE), created_at = NOW() WHERE email = ?');
    $stmt->bind_param('ss', $otp, $email);
    
    if ($stmt->execute()) {
        // Send new OTP via email
        $mail = new PHPMailer(true);
        
        try {
            // Include email configuration
            require_once 'includes/email_config.php';
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = SMTP_AUTH;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Password Reset OTP - BSU Inventory Management';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #dc3545; text-align: center;'>New Verification Code</h2>
                    <p>Hello,</p>
                    <p>You have requested a new verification code for password reset.</p>
                    <p>Your new verification code is:</p>
                    <div style='background-color: #f8f9fa; border: 2px solid #dc3545; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;'>
                        <h1 style='color: #dc3545; font-size: 32px; margin: 0; letter-spacing: 5px;'>$otp</h1>
                    </div>
                    <p><strong>This code will expire in 15 minutes.</strong></p>
                    <p>If you did not request this, please ignore this email.</p>
                    <hr style='border: 1px solid #eee; margin: 20px 0;'>
                    <p style='color: #666; font-size: 12px; text-align: center;'>
                        Batangas State University - Inventory Management System
                    </p>
                </div>
            ";
            
            $mail->send();
            $message = 'A new verification code has been sent to your email.';
            
        } catch (Exception $e) {
            $error = 'Failed to send new verification code. Please try again.';
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - BSU Inventory Management System</title>
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
             max-width: 450px;
             background: #ffffff;
             border-radius: 18px;
             box-shadow: 0 20px 60px rgba(0,0,0,0.35);
             overflow: hidden;
             margin: 20px;
         }

         .auth-header {
             background: #dc3545;
             color: #ffffff;
             padding: 24px 22px;
             text-align: center;
             border-radius: 18px 18px 0 0;
         }

         .auth-header h5 {
             margin: 0;
             font-size: 22px;
             font-weight: 800;
             letter-spacing: .5px;
         }

         .auth-body { 
             padding: 30px 26px 26px 26px; 
             background: #ffffff; 
         }

         .alert {
             border-radius: 12px;
             padding: 14px 16px;
             margin-bottom: 18px;
             font-size: 14px;
             display: flex;
             align-items: center;
             gap: 10px;
         }
         .alert-danger { 
             background: #fde8e8; 
             color: #b91c1c; 
             border: 1px solid #fecaca; 
         }
         .alert-success { 
             background: #ecfdf5; 
             color: #065f46; 
             border: 1px solid #a7f3d0; 
         }

         .form-label { 
             font-weight: 600; 
             color: #333; 
             margin-bottom: 12px; 
             display: block; 
             font-size: 16px;
         }

         .otp-input {
             width: 100%;
             border: 2px solid #e9ecef;
             border-radius: 12px;
             background: #ffffff;
             padding: 16px 18px;
             font-size: 18px;
             text-align: center;
             letter-spacing: 4px;
             font-weight: 600;
             transition: all 0.3s ease;
             box-sizing: border-box;
         }

         .otp-input:focus {
             outline: none;
             border-color: #dc3545;
             background: #ffffff;
             box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
         }

         .otp-input::placeholder {
             color: #adb5bd;
             letter-spacing: 4px;
         }

         .btn-submit {
             width: 100%;
             display: flex; 
             align-items: center; 
             justify-content: center; 
             gap: 10px;
             background: #dc3545; 
             color: #ffffff;
             border: none; 
             border-radius: 12px; 
             padding: 16px 20px; 
             font-weight: 700; 
             cursor: pointer;
             transition: all 0.3s ease;
             font-size: 16px;
             margin-top: 20px;
             box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
         }
         .btn-submit:hover { 
             background: #c82333; 
             transform: translateY(-2px);
             box-shadow: 0 6px 16px rgba(220, 53, 69, 0.4);
         }

         .btn-resend {
             width: 100%;
             display: flex; 
             align-items: center; 
             justify-content: center; 
             gap: 10px;
             background: transparent; 
             color: #dc3545;
             border: 2px solid #dc3545; 
             border-radius: 12px; 
             padding: 14px 18px; 
             font-weight: 700; 
             cursor: pointer;
             transition: all 0.3s ease;
             font-size: 14px;
             margin-top: 16px;
         }
         .btn-resend:hover { 
             background: #dc3545; 
             color: #ffffff;
             transform: translateY(-1px);
             box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
         }

         .back-link { 
             text-align: center; 
             margin-top: 20px; 
         }
         .back-link a { 
             color: #6c757d; 
             text-decoration: none; 
             font-size: 14px;
             transition: color 0.2s ease;
         }
         .back-link a:hover { 
             color: #dc3545;
             text-decoration: underline; 
         }

         .email-info {
             background: #f8f9fa;
             border-radius: 12px;
             padding: 16px;
             margin-bottom: 24px;
             text-align: center;
             font-size: 14px;
             color: #666;
             border: 1px solid #e9ecef;
         }

         .email-info i {
             color: #6c757d;
             margin-right: 8px;
         }

         .email-info strong {
             color: #333;
             font-weight: 600;
         }

         .fa-spinner {
             animation: spin 1s linear infinite;
         }

         @keyframes spin {
             0% { transform: rotate(0deg); }
             100% { transform: rotate(360deg); }
         }

         .btn-submit:disabled,
         .btn-resend:disabled {
             opacity: 0.7;
             cursor: not-allowed;
             transform: none !important;
         }
    </style>
</head>
<body>
    <div class="auth-modal" role="dialog" aria-modal="true">
        <div class="auth-header">
            <h5>Verification Account</h5>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <div class="email-info">
                <i class="fas fa-envelope"></i> Verification code sent to:<br>
                <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong>
            </div>

            <form method="POST" action="">
                <label for="otp_code" class="form-label">OTP Code:</label>
                <input type="text" 
                       id="otp_code" 
                       name="otp_code" 
                       class="otp-input"
                       placeholder="000000"
                       maxlength="6"
                       pattern="[0-9]{6}"
                       required>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Verify
                </button>
            </form>

            <form method="POST" action="">
                <button type="submit" name="resend_otp" class="btn-resend">
                    <i class="fas fa-redo"></i> Resend Code
                </button>
            </form>
            
            <div class="back-link">
                <a href="forgot_password.php"><i class="fas fa-arrow-left"></i> Back to Forgot Password</a>
            </div>
        </div>
    </div>

     <script>
         // Auto-focus on OTP input
         document.getElementById('otp_code').focus();
         
         // Only allow numbers
         document.getElementById('otp_code').addEventListener('input', function(e) {
             this.value = this.value.replace(/[^0-9]/g, '');
             
             // Auto-submit when 6 digits are entered
             if (this.value.length === 6) {
                 // Add a small delay for better UX
                 setTimeout(() => {
                     this.form.submit();
                 }, 300);
             }
         });
         
         // Prevent paste of non-numeric content
         document.getElementById('otp_code').addEventListener('paste', function(e) {
             e.preventDefault();
             const paste = (e.clipboardData || window.clipboardData).getData('text');
             const numbers = paste.replace(/[^0-9]/g, '');
             this.value = numbers.slice(0, 6);
             
             if (this.value.length === 6) {
                 setTimeout(() => {
                     this.form.submit();
                 }, 300);
             }
         });
         
         // Add loading state to buttons
         document.querySelector('form').addEventListener('submit', function(e) {
             const submitBtn = this.querySelector('.btn-submit');
             if (submitBtn) {
                 submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
                 submitBtn.disabled = true;
             }
         });
         
         document.querySelector('form[method="POST"]:last-of-type').addEventListener('submit', function(e) {
             const resendBtn = this.querySelector('.btn-resend');
             if (resendBtn) {
                 resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                 resendBtn.disabled = true;
             }
         });
     </script>
</body>
</html>
