<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check login
requireLogin();

// Function to check if asset tag already exists across all equipment tables
function checkAssetTagExists($conn, $asset_tag, $exclude_table = null, $exclude_id = null) {
    $tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone', 'equipment'];
    
    foreach ($tables as $table) {
        // Skip excluded table/id for edit operations
        if ($exclude_table && $exclude_table === $table && $exclude_id) {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE asset_tag = ? AND id != ?");
            $stmt->bind_param("si", $asset_tag, $exclude_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM $table WHERE asset_tag = ?");
            $stmt->bind_param("s", $asset_tag);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return true; // Asset tag exists
        }
        $stmt->close();
    }
    return false; // Asset tag is unique
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $asset_tag = $_POST['asset_tag'];
    
    // Check for duplicate asset tag
    if (!empty($asset_tag) && checkAssetTagExists($conn, $asset_tag)) {
        $message = "❌ Error: Asset tag '$asset_tag' already exists. Please use a unique asset tag.";
    } else {
        $assigned_person = $_POST['assigned_person'];
    $location = $_POST['location'];
    $remarks = $_POST['remarks'];
    $date_acquired = $_POST['date_acquired'];
    $unit_price = $_POST['unit_price'];

    // Determine table
    $tableMap = [
        'desktop' => 'desktop',
        'laptop' => 'laptops',
        'printer' => 'printers',
        'accesspoint' => 'accesspoint',
        'switch' => 'switch',
        'telephone' => 'telephone'
    ];

    if (isset($tableMap[$type])) {
        $table = $tableMap[$type];

        $stmt = $conn->prepare("INSERT INTO $table 
            (asset_tag, assigned_person, location, remarks, date_acquired, unit_price) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssd", $asset_tag, $assigned_person, $location, $remarks, $date_acquired, $unit_price);

        if ($stmt->execute()) {
            header("Location: equipment.php?added=1");
            exit();
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    } else {
        $message = "Invalid equipment type.";
    }
    } // Close validation else block
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Equipment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow p-4">
    <h2 class="mb-4"><i class="fas fa-plus"></i> Add Equipment</h2>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Equipment Type</label>
        <select name="type" class="form-select" required>
          <option value="">-- Select Type --</option>
          <option value="desktop">Desktop</option>
          <option value="laptop">Laptop</option>
          <option value="printer">Printer</option>
          <option value="accesspoint">Access Point</option>
          <option value="switch">Switch</option>
          <option value="telephone">Telephone</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Asset Tag</label>
        <input type="text" name="asset_tag" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Assigned Person</label>
        <input type="text" name="assigned_person" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Date Acquired</label>
        <input type="date" name="date_acquired" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Unit Price</label>
        <input type="number" step="0.01" name="unit_price" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control"></textarea>
      </div>

      <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Save</button>
      <a href="equipment.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
