<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$equipment_info = null;
$service_request_info = null;
$maintenance_history = null;
$error = '';
$success = '';

/**
 * Search all equipment tables by asset_tag or id
 */
function findEquipment($conn, $qr_data) {
    $tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone'];
    foreach ($tables as $table) {
        // try by asset_tag
        $stmt = $conn->prepare("SELECT *, ? AS table_name FROM $table WHERE asset_tag = ?");
        $stmt->bind_param("ss", $table, $qr_data);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        }
        $stmt->close();

        // try by id if qr_data is numeric
        if (ctype_digit($qr_data)) {
            $id = (int)$qr_data;
            $stmt = $conn->prepare("SELECT *, ? AS table_name FROM $table WHERE id = ?");
            $stmt->bind_param("si", $table, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $stmt->close();
                return $row;
            }
            $stmt->close();
        }
    }
    return null;
}

// Handle QR code processing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'scan_qr':
            $qr_data = trim($_POST['qr_data'] ?? '');

            if (!empty($qr_data)) {
                // If QR data was Base64-encoded
                $decodedText = base64_decode($qr_data, true);
                if ($decodedText !== false && !empty($decodedText)) {
                    $qr_data = $decodedText;
                }

                // If QR contains JSON (from generated QR)
                $decoded = json_decode($qr_data, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['asset_tag'])) {
                    $equipment_info = $decoded;
                    $equipment_info['table_name'] = $decoded['table_name'] ?? '';
                    $success = '✅ Equipment loaded from QR code (JSON data)';
                    
                    // Fetch service request info (support level) for this equipment
                    $asset_tag = $equipment_info['asset_tag'] ?? '';
                    if ($asset_tag) {
                        $srQuery = "SELECT sr.*, u.full_name as technician_name 
                                   FROM service_requests sr 
                                   LEFT JOIN users u ON sr.technician_id = u.id 
                                   WHERE sr.equipment = ? OR sr.equipment LIKE ? 
                                   ORDER BY sr.created_at DESC 
                                   LIMIT 1";
                        $srStmt = $conn->prepare($srQuery);
                        $searchPattern = '%' . $asset_tag . '%';
                        $srStmt->bind_param("ss", $asset_tag, $searchPattern);
                        $srStmt->execute();
                        $srResult = $srStmt->get_result();
                        if ($srResult && $srResult->num_rows > 0) {
                            $service_request_info = $srResult->fetch_assoc();
                        }
                        $srStmt->close();
                    }
                    
                    // Fetch last maintenance record for this equipment
                    $equipment_id = $equipment_info['id'] ?? null;
                    $table_name = $equipment_info['table_name'] ?? '';
                    if ($equipment_id && $table_name) {
                        $maintQuery = "SELECT mr.*, u.full_name as technician_name 
                                      FROM maintenance_records mr 
                                      LEFT JOIN users u ON mr.technician_id = u.id 
                                      WHERE mr.equipment_id = ? AND mr.equipment_type = ? 
                                      ORDER BY mr.created_at DESC 
                                      LIMIT 1";
                        $maintStmt = $conn->prepare($maintQuery);
                        $maintStmt->bind_param("is", $equipment_id, $table_name);
                        $maintStmt->execute();
                        $maintResult = $maintStmt->get_result();
                        if ($maintResult && $maintResult->num_rows > 0) {
                            $maintenance_history = $maintResult->fetch_assoc();
                        }
                        $maintStmt->close();
                    }
                } else {
                    // Otherwise, search across all tables
                    $equipment_info = findEquipment($conn, $qr_data);
                    if ($equipment_info) {
                        $success = '✅ Equipment found in ' . htmlspecialchars($equipment_info['table_name']) . ' table';
                        
                        // Fetch service request info (support level) for this equipment
                        $asset_tag = $equipment_info['asset_tag'] ?? '';
                        if ($asset_tag) {
                            $srQuery = "SELECT sr.*, u.full_name as technician_name 
                                       FROM service_requests sr 
                                       LEFT JOIN users u ON sr.technician_id = u.id 
                                       WHERE sr.equipment = ? OR sr.equipment LIKE ? 
                                       ORDER BY sr.created_at DESC 
                                       LIMIT 1";
                            $srStmt = $conn->prepare($srQuery);
                            $searchPattern = '%' . $asset_tag . '%';
                            $srStmt->bind_param("ss", $asset_tag, $searchPattern);
                            $srStmt->execute();
                            $srResult = $srStmt->get_result();
                            if ($srResult && $srResult->num_rows > 0) {
                                $service_request_info = $srResult->fetch_assoc();
                            }
                            $srStmt->close();
                        }
                        
                        // Fetch last maintenance record for this equipment
                        $equipment_id = $equipment_info['id'] ?? null;
                        $table_name = $equipment_info['table_name'] ?? '';
                        if ($equipment_id && $table_name) {
                            $maintQuery = "SELECT mr.*, u.full_name as technician_name 
                                          FROM maintenance_records mr 
                                          LEFT JOIN users u ON mr.technician_id = u.id 
                                          WHERE mr.equipment_id = ? AND mr.equipment_type = ? 
                                          ORDER BY mr.created_at DESC 
                                          LIMIT 1";
                            $maintStmt = $conn->prepare($maintQuery);
                            $maintStmt->bind_param("is", $equipment_id, $table_name);
                            $maintStmt->execute();
                            $maintResult = $maintStmt->get_result();
                            if ($maintResult && $maintResult->num_rows > 0) {
                                $maintenance_history = $maintResult->fetch_assoc();
                            }
                            $maintStmt->close();
                        }
                    } else {
                        $error = '❌ No equipment found with this QR code.';
                    }
                }
            } else {
                $error = '⚠️ Please enter QR code data.';
            }
            break;

    }
}

