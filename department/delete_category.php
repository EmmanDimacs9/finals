<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

if (empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'Category ID is required']);
    exit;
}

// Check if category exists
$check = $conn->prepare("SELECT id, name FROM equipment_categories WHERE id = ?");
$check->bind_param("i", $category_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Category not found']);
    exit;
}

// Delete the category
$stmt = $conn->prepare("DELETE FROM equipment_categories WHERE id = ?");
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Category deleted successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
}

$stmt->close();
?>

