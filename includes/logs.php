<?php
require_once 'db.php'; // make sure db.php connects to your database

/**
 * Log an action to the logs table.
 *
 * @param string $username  The name or username of the user performing the action
 * @param string $action  Description of what happened
 */
function logAction($username, $action) {
    global $conn;

    if (!$conn) {
        error_log("Database connection failed in logs.php");
        return;
    }

    $stmt = $conn->prepare("INSERT INTO logs (user, action, date) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $username, $action);
    $stmt->execute();
    $stmt->close();
}
?>
