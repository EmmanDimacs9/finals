<?php
require_once 'includes/session.php';
require_once 'logger.php';

if (isLoggedIn()) {
	if (isAdmin()) {
		logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], 'Logout', 'Admin logged out');
	}
}

$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
	session_unset();
	session_destroy();
}

header('Location: landing.php');
exit();
?>