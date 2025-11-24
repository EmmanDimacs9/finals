<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../../landing.php');
    exit();
}

$page_title = 'Preventive Maintenance Checklist';
require_once '../../technician/header.php';

// Adjust asset paths because this file lives outside the technician directory
$technicianBasePath = '../../technician/';
$assetBasePath = '../../';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const techBase = '<?php echo $technicianBasePath; ?>';
    const assetBase = '<?php echo $assetBasePath; ?>';

    const headerBrand = document.querySelector('.header-brand');
    if (headerBrand) {
        headerBrand.setAttribute('href', techBase + 'kanban.php');
        const logoImg = headerBrand.querySelector('img');
        if (logoImg) {
            logoImg.setAttribute('src', assetBase + 'images/User icon.png');
        }
    }

    document.querySelectorAll('.footer-nav .nav-item').forEach(function(link) {
        const href = link.getAttribute('href');
        if (!href || href.startsWith('http') || href.startsWith(techBase)) {
            return;
        }
        link.setAttribute('href', techBase + href);
    });
});
</script>
<?php

// Fetch equipment from database
$equipment_list = [];
$equipment_seen = []; // Track unique asset tags to prevent duplicates
$tables = ['desktop', 'laptops', 'printers', 'accesspoint', 'switch', 'telephone'];

foreach ($tables as $table) {
    // Escape table name
    $table_escaped = $conn->real_escape_string($table);
    
    // Build query - select only columns that definitely exist
    $query = "SELECT asset_tag, location FROM $table_escaped 
              WHERE asset_tag IS NOT NULL AND asset_tag != '' 
              ORDER BY asset_tag";
    
    $result = $conn->query($query);
    
    // Check if query was successful
    if ($result !== false && $result) {
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['asset_tag'])) {
                $asset_tag = trim($row['asset_tag']);
                
                // Only add if we haven't seen this asset_tag before
                if (!isset($equipment_seen[$asset_tag])) {
                    $equipment_seen[$asset_tag] = true;
                    $equipment_list[] = [
                        'asset_tag' => $asset_tag,
                        'location' => $row['location'] ?? '',
                        'department' => '' // Department not always available, leave empty
                    ];
                }
            }
        }
        if (method_exists($result, 'free')) {
            $result->free();
        }
    } else {
        // Log error for debugging (optional)
        // error_log("Query failed for table $table: " . $conn->error);
    }
}

