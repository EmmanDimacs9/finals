<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send notification email to admin when a new request is submitted
 */
function notifyAdminNewRequest($requestId, $formType, $submittedBy, $submittedByEmail) {
    global $conn;
    
    try {
        // Get all admin users
        $adminQuery = "SELECT full_name, email FROM users WHERE role = 'admin'";
        $adminResult = $conn->query($adminQuery);
        
        if (!$adminResult || $adminResult->num_rows === 0) {
            error_log("No admin users found for notification");
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Add all admin users as recipients
        while ($admin = $adminResult->fetch_assoc()) {
            $mail->addAddress($admin['email'], $admin['full_name']);
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Request Submitted - BSU Inventory System';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #dc3545 0%, #343a40 100%); color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">BSU Inventory Management System</h2>
                <p style="margin: 10px 0 0 0;">New Request Notification</p>
            </div>
            
            <div style="padding: 30px; background: #f8f9fa;">
                <h3 style="color: #dc3545; margin-top: 0;">📋 New Request Submitted</h3>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
                    <p><strong>Request ID:</strong> #' . $requestId . '</p>
                    <p><strong>Form Type:</strong> ' . htmlspecialchars($formType) . '</p>
                    <p><strong>Submitted By:</strong> ' . htmlspecialchars($submittedBy) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($submittedByEmail) . '</p>
                    <p><strong>Date Submitted:</strong> ' . date('Y-m-d H:i:s') . '</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . getBaseUrl() . 'request.php" 
                       style="background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        View Request
                    </a>
                </div>
                
                <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                    This is an automated notification from the BSU Inventory Management System.
                </p>
            </div>
        </div>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send admin notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Send notification email to department admin when request is approved/rejected
 */
function notifyDeptAdminRequestStatus($requestId, $formType, $status, $submittedByEmail, $adminName, $remarks = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($submittedByEmail);
        
        // Content
        $mail->isHTML(true);
        
        $statusColor = $status === 'Approved' ? '#28a745' : '#dc3545';
        $statusIcon = $status === 'Approved' ? '✅' : '❌';
        $statusText = $status === 'Approved' ? 'Approved' : 'Rejected';
        
        $mail->Subject = 'Request ' . $statusText . ' - BSU Inventory System';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #dc3545 0%, #343a40 100%); color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">BSU Inventory Management System</h2>
                <p style="margin: 10px 0 0 0;">Request Status Update</p>
            </div>
            
            <div style="padding: 30px; background: #f8f9fa;">
                <h3 style="color: ' . $statusColor . '; margin-top: 0;">' . $statusIcon . ' Request ' . $statusText . '</h3>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid ' . $statusColor . ';">
                    <p><strong>Request ID:</strong> #' . $requestId . '</p>
                    <p><strong>Form Type:</strong> ' . htmlspecialchars($formType) . '</p>
                    <p><strong>Status:</strong> <span style="color: ' . $statusColor . '; font-weight: bold;">' . $statusText . '</span></p>
                    <p><strong>Reviewed By:</strong> ' . htmlspecialchars($adminName) . '</p>
                    <p><strong>Date Reviewed:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        
        if (!empty($remarks)) {
            $mail->Body .= '<p><strong>Remarks:</strong> ' . htmlspecialchars($remarks) . '</p>';
        }
        
        $mail->Body .= '</div>';
        
        if ($status === 'Approved') {
            $mail->Body .= '
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; color: #155724;"><strong>🎉 Congratulations!</strong> Your request has been approved and will be processed accordingly.</p>
                </div>';
        } else {
            $mail->Body .= '
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; color: #721c24;"><strong>📝 Note:</strong> Your request has been rejected. Please review the requirements and submit a new request if needed.</p>
                </div>';
        }
        
        $mail->Body .= '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . getBaseUrl() . 'depdashboard.php" 
                       style="background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        View Dashboard
                    </a>
                </div>
                
                <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                    This is an automated notification from the BSU Inventory Management System.
                </p>
            </div>
        </div>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send department admin notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Send notification email to department when service request is completed
 */
function notifyDeptServiceRequestCompleted($requestId, $equipment, $location, $accomplishment, $clientEmail, $clientName, $ictSrfNo = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($clientEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Service Request Completed - Please Evaluate - BSU Inventory System';
        
        $checklistUrl = getBaseUrl() . 'department/checklist.php';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">BSU Inventory Management System</h2>
                <p style="margin: 10px 0 0 0;">Service Request Completed</p>
            </div>
            
            <div style="padding: 30px; background: #f8f9fa;">
                <h3 style="color: #28a745; margin-top: 0;">✅ Service Request Completed</h3>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <p><strong>Request ID:</strong> #' . $requestId . '</p>';
        
        if (!empty($ictSrfNo)) {
            $mail->Body .= '<p><strong>ICT SRF No:</strong> ' . htmlspecialchars($ictSrfNo) . '</p>';
        }
        
        $mail->Body .= '
                    <p><strong>Equipment:</strong> ' . htmlspecialchars($equipment) . '</p>
                    <p><strong>Location:</strong> ' . htmlspecialchars($location) . '</p>
                    <p><strong>Completed By:</strong> ICT Staff</p>
                    <p><strong>Date Completed:</strong> ' . date('F d, Y g:i A') . '</p>';
        
        if (!empty($accomplishment)) {
            $mail->Body .= '<p><strong>Work Done:</strong> ' . htmlspecialchars($accomplishment) . '</p>';
        }
        
        $mail->Body .= '</div>
                
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <div style="display: flex; align-items: start; margin-bottom: 15px;">
                        <div style="background-color: #4c51bf; border-radius: 4px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-right: 12px;">
                            <span style="color: white; font-size: 16px;">✓</span>
                        </div>
                        <div>
                            <p style="margin: 0; color: #1e40af; font-weight: 600; font-size: 16px;">Please Evaluate Our Service</p>
                            <p style="margin: 5px 0 0 0; color: #1e40af; font-size: 14px;">Your feedback helps us improve our ICT services. Please take a moment to evaluate the service provided.</p>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $checklistUrl . '" 
                       style="background: #4c51bf; color: white; padding: 14px 35px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 16px;">
                        📋 Submit Evaluation Survey
                    </a>
                </div>
                
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; color: #856404; font-size: 14px;">
                        <strong>💡 Note:</strong> You can access the evaluation survey anytime from your dashboard by clicking on <strong>"Checklist"</strong> in the navigation menu.
                    </p>
                </div>
                
                <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                    This is an automated notification from the BSU Inventory Management System.
                </p>
            </div>
        </div>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send completion notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get base URL for the application
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $path . '/';
}

/**
 * Send notification email to technicians when a new system request is submitted
 */
function notifyTechniciansNewSystemRequest($requestId, $formType, $submittedBy, $submittedByEmail, $systemName) {
    global $conn;
    
    try {
        // Get all technician users
        $techQuery = "SELECT full_name, email FROM users WHERE role = 'technician'";
        $techResult = $conn->query($techQuery);
        
        if (!$techResult || $techResult->num_rows === 0) {
            error_log("No technician users found for notification");
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Add all technician users as recipients
        while ($tech = $techResult->fetch_assoc()) {
            $mail->addAddress($tech['email'], $tech['full_name']);
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New System Request Submitted - BSU Inventory System';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #dc3545 0%, #343a40 100%); color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">BSU Inventory Management System</h2>
                <p style="margin: 10px 0 0 0;">New System Request Notification</p>
            </div>
            
            <div style="padding: 30px; background: #f8f9fa;">
                <h3 style="color: #dc3545; margin-top: 0;">🔧 New System Request Submitted</h3>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
                    <p><strong>Request ID:</strong> #' . $requestId . '</p>
                    <p><strong>System Name:</strong> ' . htmlspecialchars($systemName) . '</p>
                    <p><strong>Form Type:</strong> ' . htmlspecialchars($formType) . '</p>
                    <p><strong>Submitted By:</strong> ' . htmlspecialchars($submittedBy) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($submittedByEmail) . '</p>
                    <p><strong>Date Submitted:</strong> ' . date('Y-m-d H:i:s') . '</p>
                </div>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . getBaseUrl() . 'technician/indet.php"
                       style="background: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        View Request
                    </a>
                </div>
                
                <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                    This is an automated notification from the BSU Inventory Management System.
                </p>
            </div>
        </div>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send technician notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Send test notification email
 */
function sendTestNotification($email) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPDebug  = 0;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Notification - BSU Inventory System';
        
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #dc3545 0%, #343a40 100%); color: white; padding: 20px; text-align: center;">
                <h2 style="margin: 0;">BSU Inventory Management System</h2>
                <p style="margin: 10px 0 0 0;">Test Notification</p>
            </div>
            
            <div style="padding: 30px; background: #f8f9fa;">
                <h3 style="color: #28a745; margin-top: 0;">✅ Email Notifications Working!</h3>
                
                <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <p><strong>Test Time:</strong> ' . date('Y-m-d H:i:s') . '</p>
                    <p><strong>Status:</strong> Email notifications are properly configured and working.</p>
                </div>
                
                <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                    This is a test notification from the BSU Inventory Management System.
                </p>
            </div>
        </div>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Failed to send test notification: " . $e->getMessage());
        return false;
    }
}
?>
