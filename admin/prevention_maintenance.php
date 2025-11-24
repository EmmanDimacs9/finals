<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preventive Maintenance Plan - BSU ICT Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
        }
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.05rem;
            text-transform: uppercase;
            color: var(--secondary-color);
        }
        .maintenance-table th,
        .maintenance-table td {
            font-size: 0.85rem;
            text-align: center;
            vertical-align: middle;
            padding: 0.6rem;
        }
        .maintenance-table thead th {
            background-color: #f1f3f5;
            text-transform: uppercase;
        }
        .legend span {
            display: inline-block;
            margin-right: 1.5rem;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            height: 2rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
            <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU ICT System
            </a>
            <div class="navbar-nav ms-auto">
                <a href="profile.php" class="btn btn-light me-2"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" id="clearBtn"><i class="fas fa-eraser"></i> Clear</button>
                    <button class="btn btn-danger" id="savePlanBtn"><i class="fas fa-save"></i> Save Plan</button>
                    <button class="btn btn-outline-danger" id="exportPdfBtn"><i class="fas fa-file-pdf"></i> Export PDF</button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Office / College</label>
                            <input type="text" class="form-control" id="office_college" name="office_college" value="ICT Services">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Reference No.</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" value="BatStateU-DOC-AF-04">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Effectivity Date</label>
                            <input type="date" class="form-control" id="effectivity_date" name="effectivity_date">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Revision No.</label>
                            <input type="text" class="form-control" id="revision_no" name="revision_no" value="02">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">FY</label>
                            <input type="text" class="form-control" id="fy" name="fy" value="2025">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php
                    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    $desktopComputers = [];
                    $networkEquipment = [];
                    ?>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title">ICT Equipment - Desktop Computers</div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="addDesktopBtn">
                                <i class="fas fa-plus"></i> Add Equipment
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="bulkAddDesktopBtn">
                                <i class="fas fa-layer-group"></i> Bulk Add
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered maintenance-table" id="desktopTable">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 260px;">Equipment / Item</th>
                                    <?php foreach ($months as $month): ?>
                                        <th><?= $month ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="desktopTableBody">
                                <tr id="desktopEmptyRow">
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="section-title">ICT Equipment - Network Devices</div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="addNetworkBtn">
                                <i class="fas fa-plus"></i> Add Equipment
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="bulkAddNetworkBtn">
                                <i class="fas fa-layer-group"></i> Bulk Add
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered maintenance-table" id="networkTable">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 260px;">Equipment / Item</th>
                                    <?php foreach ($months as $month): ?>
                                        <th><?= $month ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody id="networkTableBody">
                                <tr id="networkEmptyRow">
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="legend mb-4">
                        <strong class="section-title me-3">Legend:</strong>
                        <span>M - Monthly</span>
                        <span>Q - Quarterly</span>
                        <span>SA - Semi Annually</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Prepared by</label>
                            <input type="text" class="form-control" id="prepared_by" name="prepared_by">
                            <small class="text-muted">ICT Services Staff</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50" id="prepared_date" name="prepared_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Reviewed by</label>
                            <input type="text" class="form-control" id="reviewed_by" name="reviewed_by">
                            <small class="text-muted">Head, ICT Services</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50" id="reviewed_date" name="reviewed_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Approved by</label>
                            <input type="text" class="form-control" id="approved_by" name="approved_by">
                            <small class="text-muted">Vice Chancellor for Development and External Affairs</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50" id="approved_date" name="approved_date">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEquipmentModalLabel">Add Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addEquipmentForm">
                    <div class="mb-3">
                        <label for="equipmentAssetTag" class="form-label">Equipment (Asset Tag)</label>
                        <select class="form-select" id="equipmentAssetTag" name="asset_tag" required>
                            <option value="">Select Asset Tag...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="equipmentLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="equipmentLocation" name="location" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="equipmentFrequency" class="form-label">Maintenance Frequency</label>
                        <select class="form-select" id="equipmentFrequency" name="frequency">
                            <option value="">None (Manual Selection)</option>
                            <option value="M">M - Monthly</option>
                            <option value="Q">Q - Quarterly</option>
                            <option value="SA">SA - Semi Annually</option>
                        </select>
                        <small class="text-muted">Select a frequency to auto-fill months, or leave as "None" to select manually</small>
                    </div>
                    <div class="mb-3">
                        <label for="equipmentStartMonth" class="form-label">Start Month</label>
                        <select class="form-select" id="equipmentStartMonth" name="start_month">
                            <option value="">Select Start Month...</option>
                            <option value="Jan">January</option>
                            <option value="Feb">February</option>
                            <option value="Mar">March</option>
                            <option value="Apr">April</option>
                            <option value="May">May</option>
                            <option value="Jun">June</option>
                            <option value="Jul">July</option>
                            <option value="Aug">August</option>
                            <option value="Sep">September</option>
                            <option value="Oct">October</option>
                            <option value="Nov">November</option>
                            <option value="Dec">December</option>
                        </select>
                        <small class="text-muted">Select the starting month for maintenance schedule (required if frequency is selected)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddEquipment">Add Equipment</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Equipment Modal -->
<div class="modal fade" id="bulkAddEquipmentModal" tabindex="-1" aria-labelledby="bulkAddEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkAddEquipmentModalLabel">Bulk Add Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkAddEquipmentForm">
                    <div class="mb-3">
                        <label for="bulkAssetTags" class="form-label">Select Asset Tags</label>
                        <select class="form-select" id="bulkAssetTags" name="asset_tags" multiple size="10" required>
                            <option value="" disabled>Loading asset tags...</option>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple asset tags. The system will automatically fetch locations for each selected asset tag.</small>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBulkTags">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBulkTags">Deselect All</button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bulkFrequency" class="form-label">Maintenance Frequency</label>
                            <select class="form-select" id="bulkFrequency" name="frequency">
                                <option value="">None (Manual Selection)</option>
                                <option value="M">M - Monthly</option>
                                <option value="Q">Q - Quarterly</option>
                                <option value="SA">SA - Semi Annually</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bulkStartMonth" class="form-label">Start Month</label>
                            <select class="form-select" id="bulkStartMonth" name="start_month">
                                <option value="">Select Start Month...</option>
                                <option value="Jan">January</option>
                                <option value="Feb">February</option>
                                <option value="Mar">March</option>
                                <option value="Apr">April</option>
                                <option value="May">May</option>
                                <option value="Jun">June</option>
                                <option value="Jul">July</option>
                                <option value="Aug">August</option>
                                <option value="Sep">September</option>
                                <option value="Oct">October</option>
                                <option value="Nov">November</option>
                                <option value="Dec">December</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> The system will automatically fetch locations for each asset tag. If a frequency is selected, all equipment will use the same schedule.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmBulkAddEquipment">
                    <i class="fas fa-layer-group"></i> Add All Equipment
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('click', function(e) {
    const logoutLink = e.target.closest('a[href="logout.php"]');
    if (!logoutLink) return;
    e.preventDefault();
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = logoutLink.href;
    }
});

