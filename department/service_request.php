<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

requireLogin();

// Fetch all unique offices/departments from equipment tables
$departmentsQuery = "
    SELECT DISTINCT department_office AS dept FROM desktop WHERE department_office IS NOT NULL AND department_office != ''
    UNION
    SELECT DISTINCT department AS dept FROM laptops WHERE department IS NOT NULL AND department != ''
    UNION
    SELECT DISTINCT department AS dept FROM printers WHERE department IS NOT NULL AND department != ''
    UNION
    SELECT DISTINCT department AS dept FROM accesspoint WHERE department IS NOT NULL AND department != ''
    UNION
    SELECT DISTINCT department AS dept FROM switch WHERE department IS NOT NULL AND department != ''
    UNION
    SELECT DISTINCT department AS dept FROM telephone WHERE department IS NOT NULL AND department != ''
    ORDER BY dept ASC
";
$departmentsResult = $conn->query($departmentsQuery);
$departments = [];
while ($row = $departmentsResult->fetch_assoc()) {
    $departments[] = $row['dept'];
}

// Fetch all unique locations from equipment tables
$locationsQuery = "
    SELECT DISTINCT location AS loc FROM desktop WHERE location IS NOT NULL AND location != ''
    UNION
    SELECT DISTINCT location AS loc FROM laptops WHERE location IS NOT NULL AND location != ''
    UNION
    SELECT DISTINCT location AS loc FROM printers WHERE location IS NOT NULL AND location != ''
    UNION
    SELECT DISTINCT location AS loc FROM accesspoint WHERE location IS NOT NULL AND location != ''
    UNION
    SELECT DISTINCT location AS loc FROM switch WHERE location IS NOT NULL AND location != ''
    UNION
    SELECT DISTINCT location AS loc FROM telephone WHERE location IS NOT NULL AND location != ''
    ORDER BY loc ASC
";
$locationsResult = $conn->query($locationsQuery);
$locations = [];
while ($row = $locationsResult->fetch_assoc()) {
    $locations[] = $row['loc'];
}

// Fetch technicians/staff
$techniciansQuery = "SELECT id, full_name FROM users WHERE role = 'technician' ORDER BY full_name ASC";
$techniciansResult = $conn->query($techniciansQuery);
$technicians = [];
while ($row = $techniciansResult->fetch_assoc()) {
    $technicians[] = $row;
}

// Generate ICT SRF No (format: YYYY-XXXXX)
$currentYear = date('Y');
$srfQuery = "SELECT MAX(CAST(SUBSTRING_INDEX(ict_srf_no, '-', -1) AS UNSIGNED)) as max_num 
             FROM service_requests 
             WHERE ict_srf_no LIKE ?";