// Sort equipment list by asset_tag for better organization
usort($equipment_list, function($a, $b) {
    return strcmp($a['asset_tag'], $b['asset_tag']);
});
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-clipboard-check text-danger"></i> Preventive Maintenance Checklist
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Fill out the preventive maintenance checklist form
                    </p>
                </div>
            </div>

            <form id="preventiveForm" method="POST" action="preventiveChecklistPDF.php" target="_blank">
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><strong>Office/College:</strong></label>
                                <input type="text" class="form-control" name="office" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>FY (Fiscal Year):</strong></label>
                                <input type="text" class="form-control" name="fy" value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><strong>Effectivity Date:</strong></label>
                                <input type="date" class="form-control" name="effectivity_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-check-square"></i> Type of Equipment/Item</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="Vehicle" id="type_vehicle">
                                    <label class="form-check-label" for="type_vehicle">Vehicle</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="ACU" id="type_acu">
                                    <label class="form-check-label" for="type_acu">ACU</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="ICT Equipment" id="type_ict" checked>
                                    <label class="form-check-label" for="type_ict">ICT Equipment</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="Medical/Dental Equipment" id="type_medical">
                                    <label class="form-check-label" for="type_medical">Medical/Dental Equipment</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="Building" id="type_building">
                                    <label class="form-check-label" for="type_building">Building</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="EMU" id="type_emu">
                                    <label class="form-check-label" for="type_emu">EMU</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="Laboratory Equipment" id="type_lab">
                                    <label class="form-check-label" for="type_lab">Laboratory Equipment</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="equipment_type[]" value="Others" id="type_others">
                                    <label class="form-check-label" for="type_others">Others, specify:</label>
                                    <input type="text" class="form-control form-control-sm mt-1" name="equipment_type_other" placeholder="Specify">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Frequency</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="frequency[]" value="Monthly" id="freq_monthly">
                                    <label class="form-check-label" for="freq_monthly">Monthly</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="frequency[]" value="Quarterly" id="freq_quarterly">
                                    <label class="form-check-label" for="freq_quarterly">Quarterly</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="frequency[]" value="Semi-annually" id="freq_semi" checked>
                                    <label class="form-check-label" for="freq_semi">Semi-annually</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="frequency[]" value="Annually" id="freq_annually">
                                    <label class="form-check-label" for="freq_annually">Annually</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-tasks"></i> Activities & Equipment</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle"></i> Tick appropriate box with (✔) if checked item is ok. Put an (x) mark if item is not okay.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="activitiesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">ACTIVITIES</th>
                                        <th style="width: 50%;" id="equipmentHeader">EQUIPMENT NO./ ITEMS LOCATION</th>
                                        <th style="width: 20%;">REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1. Perform virus checkup</td>
                                        <td id="equipmentCells1"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>2. Update Software/Firmware</td>
                                        <td id="equipmentCells2"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>3. Uninstall unused software</td>
                                        <td id="equipmentCells3"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>4. Run Disk Cleanup</td>
                                        <td id="equipmentCells4"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>5. Defrag Hard Drive</td>
                                        <td id="equipmentCells5"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td>6. House Keeping</td>
                                        <td id="equipmentCells6"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">a) monitor, mouse, and keyboard</td>
                                        <td id="equipmentCells6a"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">b) printer, scanner</td>
                                        <td id="equipmentCells6b"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">c) router, switch, & access point</td>
                                        <td id="equipmentCells6c"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                    <tr>
                                        <td style="padding-left: 30px;">d) IP Phone, CCTV, View board</td>
                                        <td id="equipmentCells6d"></td>
                                        <td><textarea class="form-control form-control-sm" name="remarks[]" rows="1"></textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <label class="form-label"><strong>Select Equipment (max 12):</strong></label>
                            <select class="form-select" id="equipmentSelect" multiple size="8">
                                <?php foreach ($equipment_list as $eq): ?>
                                    <option value="<?php echo htmlspecialchars($eq['asset_tag']); ?>">
                                        <?php echo htmlspecialchars($eq['asset_tag'] . ' - ' . $eq['location']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addEquipment()">
                                <i class="fas fa-plus"></i> Add Selected Equipment
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="clearEquipment()">
                                <i class="fas fa-trash"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-signature"></i> Signatures</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Conducted by:</strong></label>
                                <input type="text" class="form-control" name="conducted_by" value="Ronald C. Tud" readonly style="background-color: #e9ecef;">
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Auto-filled</small>
                                <label class="form-label mt-2"><strong>Date:</strong></label>
                                <input type="date" class="form-control" name="conducted_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Verified by:</strong></label>
                                <input type="text" class="form-control" name="verified_by" required>
                                <label class="form-label mt-2"><strong>Date:</strong></label>
                                <input type="date" class="form-control" name="verified_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white" style="background-color: #dc3545;">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Corrective Action Record</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="correctiveTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Corrective Action</th>
                                        <th>Office Responsible</th>
                                        <th>Date Accomplished</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="date" class="form-control form-control-sm" name="corrective_date[]"></td>
                                        <td><textarea class="form-control form-control-sm" name="corrective_action[]" rows="1"></textarea></td>
                                        <td><input type="text" class="form-control form-control-sm" name="corrective_office[]"></td>
                                        <td><input type="date" class="form-control form-control-sm" name="corrective_accomplished[]"></td>
                                        <td><textarea class="form-control form-control-sm" name="corrective_remarks[]" rows="1"></textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCorrectiveRow()">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                    </div>
                </div>

                <input type="hidden" name="equipment_tags" id="equipmentTagsInput" value="">
                <input type="hidden" name="activities_data" id="activitiesDataInput" value="">
                
                <div class="d-flex justify-content-end gap-2 mb-4">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='../../technician/reports.php'">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" onclick="return prepareFormData(event)">
                        <i class="fas fa-file-pdf"></i> Generate PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let selectedEquipment = [];

function addEquipment() {
    const select = document.getElementById('equipmentSelect');
    const selected = Array.from(select.selectedOptions).map(opt => opt.value);
    
    selected.forEach(tag => {
        if (!selectedEquipment.includes(tag) && selectedEquipment.length < 12) {
            selectedEquipment.push(tag);
        }
    });
    
    updateEquipmentTable();
    select.selectedIndex = -1;
}

function clearEquipment() {
    selectedEquipment = [];
    updateEquipmentTable();
}

function updateEquipmentTable() {
    // Update header
    const header = document.getElementById('equipmentHeader');
    header.innerHTML = 'EQUIPMENT NO./ ITEMS LOCATION';
    if (selectedEquipment.length > 0) {
        header.colSpan = selectedEquipment.length;
    }
    
    // Update all activity rows
    for (let i = 1; i <= 6; i++) {
        updateEquipmentRow('equipmentCells' + i, i);
    }
    updateEquipmentRow('equipmentCells6a', '6a');
    updateEquipmentRow('equipmentCells6b', '6b');
    updateEquipmentRow('equipmentCells6c', '6c');
    updateEquipmentRow('equipmentCells6d', '6d');
}

function updateEquipmentRow(cellId, activityNum) {
    const cell = document.getElementById(cellId);
    if (!cell) return;
    
    if (selectedEquipment.length === 0) {
        cell.innerHTML = '';
        cell.colSpan = 1;
        return;
    }
    
    let html = '';
    selectedEquipment.forEach((tag, idx) => {
        html += `<div style="display: inline-block; width: ${100/selectedEquipment.length}%; padding: 2px; border-right: 1px solid #ddd;">
            <div style="font-size: 0.7rem; text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 2px; margin-bottom: 2px;">${tag}</div>
            <div style="text-align: center;">
                <input type="radio" name="activity_${activityNum}_${idx}" value="ok" style="margin: 0;">
                <label style="font-size: 0.7rem; margin-left: 2px;">✔</label>
                <input type="radio" name="activity_${activityNum}_${idx}" value="not_ok" style="margin-left: 5px; margin: 0;">
                <label style="font-size: 0.7rem; margin-left: 2px;">✗</label>
            </div>
        </div>`;
    });
    
    cell.innerHTML = html;
    cell.colSpan = selectedEquipment.length;
}

function prepareFormData(e) {
    e.preventDefault();
    
    // Store equipment tags
    document.getElementById('equipmentTagsInput').value = JSON.stringify(selectedEquipment);
    
    // Collect activities data
    const activitiesData = {};
    const form = document.getElementById('preventiveForm');
    const formData = new FormData(form);
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('activity_')) {
            const parts = key.split('_');
            if (parts.length >= 3) {
                const activity = parts[1];
                const eqIdx = parts[2];
                if (!activitiesData[activity]) {
                    activitiesData[activity] = {};
                }
                activitiesData[activity][eqIdx] = value;
            }
        }
    }
    
    document.getElementById('activitiesDataInput').value = JSON.stringify(activitiesData);
    
    // Submit the form
    form.submit();
    return false;
}

function addCorrectiveRow() {
    const tbody = document.getElementById('correctiveTable').querySelector('tbody');
    const row = tbody.insertRow();
    row.innerHTML = `
        <td><input type="date" class="form-control form-control-sm" name="corrective_date[]"></td>
        <td><textarea class="form-control form-control-sm" name="corrective_action[]" rows="1"></textarea></td>
        <td><input type="text" class="form-control form-control-sm" name="corrective_office[]"></td>
        <td><input type="date" class="form-control form-control-sm" name="corrective_accomplished[]"></td>
        <td><textarea class="form-control form-control-sm" name="corrective_remarks[]" rows="1"></textarea></td>
    `;
}

// Initialize
updateEquipmentTable();
</script>

<?php require_once '../../technician/footer.php'; ?>