// Add Equipment functionality
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
let currentTableType = ''; // 'desktop' or 'network'
let equipmentData = {}; // Store equipment data for dropdown

// Initialize modal
const addEquipmentModal = new bootstrap.Modal(document.getElementById('addEquipmentModal'));
const equipmentAssetTagSelect = document.getElementById('equipmentAssetTag');
const equipmentLocationInput = document.getElementById('equipmentLocation');

// Load equipment data when modal opens
function loadEquipmentData(category) {
    equipmentAssetTagSelect.innerHTML = '<option value="">Loading...</option>';
    equipmentLocationInput.value = '';
    
    fetch(`get_all_equipment.php?category=${category}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.equipment) {
                equipmentData = {};
                equipmentAssetTagSelect.innerHTML = '<option value="">Select Asset Tag...</option>';
                
                data.equipment.forEach(item => {
                    equipmentData[item.asset_tag] = item;
                    const option = document.createElement('option');
                    option.value = item.asset_tag;
                    option.textContent = item.asset_tag;
                    equipmentAssetTagSelect.appendChild(option);
                });
            } else {
                equipmentAssetTagSelect.innerHTML = '<option value="">No equipment found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading equipment:', error);
            equipmentAssetTagSelect.innerHTML = '<option value="">Error loading equipment</option>';
        });
}

// Auto-fill location when asset tag is selected
equipmentAssetTagSelect.addEventListener('change', function() {
    const selectedAssetTag = this.value;
    if (selectedAssetTag && equipmentData[selectedAssetTag]) {
        equipmentLocationInput.value = equipmentData[selectedAssetTag].location || '';
    } else {
        equipmentLocationInput.value = '';
    }
});

// Add equipment row to table
function addEquipmentRowToTable(tableBodyId, emptyRowId, assetTag, location, frequency = '', startMonth = '') {
    const tbody = document.getElementById(tableBodyId);
    const emptyRow = document.getElementById(emptyRowId);
    
    // Remove empty row if it exists
    if (emptyRow) {
        emptyRow.remove();
    }
    
    // Determine which months should be selected based on frequency and start month
    let selectedMonths = {};
    
    if (frequency && startMonth) {
        const startIndex = months.indexOf(startMonth);
        
        if (startIndex === -1) {
            // Invalid start month, don't auto-fill
        } else if (frequency === 'M') {
            // Monthly - all months starting from start month
            months.forEach(month => {
                selectedMonths[month] = 'M';
            });
        } else if (frequency === 'Q') {
            // Quarterly - every 3 months starting from start month
            for (let i = 0; i < 12; i++) {
                const monthIndex = (startIndex + (i * 3)) % 12;
                selectedMonths[months[monthIndex]] = 'Q';
            }
        } else if (frequency === 'SA') {
            // Semi-Annually - every 6 months starting from start month
            for (let i = 0; i < 2; i++) {
                const monthIndex = (startIndex + (i * 6)) % 12;
                selectedMonths[months[monthIndex]] = 'SA';
            }
        }
    }
    
    // Create new row
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-start">
            <div class="d-flex align-items-center gap-2">
                <div>
                    <div><strong>${assetTag}</strong></div>
                    <small class="text-muted">${location || 'No location'}</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeEquipmentRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
        ${months.map(month => {
            const selected = selectedMonths[month] || '';
            return `
            <td>
                <select class="form-select form-select-sm">
                    <option value="" ${selected === '' ? 'selected' : ''}></option>
                    <option value="M" ${selected === 'M' ? 'selected' : ''}>M</option>
                    <option value="Q" ${selected === 'Q' ? 'selected' : ''}>Q</option>
                    <option value="SA" ${selected === 'SA' ? 'selected' : ''}>SA</option>
                </select>
            </td>
        `;
        }).join('')}
    `;
    
    tbody.appendChild(row);
}

function removeEquipmentRow(btn) {
    const row = btn.closest('tr');
    const tbody = row.closest('tbody');
    row.remove();
    
    // Show empty message if no rows left
    if (tbody.children.length === 0) {
        const emptyRow = document.createElement('tr');
        const tableId = tbody.id;
        const emptyRowId = tableId === 'desktopTableBody' ? 'desktopEmptyRow' : 'networkEmptyRow';
        emptyRow.id = emptyRowId;
        emptyRow.innerHTML = `
            <td colspan="13" class="text-center text-muted py-4">
                <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }
}

// Initialize bulk add modal
const bulkAddEquipmentModal = new bootstrap.Modal(document.getElementById('bulkAddEquipmentModal'));
const bulkAssetTagsSelect = document.getElementById('bulkAssetTags');
let bulkEquipmentData = {}; // Store equipment data for bulk dropdown

// Load equipment data for bulk dropdown
function loadBulkEquipmentData(category) {
    bulkAssetTagsSelect.innerHTML = '<option value="" disabled>Loading asset tags...</option>';
    
    fetch(`get_all_equipment.php?category=${category}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.equipment) {
                bulkEquipmentData = {};
                bulkAssetTagsSelect.innerHTML = '';
                
                data.equipment.forEach(item => {
                    bulkEquipmentData[item.asset_tag] = item;
                    const option = document.createElement('option');
                    option.value = item.asset_tag;
                    option.textContent = `${item.asset_tag}${item.location ? ' - ' + item.location : ''}`;
                    bulkAssetTagsSelect.appendChild(option);
                });
                
                if (data.equipment.length === 0) {
                    bulkAssetTagsSelect.innerHTML = '<option value="" disabled>No equipment found</option>';
                }
            } else {
                bulkAssetTagsSelect.innerHTML = '<option value="" disabled>No equipment found</option>';
            }
        })
        .catch(error => {
            console.error('Error loading equipment:', error);
            bulkAssetTagsSelect.innerHTML = '<option value="" disabled>Error loading equipment</option>';
        });
}

