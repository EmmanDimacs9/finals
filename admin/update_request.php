<?php
require_once '../includes/db.php';
require_once '../includes/session.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if ($id && in_array($status, ['Approved', 'Rejected'])) {
    $stmt = $conn->prepare("UPDATE system_requests SET status=?, date_updated=NOW() WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Request $status successfully!'); window.location.href='request.php';</script>";
} else {
    echo "Invalid request.";
}
?>