$stmt = $conn->prepare($srfQuery);
$yearPattern = $currentYear . '-%';
$stmt->bind_param("s", $yearPattern);
$stmt->execute();
$srfResult = $stmt->get_result();
$maxNum = 0;
if ($srfRow = $srfResult->fetch_assoc()) {
    $maxNum = $srfRow['max_num'] ?? 0;
}
$nextSrfNum = str_pad($maxNum + 1, 5, '0', STR_PAD_LEFT);
$ictSrfNo = $currentYear . '-' . $nextSrfNum;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Service Request - BSU ICT Management System</title>
    <link rel="icon" href="/favicon.ico?v=8" type="image/png">
    <link rel="shortcut icon" href="/favicon.ico?v=8" type="image/png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.ico?v=8">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon.ico?v=8">
    <link rel="apple-touch-icon" href="/favicon.ico?v=8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
        }
        body { background-color: #f8f9fa; }
        .navbar { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .sidebar {
            background: #fff;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: var(--secondary-color);
            margin: 5px 10px;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
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
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="depdashboard.php">
            <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU ICT System
        </a>
        <div class="navbar-nav ms-auto">
            <a href="dep_profile.php" class="btn btn-light me-2"><i class="fas fa-user-circle"></i> Profile</a>
            <a href="../logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar py-4">
            <h5 class="text-center text-danger mb-3"><i class="fas fa-bars"></i> Navigation</h5>
            <div class="nav flex-column">
                <a href="depdashboard.php" class="nav-link"><i class="fas fa-user-tie"></i> Depart Head</a>
                <a href="service_request.php" class="nav-link active"><i class="fas fa-desktop"></i> Service Request</a>
                <a href="system_request.php" class="nav-link"><i class="fas fa-cog"></i> System Request</a>
                <a href="preventive_plan.php" class="nav-link"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-desktop"></i> ICT Service Request Form</h2>
                <div class="d-flex gap-2">
                    <button form="serviceRequestForm" type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generate PDF</button>
                    <button form="serviceRequestForm" type="button" class="btn btn-warning sendRequestBtn" data-form="ICT Service Request Form"><i class="fas fa-paper-plane"></i> Send Request</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="serviceRequestForm" class="needs-validation" method="POST" action="../PDFS/ICTRequestForm/ictServiceRequestPDF.php" target="_blank" novalidate>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Client Steps:</strong> Please fill out this form to request ICT service. A technician will receive your request, observe the equipment, and assign the appropriate support level.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Campus</label>
                                <input type="text" name="campus" class="form-control" value="Batangas State University - Lipa Campus" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ICT SRF No.</label>
                                <input type="text" name="ict_srf_no" id="ict_srf_no" class="form-control" value="<?= htmlspecialchars($ictSrfNo) ?>" readonly required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client's Name</label>
                                <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Technician Assigned</label>
                                <select name="technician" id="technician_select" class="form-select" required>
                                    <option value="">Select Technician/Staff</option>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= htmlspecialchars($tech['id']) ?>" data-name="<?= htmlspecialchars($tech['full_name']) ?>">
                                            <?= htmlspecialchars($tech['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Office/Department</label>
                                <select name="office" class="form-select" required>
                                    <option value="">Select Office/Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date/Time of Request</label>
                                <input type="datetime-local" name="date_time_call" id="date_time_call" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Required Response Time</label>
                                <div class="input-group">
                                    <input type="text" name="response_time" id="response_time" class="form-control" readonly>
                                    <button type="button" class="btn btn-outline-primary" id="startTimerBtn">
                                        <i class="fas fa-play"></i> Start Timer
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="stopTimerBtn" style="display:none;">
                                        <i class="fas fa-stop"></i> Stop
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" id="resetTimerBtn">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                                <small class="text-muted">Timer will track the response time for this service request</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Signature</label>
                                <div class="border rounded p-2 bg-light">
                                    <canvas id="signatureCanvas" style="width: 100%; height: 150px; cursor: crosshair; border: 1px solid #ddd; background: white;"></canvas>
                                    <div class="mt-2 d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignatureBtn">
                                            <i class="fas fa-eraser"></i> Clear
                                        </button>
                                        <small class="text-muted align-self-center ms-auto">Draw your signature above</small>
                                    </div>
                                </div>
                                <input type="hidden" name="signature" id="signatureData">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Equipment/Item Concern</label>
                            <input type="text" name="equipment" class="form-control" placeholder="e.g., Desktop PC, Printer, Network Connection" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Service Requirements / Description of Concern</label>
                            <textarea name="requirements" class="form-control" rows="4" placeholder="Please describe the issue or service needed..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-select" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="accomplishment" value="">
                        <input type="hidden" name="remarks" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('click', function(e) {
    const logoutLink = e.target.closest('a[href="../logout.php"]');
    if (!logoutLink) return;
    e.preventDefault();
    if (confirm('Are you sure you want to log out?')) {
        window.location.href = logoutLink.href;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('serviceRequestForm');
    
    // Initialize Signature Pad
    const canvas = document.getElementById('signatureCanvas');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 0)'
    });
    
    // Adjust canvas size
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);
    
    // Clear signature button
    document.getElementById('clearSignatureBtn').addEventListener('click', function() {
        signaturePad.clear();
    });
    
    // Timer functionality
    let timerInterval = null;
    let startTime = null;
    let elapsedTime = 0;
    const responseTimeInput = document.getElementById('response_time');
    const startTimerBtn = document.getElementById('startTimerBtn');
    const stopTimerBtn = document.getElementById('stopTimerBtn');
    const resetTimerBtn = document.getElementById('resetTimerBtn');
    
    function formatTime(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }
    
    function updateTimer() {
        if (startTime) {
            elapsedTime = Math.floor((Date.now() - startTime) / 1000);
        }
        responseTimeInput.value = formatTime(elapsedTime);
    }
    
    startTimerBtn.addEventListener('click', function() {
        if (!startTime) {
            startTime = Date.now() - (elapsedTime * 1000);
        }
        timerInterval = setInterval(updateTimer, 1000);
        startTimerBtn.style.display = 'none';
        stopTimerBtn.style.display = 'inline-block';
        updateTimer();
    });
    
    stopTimerBtn.addEventListener('click', function() {
        clearInterval(timerInterval);
        timerInterval = null;
        startTimerBtn.style.display = 'inline-block';
        stopTimerBtn.style.display = 'none';
    });
    
    resetTimerBtn.addEventListener('click', function() {
        clearInterval(timerInterval);
        timerInterval = null;
        startTime = null;
        elapsedTime = 0;
        responseTimeInput.value = '00:00:00';
        startTimerBtn.style.display = 'inline-block';
        stopTimerBtn.style.display = 'none';
    });
    
    // Initialize timer display
    responseTimeInput.value = '00:00:00';

    document.querySelectorAll('.sendRequestBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const formType = this.getAttribute('data-form');
            const form = document.getElementById('serviceRequestForm');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Check if signature is provided
            if (signaturePad.isEmpty()) {
                alert('⚠️ Please provide your signature');
                return;
            }
            
            // Save signature to hidden input
            document.getElementById('signatureData').value = signaturePad.toDataURL();
            
            // Stop timer if running
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            // Get technician name for PDF
            const technicianSelect = document.getElementById('technician_select');
            const technicianName = technicianSelect.options[technicianSelect.selectedIndex]?.dataset.name || '';
            if (technicianName) {
                // Add technician name to form for PDF generation
                const techNameInput = document.createElement('input');
                techNameInput.type = 'hidden';
                techNameInput.name = 'technician_name';
                techNameInput.value = technicianName;
                form.appendChild(techNameInput);
            }

            const formData = new FormData(form);
            formData.append('form_type', formType);

            fetch('send_request.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                if (data.includes('✅')) {
                    form.reset();
                    signaturePad.clear();
                    clearInterval(timerInterval);
                    elapsedTime = 0;
                    startTime = null;
                    responseTimeInput.value = '00:00:00';
                    startTimerBtn.style.display = 'inline-block';
                    stopTimerBtn.style.display = 'none';
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error);
            });
        });
    });
});
</script>
</body>
</html>