// Select All / Deselect All buttons
document.getElementById('selectAllBulkTags').addEventListener('click', function() {
    Array.from(bulkAssetTagsSelect.options).forEach(option => {
        if (option.value) {
            option.selected = true;
        }
    });
});

document.getElementById('deselectAllBulkTags').addEventListener('click', function() {
    Array.from(bulkAssetTagsSelect.options).forEach(option => {
        option.selected = false;
    });
});

// Add event listeners for Add Equipment buttons
document.getElementById('addDesktopBtn').addEventListener('click', function() {
    currentTableType = 'desktop';
    loadEquipmentData('desktop');
    addEquipmentModal.show();
});

document.getElementById('addNetworkBtn').addEventListener('click', function() {
    currentTableType = 'network';
    loadEquipmentData('network');
    addEquipmentModal.show();
});

// Add event listeners for Bulk Add Equipment buttons
document.getElementById('bulkAddDesktopBtn').addEventListener('click', function() {
    currentTableType = 'desktop';
    loadBulkEquipmentData('desktop');
    bulkAddEquipmentModal.show();
});

document.getElementById('bulkAddNetworkBtn').addEventListener('click', function() {
    currentTableType = 'network';
    loadBulkEquipmentData('network');
    bulkAddEquipmentModal.show();
});

