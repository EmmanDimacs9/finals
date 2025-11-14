<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

if (!isset($_GET['asset_tag'], $_GET['type'])) {
    die("❌ Invalid request.");
}

$asset_tag = $conn->real_escape_string($_GET['asset_tag']);
$type = strtolower($_GET['type']);

// Map to actual table names
$map = [
    'desktop' => 'desktop',
    'laptop' => 'laptops',
    'printer' => 'printers',
    'access point' => 'accesspoint',
    'switch' => 'switch',
    'telephone' => 'telephone'
];

if (!array_key_exists($type, $map)) {
    die("Invalid equipment type.");
}

$table = $map[$type];

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [];
    $params = [];
    $typestr = '';

    foreach ($_POST as $key => $value) {
        if ($key === 'asset_tag') continue; // don't update PK
        $fields[] = "`$key`=?";
        $params[] = $value;
        $typestr .= 's';
    }
    $params[] = $asset_tag;
    $typestr .= 's';

    $sql = "UPDATE `$table` SET " . implode(", ", $fields) . " WHERE asset_tag=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($typestr, ...$params);

    if ($stmt->execute()) {
        header("Location: equipment.php?updated=1");
        exit;
    } else {
        echo "❌ Update failed: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch current record
$res = $conn->query("SELECT * FROM `$table` WHERE asset_tag = '$asset_tag' LIMIT 1");
if ($res->num_rows === 0) {
    die("❌ Equipment not found.");
}
$data = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment - BSU Inventory Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
            --light-bg: #f8f9fa;
            --border-radius: 12px;
            --shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            padding: 2rem 0;
        }

        .edit-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin-bottom: 0;
        }

        .edit-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .edit-header .badge {
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }

        .form-container {
            background: white;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--light-bg);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }

        .form-section h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-control:read-only {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-color) 0%, #c82333 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .btn-cancel {
            background: #6c757d;
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }

        .field-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .required-field::after {
            content: " *";
            color: var(--primary-color);
            font-weight: bold;
        }

        .asset-tag-display {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .field-group {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .edit-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="edit-header">
                    <h1>
                        <div class="icon-wrapper">
                            <i class="fas fa-edit"></i>
                        </div>
                        Edit Equipment
                        <span class="badge"><?php echo ucfirst($type); ?></span>
                    </h1>
                </div>

                <!-- Asset Tag Display -->
                <div class="asset-tag-display">
                    <i class="fas fa-tag me-2"></i>
                    Asset Tag: <?php echo htmlspecialchars($data['asset_tag']); ?>
                </div>

                <!-- Form Container -->
                <div class="form-container">
  <form method="POST">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h5><i class="fas fa-info-circle"></i> Basic Information</h5>
                            <div class="field-group">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tag"></i>
                                        Asset Tag
                                    </label>
                                    <input type="text" class="form-control" name="asset_tag" value="<?php echo htmlspecialchars($data['asset_tag']); ?>" readonly>
                                </div>
                                
                                <?php if (isset($data['property_equipment'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-box"></i>
                                        Property Equipment
                                    </label>
                                    <input type="text" class="form-control" name="property_equipment" value="<?php echo htmlspecialchars($data['property_equipment']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['assigned_person'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Assigned Person
                                    </label>
                                    <input type="text" class="form-control" name="assigned_person" value="<?php echo htmlspecialchars($data['assigned_person']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['location'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Location
                                    </label>
                                    <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($data['location']); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Technical Specifications -->
                        <?php if (isset($data['hardware_specifications']) || isset($data['software_specifications']) || isset($data['processor']) || isset($data['ram']) || isset($data['gpu'])): ?>
                        <div class="form-section">
                            <h5><i class="fas fa-cogs"></i> Technical Specifications</h5>
                            <div class="field-group">
                                <?php if (isset($data['hardware_specifications'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-microchip"></i>
                                        Hardware Specifications
                                    </label>
                                    <textarea name="hardware_specifications" class="form-control"><?php echo htmlspecialchars($data['hardware_specifications']); ?></textarea>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['software_specifications'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-code"></i>
                                        Software Specifications
                                    </label>
                                    <textarea name="software_specifications" class="form-control"><?php echo htmlspecialchars($data['software_specifications']); ?></textarea>
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['processor'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-microchip"></i>
                                        Processor
                                    </label>
                                    <input type="text" class="form-control" name="processor" value="<?php echo htmlspecialchars($data['processor']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['ram'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-memory"></i>
                                        RAM
                                    </label>
                                    <input type="text" class="form-control" name="ram" value="<?php echo htmlspecialchars($data['ram']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['gpu'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-desktop"></i>
                                        GPU
                                    </label>
                                    <input type="text" class="form-control" name="gpu" value="<?php echo htmlspecialchars($data['gpu']); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Administrative Information -->
                        <div class="form-section">
                            <h5><i class="fas fa-clipboard-list"></i> Administrative Information</h5>
                            <div class="field-group">
                                <?php if (isset($data['date_acquired'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Date Acquired
                                    </label>
                                    <input type="date" class="form-control" name="date_acquired" value="<?php echo htmlspecialchars($data['date_acquired']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['unit_price'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign"></i>
                                        Unit Price
                                    </label>
                                    <input type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo htmlspecialchars($data['unit_price']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['useful_life'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i>
                                        Useful Life
                                    </label>
                                    <input type="text" class="form-control" name="useful_life" value="<?php echo htmlspecialchars($data['useful_life']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['inventory_item_no'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-list-ol"></i>
                                        Inventory Item No
                                    </label>
                                    <input type="text" class="form-control" name="inventory_item_no" value="<?php echo htmlspecialchars($data['inventory_item_no']); ?>">
                                </div>
                                <?php endif; ?>

                                <?php if (isset($data['high_value_ics_no'])): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-barcode"></i>
                                        High Value ICS No
                                    </label>
                                    <input type="text" class="form-control" name="high_value_ics_no" value="<?php echo htmlspecialchars($data['high_value_ics_no']); ?>">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status & Remarks -->
                        <div class="form-section">
                            <h5><i class="fas fa-comment-alt"></i> Status & Remarks</h5>
                            <div class="field-group">
                                <?php if (isset($data['remarks'])): ?>
      <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-sticky-note"></i>
                                        Remarks
                                    </label>
                                    <textarea name="remarks" class="form-control"><?php echo htmlspecialchars($data['remarks']); ?></textarea>
                                </div>
        <?php endif; ?>
      </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>
                                Save Changes
                            </button>
                            <a href="equipment.php" class="btn btn-cancel">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        </div>
  </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add form validation and enhanced user experience
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const saveBtn = document.querySelector('.btn-save');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
            
            // Loading state for save button
            form.addEventListener('submit', function() {
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                saveBtn.disabled = true;
            });
        });
    </script>
</body>
</html>
