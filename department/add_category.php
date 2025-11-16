<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = isset($_POST['name']) ? trim($conn->real_escape_string($_POST['name'])) : '';
$description = isset($_POST['description']) ? trim($conn->real_escape_string($_POST['description'])) : '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

// Check if category already exists
$check = $conn->prepare("SELECT id FROM equipment_categories WHERE name = ?");
$check->bind_param("s", $name);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Category already exists']);
    exit;
}

// Insert new category
$stmt = $conn->prepare("INSERT INTO equipment_categories (name, description) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $description);

if ($stmt->execute()) {
    $categoryId = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Category added successfully',
        'category' => [
            'id' => $categoryId,
            'name' => $name,
            'description' => $description
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add category']);
}

$stmt->close();
?>