// Confirm add equipment
document.getElementById('confirmAddEquipment').addEventListener('click', function() {
    const assetTag = equipmentAssetTagSelect.value;
    const location = equipmentLocationInput.value;
    const frequency = document.getElementById('equipmentFrequency').value;
    const startMonth = document.getElementById('equipmentStartMonth').value;
    
    if (!assetTag) {
        alert('Please select an asset tag');
        return;
    }
    
    if (frequency && !startMonth) {
        alert('Please select a start month when choosing a frequency');
        return;
    }
    
    if (currentTableType === 'desktop') {
        addEquipmentRowToTable('desktopTableBody', 'desktopEmptyRow', assetTag, location, frequency, startMonth);
    } else if (currentTableType === 'network') {
        addEquipmentRowToTable('networkTableBody', 'networkEmptyRow', assetTag, location, frequency, startMonth);
    }
    
    // Reset form and close modal
    document.getElementById('addEquipmentForm').reset();
    equipmentLocationInput.value = '';
    addEquipmentModal.hide();
});

// Bulk Add Equipment functionality
document.getElementById('confirmBulkAddEquipment').addEventListener('click', function() {
    const selectedOptions = Array.from(bulkAssetTagsSelect.selectedOptions);
    const frequency = document.getElementById('bulkFrequency').value;
    const startMonth = document.getElementById('bulkStartMonth').value;
    
    if (selectedOptions.length === 0) {
        alert('Please select at least one asset tag');
        return;
    }
    
    if (frequency && !startMonth) {
        alert('Please select a start month when choosing a frequency');
        return;
    }
    
    // Get selected asset tags
    const assetTags = selectedOptions.map(option => option.value);
    
    // Determine target table
    const tableBodyId = currentTableType === 'desktop' ? 'desktopTableBody' : 'networkTableBody';
    const emptyRowId = currentTableType === 'desktop' ? 'desktopEmptyRow' : 'networkEmptyRow';
    
    // Remove empty row if it exists
    const emptyRow = document.getElementById(emptyRowId);
    if (emptyRow) {
        emptyRow.remove();
    }
    
    // Process each asset tag
    let processedCount = 0;
    
    // Process sequentially to avoid overwhelming the server
    async function processAssetTags() {
        for (const assetTag of assetTags) {
            try {
                // Get location from stored data or fetch if not available
                let location = '';
                if (bulkEquipmentData[assetTag]) {
                    location = bulkEquipmentData[assetTag].location || '';
                } else {
                    // Fallback: fetch from server
                    const response = await fetch(`get_equipment_location.php?asset_tag=${encodeURIComponent(assetTag)}`);
                    const data = await response.json();
                    location = data.success ? (data.location || '') : '';
                }
                
                // Add equipment row
                addEquipmentRowToTable(tableBodyId, null, assetTag, location, frequency, startMonth);
                processedCount++;
            } catch (error) {
                console.error(`Error processing ${assetTag}:`, error);
                // Still add the row even if location fetch fails
                addEquipmentRowToTable(tableBodyId, null, assetTag, '', frequency, startMonth);
                processedCount++;
            }
        }
        
        // Show completion message
        alert(`Successfully added ${processedCount} equipment item(s).`);
        
        // Reset form and close modal
        document.getElementById('bulkAddEquipmentForm').reset();
        bulkAddEquipmentModal.hide();
    }
    
    processAssetTags();
});

