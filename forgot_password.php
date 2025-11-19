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

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Please enter your email address.';
    } else {
        // Check if user exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in database with expiration (15 minutes from now)
            $stmt2 = $conn->prepare('INSERT INTO password_reset_otps (email, otp_code, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NOW()) ON DUPLICATE KEY UPDATE otp_code = ?, expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE), created_at = NOW()');
            $stmt2->bind_param('sss', $email, $otp, $otp);
            
            if ($stmt2->execute()) {
                // Send OTP via email
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
                    $mail->Subject = 'Password Reset OTP - BSU Inventory Management';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #dc3545; text-align: center;'>Password Reset Verification</h2>
                            <p>Hello,</p>
                            <p>You have requested to reset your password for the BSU Inventory Management System.</p>
                            <p>Your verification code is:</p>
                            <div style='background-color: #f8f9fa; border: 2px solid #dc3545; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;'>
                                <h1 style='color: #dc3545; font-size: 32px; margin: 0; letter-spacing: 5px;'>$otp</h1>
                            </div>
                            <p><strong>This code will expire in 15 minutes.</strong></p>
                            <p>If you did not request this password reset, please ignore this email.</p>
                            <hr style='border: 1px solid #eee; margin: 20px 0;'>
                            <p style='color: #666; font-size: 12px; text-align: center;'>
                                Batangas State University - Inventory Management System
                            </p>
                        </div>
                    ";
                    
                    $mail->send();
                    
                    // Set success message and redirect to OTP verification page
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['email_sent_message'] = 'Verification code sent successfully to your email.';
                    header('Location: otp.php');
                    exit();
                    
                } catch (Exception $e) {
                    $error = 'Failed to send verification email. Please try again.';
                }
            } else {
                $error = 'Failed to process your request. Please try again.';
            }
            $stmt2->close();
        } else {
            // Same generic message for security
            $message = 'If this email is registered, a verification code has been sent.';
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
    <title>Forgot Password - BSU Inventory Management System</title>
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
            /* width: 90%; */
            max-width: 560px;
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
        

        .back-link {
            /* width: 100%; */
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #dc3545; color: #ffffff;
            border: none; border-radius: 10px; padding: 12px 16px; font-weight: 700; cursor: pointer;
            transition: background 0.2s ease;
        }
        .back-link:hover { background: #c82333; }

        .back-link { text-align: center; margin-top: 14px; }
        .back-link a { color: #ffffff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-modal" role="dialog" aria-modal="true">
        <div class="auth-header">
            <i class="fas fa-key"></i>
            <h5>Forgot Password</h5>
        </div>
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert alert-success" role="alert"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="email" class="form-label">Enter your account email</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email" required>
                </div>
                <div style="height: 14px"></div>
                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Send Verification Code</button>
            </form>

            
            
            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 