$page_title = 'QR Code Scanner';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-3" style="color: #212529; font-weight: 700;">
                <i class="fas fa-qrcode text-danger"></i> QR Code Scanner & Generator
            </h2>
            <p class="text-muted mb-4">
                <i class="fas fa-info-circle"></i> Scan QR codes to view equipment information, service history, and maintenance records
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- QR Code Input Methods -->
                <div class="col-md-6">
                    <!-- Scanner -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-camera"></i> Scan QR Code</h5>
                        </div>
                        <div class="card-body text-center">
                            <div id="qr-reader" style="width: 100%; max-width: 400px; margin: 0 auto; display: none;"></div>
                            
                            <button id="start-scan-btn" class="btn btn-danger mt-3">
                                <i class="fas fa-qrcode"></i> Start QR Scan
                            </button>
                            
                            <button id="stop-scan-btn" class="btn btn-secondary mt-3" style="display: none;">
                                <i class="fas fa-stop"></i> Stop Scan
                            </button>
                            
                            <p class="text-muted small mt-2">Position the QR code within the scanner frame</p>
                        </div>
                    </div>


                    <!-- Manual Entry -->
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-keyboard"></i> Manual Entry</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="scan_qr">
                                <div class="mb-3">
                                    <label class="form-label">Enter QR Code Data</label>
                                    <textarea class="form-control" name="qr_data" rows="3" 
                                              placeholder="Paste or type QR code data here..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search Equipment
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Equipment Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="fas fa-info-circle"></i> Equipment Information</h5>
                            <?php if ($equipment_info): ?>
                                <span class="badge bg-success">Equipment Found</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if ($equipment_info): ?>
                                <div class="equipment-details">
                                    <div class="row">
                                        <?php 
                                        $important_fields = ['id', 'asset_tag', 'property_equipment', 'department_office', 'department', 'assigned_person', 'location', 'model', 'processor', 'ram', 'hard_drive', 'operating_system', 'inventory_item_no'];
                                        $other_fields = [];
                                        
                                        foreach ($equipment_info as $key => $val): 
                                            if ($key === 'table_name') continue;
                                            
                                            if (in_array($key, $important_fields) && !empty($val)) {
                                                $other_fields[$key] = $val;
                                            }
                                        endforeach;
                                        
                                        // Display important fields
                                        foreach ($important_fields as $field):
                                            if (isset($equipment_info[$field]) && !empty($equipment_info[$field])):
                                        ?>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted"><?php echo ucwords(str_replace('_', ' ', $field)); ?>:</small>
                                                <div class="fw-bold"><?php echo htmlspecialchars($equipment_info[$field]); ?></div>
                                            </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>

                                    <!-- Service Request Info (Support Level) -->
                                    <?php if ($service_request_info): ?>
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-tools text-primary"></i> Last Service Request</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Support Level:</small>
                                                <div class="fw-bold">
                                                    <?php 
                                                    $level = $service_request_info['support_level'] ?? 'N/A';
                                                    $badgeClass = '';
                                                    if ($level === 'L1') $badgeClass = 'bg-info';
                                                    elseif ($level === 'L2') $badgeClass = 'bg-primary';
                                                    elseif ($level === 'L3') $badgeClass = 'bg-warning';
                                                    elseif ($level === 'L4') $badgeClass = 'bg-danger';
                                                    else $badgeClass = 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($level); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Processing Time:</small>
                                                <div class="fw-bold"><?php echo htmlspecialchars($service_request_info['processing_time'] ?? 'N/A'); ?></div>
                                            </div>
                                            <?php if ($service_request_info['technician_name']): ?>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Technician:</small>
                                                <div class="fw-bold"><?php echo htmlspecialchars($service_request_info['technician_name']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($service_request_info['accomplishment']): ?>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Work Done:</small>
                                                <div class="small"><?php echo htmlspecialchars($service_request_info['accomplishment']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($service_request_info['remarks']): ?>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Remarks:</small>
                                                <div class="small"><?php echo htmlspecialchars($service_request_info['remarks']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Maintenance History -->
                                    <?php if ($maintenance_history): ?>
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-wrench text-success"></i> Last Maintenance</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Type:</small>
                                                <div class="fw-bold">
                                                    <?php 
                                                    $maintType = ucfirst($maintenance_history['maintenance_type'] ?? 'N/A');
                                                    $maintBadge = '';
                                                    if ($maintenance_history['maintenance_type'] === 'preventive') $maintBadge = 'bg-success';
                                                    elseif ($maintenance_history['maintenance_type'] === 'corrective') $maintBadge = 'bg-warning';
                                                    elseif ($maintenance_history['maintenance_type'] === 'upgrade') $maintBadge = 'bg-info';
                                                    else $maintBadge = 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $maintBadge; ?>"><?php echo htmlspecialchars($maintType); ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Status:</small>
                                                <div class="fw-bold">
                                                    <?php 
                                                    $status = ucfirst(str_replace('_', ' ', $maintenance_history['status'] ?? 'N/A'));
                                                    ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($status); ?></span>
                                                </div>
                                            </div>
                                            <?php if ($maintenance_history['technician_name']): ?>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Technician:</small>
                                                <div class="fw-bold"><?php echo htmlspecialchars($maintenance_history['technician_name']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($maintenance_history['description']): ?>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Action Taken:</small>
                                                <div class="small"><?php echo htmlspecialchars($maintenance_history['description']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($maintenance_history['remarks']): ?>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Remarks:</small>
                                                <div class="small"><?php echo htmlspecialchars($maintenance_history['remarks']); ?></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($maintenance_history['start_date']): ?>
                                            <div class="col-md-6 mb-2">
                                                <small class="text-muted">Date:</small>
                                                <div class="small"><?php echo date('M d, Y', strtotime($maintenance_history['start_date'])); ?></div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Editable Remarks Section -->
                                    <div class="mt-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-comment"></i> Remarks
                                        </label>
                                        <div class="input-group">
                                            <textarea 
                                                id="remarksTextarea" 
                                                class="form-control" 
                                                rows="3" 
                                                placeholder="Enter remarks for this equipment..."
                                                data-equipment-id="<?php echo $equipment_info['id']; ?>"
                                                data-table-name="<?php echo $equipment_info['table_name']; ?>"
                                            ><?php echo htmlspecialchars($equipment_info['remarks'] ?? ''); ?></textarea>
                                            <button 
                                                class="btn btn-outline-primary" 
                                                type="button" 
                                                id="saveRemarksBtn"
                                                onclick="updateRemarks()"
                                            >
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                        </div>
                                        <div id="remarksStatus" class="mt-2"></div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button class="btn btn-success" onclick="addToHistory(<?php echo $equipment_info['id']; ?>, '<?php echo $equipment_info['table_name']; ?>')">
                                            <i class="fas fa-history"></i> Add to History
                                        </button>
                                        <button class="btn btn-outline-info" onclick="refreshEquipmentInfo()">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-qrcode fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Equipment Selected</h5>
                                    <p class="text-muted">Scan a QR code, upload a QR file, or enter QR data to view equipment information.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let html5QrcodeScanner;
let isScanning = false;

function onScanSuccess(decodedText, decodedResult) {
    if (!decodedText || decodedText.trim() === "") {
        console.warn("⚠️ Empty QR detected, ignoring.");
        return;
    }
    console.log("✅ QR scanned:", decodedText);

    const safeValue = btoa(decodedText);

    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="scan_qr">
        <input type="hidden" name="qr_data" value="${safeValue}">
    `;
    document.body.appendChild(form);

    if (isScanning && html5QrcodeScanner) {
        html5QrcodeScanner.stop();
        isScanning = false;
        document.getElementById("qr-reader").style.display = "none";
        document.getElementById("start-scan-btn").style.display = "inline-block";
        document.getElementById("stop-scan-btn").style.display = "none";
    }

    form.submit();
}

function onScanFailure(error) {
    console.warn(`⚠️ QR scan error: ${error}`);
}

document.getElementById("start-scan-btn").addEventListener("click", () => {
    if (!isScanning) {
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        document.getElementById("qr-reader").style.display = "block";
        document.getElementById("start-scan-btn").style.display = "none";
        document.getElementById("stop-scan-btn").style.display = "inline-block";

        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).then(() => {
            isScanning = true;
        }).catch(err => {
            alert("❌ Unable to start scanner: " + err);
        });
    }
});

document.getElementById("stop-scan-btn").addEventListener("click", () => {
    if (isScanning && html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
            document.getElementById("qr-reader").style.display = "none";
            document.getElementById("start-scan-btn").style.display = "inline-block";
            document.getElementById("stop-scan-btn").style.display = "none";
        }).catch(err => {
            console.error("❌ Failed to stop scanner:", err);
        });
    }
});

function addToHistory(equipmentId, tableName) {
    fetch('add_to_history.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ equipment_id: equipmentId, table_name: tableName, action: 'qr_scan' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('✅ Equipment added to history!', 'success');
        } else {
            showAlert('❌ Failed to add to history: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        showAlert('❌ Network error occurred', 'danger');
    });
}

function updateRemarks() {
    const textarea = document.getElementById('remarksTextarea');
    const equipmentId = textarea.dataset.equipmentId;
    const tableName = textarea.dataset.tableName;
    const remarks = textarea.value.trim();
    
    const saveBtn = document.getElementById('saveRemarksBtn');
    const statusDiv = document.getElementById('remarksStatus');
    
    // Show loading state
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;
    
    fetch('update_remarks.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            equipment_id: equipmentId, 
            table_name: tableName, 
            remarks: remarks 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('✅ Remarks updated successfully!', 'success');
            statusDiv.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Last updated: ' + new Date().toLocaleTimeString() + '</small>';
        } else {
            showAlert('❌ Failed to update remarks: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(error => {
        console.error('❌ Error:', error);
        showAlert('❌ Network error occurred', 'danger');
    })
    .finally(() => {
        // Reset button state
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
        saveBtn.disabled = false;
    });
}

function refreshEquipmentInfo() {
    // Reload the page to refresh equipment info
    window.location.reload();
}

function showAlert(message, type) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<style>
.equipment-details {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    padding: 25px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.equipment-details:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.equipment-details p { 
    margin-bottom: 6px; 
}

#qr-reader { 
    border: 2px solid #dc3545; 
    border-radius: 10px; 
}

.card {
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.card-header {
    background: linear-gradient(135deg, #dc3545 0%, #343a40 100%);
    color: white;
    border-radius: 12px 12px 0 0 !important;
    border: none;
    font-weight: 600;
    padding: 15px 20px;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border-color: #007bff;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #ced4da;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.alert {
    border-radius: 8px;
    border: none;
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
    border-radius: 6px;
}

.text-muted {
    color: #6c757d !important;
}

.fw-bold {
    color: #495057;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .equipment-details {
        padding: 15px;
    }
    
    .card-header h5 {
        font-size: 1.1rem;
    }
    
    .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}
</style>

<?php require_once 'footer.php'; ?>