// Helper function to collect form and equipment data
function collectFormData() {
    const formData = {
        office_college: document.getElementById('office_college').value || '',
        reference_no: document.getElementById('reference_no').value || '',
        effectivity_date: document.getElementById('effectivity_date').value || '',
        revision_no: document.getElementById('revision_no').value || '',
        fy: document.getElementById('fy').value || '',
        prepared_by: document.getElementById('prepared_by').value || '',
        prepared_date: document.getElementById('prepared_date').value || '',
        reviewed_by: document.getElementById('reviewed_by').value || '',
        reviewed_date: document.getElementById('reviewed_date').value || '',
        approved_by: document.getElementById('approved_by').value || '',
        approved_date: document.getElementById('approved_date').value || ''
    };

    // Collect equipment data from tables
    const equipmentData = {};
    const categories = [];

    // Desktop Computers table
    const desktopTableBody = document.getElementById('desktopTableBody');
    const desktopRows = desktopTableBody.querySelectorAll('tr');
    const desktopEquipment = [];
    
    desktopRows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (!firstCell || firstCell.colSpan === 13) return; // Skip empty message row
        
        const assetTagElement = firstCell.querySelector('strong');
        if (!assetTagElement) return;
        
        const equipmentName = assetTagElement.textContent.trim();
        if (!equipmentName) return;
        
        const schedule = {};
        const selects = row.querySelectorAll('select');
        selects.forEach((select, index) => {
            if (index < months.length) {
                schedule[months[index]] = select.value || '';
            }
        });
        
        desktopEquipment.push({
            name: equipmentName,
            schedule: schedule
        });
    });

    if (desktopEquipment.length > 0) {
        equipmentData['desktopcomputers'] = desktopEquipment;
        categories.push({ id: 'desktopcomputers', name: 'Desktop Computers' });
    }

    // Network Devices table
    const networkTableBody = document.getElementById('networkTableBody');
    const networkRows = networkTableBody.querySelectorAll('tr');
    const networkEquipment = [];
    
    networkRows.forEach(row => {
        const firstCell = row.querySelector('td:first-child');
        if (!firstCell || firstCell.colSpan === 13) return; // Skip empty message row
        
        const assetTagElement = firstCell.querySelector('strong');
        if (!assetTagElement) return;
        
        const equipmentName = assetTagElement.textContent.trim();
        if (!equipmentName) return;
        
        const schedule = {};
        const selects = row.querySelectorAll('select');
        selects.forEach((select, index) => {
            if (index < months.length) {
                schedule[months[index]] = select.value || '';
            }
        });
        
        networkEquipment.push({
            name: equipmentName,
            schedule: schedule
        });
    });

    if (networkEquipment.length > 0) {
        equipmentData['networkdevices'] = networkEquipment;
        categories.push({ id: 'networkdevices', name: 'Network Devices' });
    }

    return { formData, equipmentData, categories };
}

