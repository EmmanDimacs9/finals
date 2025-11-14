<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../logger.php';

if (isset($_SESSION['user_id'])) {
    // Log logout activity
    if (isAdmin()) {
        logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Logout', 'Admin logged out');
    }
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}

header('Location: ../landing.php');
exit();
?>

