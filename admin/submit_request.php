<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $form_type = trim($_POST['form_type'] ?? '');

    if ($user_id === 0 || $form_type === '') {
        echo "<script>alert('❌ Missing data.'); window.history.back();</script>";
        exit;
    }

    // optional: simple PDF path placeholder
    $pdfPath = "PDFS/{$form_type}/generated/" . time() . ".pdf";

    $stmt = $conn->prepare("INSERT INTO system_requests (user_id, report_type, pdf_path, status, date_submitted) VALUES (?, ?, ?, 'Pending', NOW())");
    $stmt->bind_param("iss", $user_id, $form_type, $pdfPath);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Request sent successfully!'); window.location.href='../department/depdashboard.php';</script>";
    } else {
        echo "<script>alert('❌ Database error: {$stmt->error}'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
