<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 0;
$request_id = $_POST['request_id'] ?? 0;
$eval_response = $_POST['eval_response'] ?? null;
$eval_quality = $_POST['eval_quality'] ?? null;
$eval_courtesy = $_POST['eval_courtesy'] ?? null;
$eval_overall = $_POST['eval_overall'] ?? null;
$comments = $_POST['comments'] ?? '';

if ($user_id === 0 || $request_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

if (!$eval_response || !$eval_quality || !$eval_courtesy || !$eval_overall) {
    echo json_encode(['success' => false, 'message' => 'Please complete all evaluation fields']);
    exit;
}

// Check if survey already exists
$checkStmt = $conn->prepare("SELECT id FROM service_surveys WHERE service_request_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $request_id, $user_id);
$checkStmt->execute();
$result = $checkStmt->get_result();
$checkStmt->close();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Survey already submitted for this request']);
    exit;
}

// Insert survey
$stmt = $conn->prepare("INSERT INTO service_surveys (service_request_id, user_id, eval_response, eval_quality, eval_courtesy, eval_overall, comments, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iiiiiis", $request_id, $user_id, $eval_response, $eval_quality, $eval_courtesy, $eval_overall, $comments);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Survey submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

