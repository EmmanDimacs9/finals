<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
include '../logger.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

$formData = $input['formData'] ?? [];
$equipmentData = $input['equipmentData'] ?? [];
$categories = $input['categories'] ?? [];

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'SYSTEM';

try {
    // Check if preventive_maintenance_plans table exists, create if not
    $checkTable = $conn->query("SHOW TABLES LIKE 'preventive_maintenance_plans'");
    if ($checkTable->num_rows == 0) {
        $createTable = "CREATE TABLE IF NOT EXISTS preventive_maintenance_plans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            office_college VARCHAR(255),
            reference_no VARCHAR(255),
            effectivity_date DATE,
            revision_no VARCHAR(50),
            fy VARCHAR(50),
            prepared_by VARCHAR(255),
            prepared_date DATE,
            reviewed_by VARCHAR(255),
            reviewed_date DATE,
            approved_by VARCHAR(255),
            approved_date DATE,
            equipment_data TEXT,
            categories TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $conn->query($createTable);
    }
    
    // Insert the plan
    $stmt = $conn->prepare("INSERT INTO preventive_maintenance_plans (
        user_id, office_college, reference_no, effectivity_date, revision_no, fy,
        prepared_by, prepared_date, reviewed_by, reviewed_date, approved_by, approved_date,
        equipment_data, categories
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $office_college = $formData['office_college'] ?? '';
    $reference_no = $formData['reference_no'] ?? '';
    $effectivity_date = !empty($formData['effectivity_date']) ? $formData['effectivity_date'] : null;
    $revision_no = $formData['revision_no'] ?? '';
    $fy = $formData['fy'] ?? '';
    $prepared_by = $formData['prepared_by'] ?? '';
    $prepared_date = !empty($formData['prepared_date']) ? $formData['prepared_date'] : null;
    $reviewed_by = $formData['reviewed_by'] ?? '';
    $reviewed_date = !empty($formData['reviewed_date']) ? $formData['reviewed_date'] : null;
    $approved_by = $formData['approved_by'] ?? '';
    $approved_date = !empty($formData['approved_date']) ? $formData['approved_date'] : null;
    $equipment_data_json = json_encode($equipmentData);
    $categories_json = json_encode($categories);
    
    $stmt->bind_param("isssssssssssss",
        $user_id,
        $office_college,
        $reference_no,
        $effectivity_date,
        $revision_no,
        $fy,
        $prepared_by,
        $prepared_date,
        $reviewed_by,
        $reviewed_date,
        $approved_by,
        $approved_date,
        $equipment_data_json,
        $categories_json
    );
    
    if ($stmt->execute()) {
        $plan_id = $conn->insert_id;
        
        // Log the action
        logAdminAction($user_id, $user_name, "Saved Preventive Maintenance Plan", "Plan ID: $plan_id");
        
        echo json_encode([
            'success' => true,
            'message' => 'Plan saved successfully',
            'plan_id' => $plan_id
        ]);
    } else {
        throw new Exception('Failed to save plan: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>




