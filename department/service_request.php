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

// Fetch technicians for assignment dropdown
$techniciansQuery = "SELECT id, full_name, email FROM users WHERE role = 'technician' ORDER BY full_name ASC";
$techniciansResult = $conn->query($techniciansQuery);
$technicians = [];
if ($techniciansResult) {
    while ($row = $techniciansResult->fetch_assoc()) {
        $technicians[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICT Service Request - BSU ICT Management System</title>
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
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-desktop"></i> ICT Service Request Form</h2>
                <div class="d-flex gap-2">
                    <button form="serviceRequestForm" type="button" class="btn btn-danger" id="generatePdfBtn"><i class="fas fa-file-pdf"></i> Generate PDF</button>
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
                                <label class="form-label">Client's Name</label>
                                <input type="text" name="client_name" class="form-control" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" readonly required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ICT SRF No.</label>
                                <input type="text" name="ict_srf_no" class="form-control" id="ictSrfNo" placeholder="Auto-generated" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Technician Assigned</label>
                                <select name="technician" class="form-select">
                                    <option value="">Select Technician (optional)</option>
                                    <?php foreach ($technicians as $tech): ?>
                                        <option value="<?= htmlspecialchars($tech['full_name']) ?>">
                                            <?= htmlspecialchars($tech['full_name']) ?> (<?= htmlspecialchars($tech['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Required Response Time</label>
                                <select name="response_time" class="form-select">
                                    <option value="">Select Response Time</option>
                                    <option value="5 minutes">5 minutes</option>
                                    <option value="15 minutes">15 minutes</option>
                                    <option value="30 minutes">30 minutes</option>
                                    <option value="1 hour">1 hour</option>
                                    <option value="2 hours">2 hours</option>
                                    <option value="4 hours">4 hours</option>
                                    <option value="Within the day">Within the day</option>
                                    <option value="Next business day">Next business day</option>
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
                                <input type="datetime-local" name="date_time_call" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Equipment/Item Concern</label>
                            <select name="equipment" class="form-select" required>
                                <option value="">Select Equipment/Item</option>
                                <option value="Desktop">Desktop</option>
                                <option value="Laptop">Laptop</option>
                                <option value="Printer">Printer</option>
                                <option value="Switch">Switch</option>
                                <option value="Telephone">Telephone</option>
                                <option value="Access Point">Access Point</option>
                            </select>
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

                        <div class="section-title mt-4">Technician / Accomplishment Details (optional)</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Actual Response Time</label>
                                <input type="text" name="accomp_response" class="form-control" placeholder="e.g., Arrived within 2 hours">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Time</label>
                                <input type="text" name="accomp_service" class="form-control" placeholder="e.g., 1:30 PM - 2:15 PM">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Accomplishment Summary</label>
                            <textarea name="accomplishment" class="form-control" rows="3" placeholder="Describe the work completed or observations made (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Technician Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Additional notes, recommendations, or follow-up actions"></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    const ictSrfInput = document.getElementById('ictSrfNo');

    // Auto-generate ICT SRF No. (e.g., ICT-SRF-2025-0001)
    function generateSrfNumber() {
        const now = new Date();
        const year = now.getFullYear();
        const uniquePart = now.getTime().toString().slice(-4); // last 4 digits of timestamp
        return `ICT-SRF-${year}-${uniquePart}`;
    }

    if (ictSrfInput) {
        ictSrfInput.value = generateSrfNumber();
    }

    // Handle PDF Generation with validation
    document.getElementById('generatePdfBtn').addEventListener('click', function() {
        const form = document.getElementById('serviceRequestForm');
        
        // Validate form before submitting (since form has novalidate attribute)
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Submit form to generate PDF
        form.submit();
    });

    document.querySelectorAll('.sendRequestBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const formType = this.getAttribute('data-form');
            const form = document.getElementById('serviceRequestForm');

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
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

