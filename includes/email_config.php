<?php
// Email Configuration for PHPMailer
// Replace these values with your actual email settings

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'ictoffice0520@gmail.com'); // Replace with your BSU Gmail
define('SMTP_PASSWORD', 'hkmp gplq zxsd otmy'); // Replace with your App Password
define('SMTP_FROM_EMAIL', 'ictoffice0520@gmail.com');
define('SMTP_FROM_NAME', 'BSU Inventory Management System');

// Alternative: If using a different email provider, update these settings
// For Outlook/Hotmail:
// define('SMTP_HOST', 'smtp-mail.outlook.com');
// define('SMTP_PORT', 587);

// For Yahoo:
// define('SMTP_HOST', 'smtp.mail.yahoo.com');
// define('SMTP_PORT', 587);

// Email settings
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_AUTH', true);
define('SMTP_DEBUG', false); // Set to true for debugging
?>