// Clear button functionality
document.getElementById('clearBtn').addEventListener('click', function() {
    if (!confirm('Are you sure you want to clear all data? This action cannot be undone.')) {
        return;
    }
    
    // Clear form fields
    document.getElementById('office_college').value = 'ICT Services';
    document.getElementById('reference_no').value = 'BatStateU-DOC-AF-04';
    document.getElementById('effectivity_date').value = '';
    document.getElementById('revision_no').value = '02';
    document.getElementById('fy').value = '2025';
    document.getElementById('prepared_by').value = '';
    document.getElementById('prepared_date').value = '';
    document.getElementById('reviewed_by').value = '';
    document.getElementById('reviewed_date').value = '';
    document.getElementById('approved_by').value = '';
    document.getElementById('approved_date').value = '';
    
    // Clear equipment tables
    const desktopTableBody = document.getElementById('desktopTableBody');
    const networkTableBody = document.getElementById('networkTableBody');
    
    desktopTableBody.innerHTML = `
        <tr id="desktopEmptyRow">
            <td colspan="13" class="text-center text-muted py-4">
                <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
            </td>
        </tr>
    `;
    
    networkTableBody.innerHTML = `
        <tr id="networkEmptyRow">
            <td colspan="13" class="text-center text-muted py-4">
                <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
            </td>
        </tr>
    `;
    
    alert('All data has been cleared.');
});

// Save Plan button functionality
document.getElementById('savePlanBtn').addEventListener('click', function() {
    const { formData, equipmentData, categories } = collectFormData();
    
    // Validate that there's at least some equipment
    if (Object.keys(equipmentData).length === 0) {
        alert('Please add at least one equipment item before saving the plan.');
        return;
    }
    
    // Show loading state
    const saveBtn = this;
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    // Send data to server
    fetch('save_preventive_plan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            formData: formData,
            equipmentData: equipmentData,
            categories: categories
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Plan saved successfully!');
        } else {
            alert('Failed to save plan: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving plan:', error);
        alert('Error saving plan. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
});

// Export PDF functionality
document.getElementById('exportPdfBtn').addEventListener('click', function() {
    const { formData, equipmentData, categories } = collectFormData();
    
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'generate_preventive_report.php';
    form.style.display = 'none';

    // Add form fields
    Object.keys(formData).forEach(key => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = formData[key];
        form.appendChild(input);
    });

    // Add equipment data as JSON
    const equipmentInput = document.createElement('input');
    equipmentInput.type = 'hidden';
    equipmentInput.name = 'equipment_data';
    equipmentInput.value = JSON.stringify(equipmentData);
    form.appendChild(equipmentInput);

    // Add categories as JSON
    const categoriesInput = document.createElement('input');
    categoriesInput.type = 'hidden';
    categoriesInput.name = 'categories';
    categoriesInput.value = JSON.stringify(categories);
    form.appendChild(categoriesInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});
</script>
</body>
</html>

