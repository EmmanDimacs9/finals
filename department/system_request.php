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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Request - BSU ICT Management System</title>
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
                <a href="service_request.php" class="nav-link"><i class="fas fa-desktop"></i> Service Request</a>
                <a href="system_request.php" class="nav-link active"><i class="fas fa-cog"></i> System Request</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-cog"></i> System Request Form</h2>
                <div class="d-flex gap-2">
                    <button form="systemRequestForm" type="submit" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Generate PDF</button>
                    <button form="systemRequestForm" type="button" class="btn btn-warning sendRequestBtn" data-form="System Request"><i class="fas fa-paper-plane"></i> Send Request</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="systemRequestForm" class="needs-validation" method="POST" action="../PDFS/SystemRequest/systemReqsPDF.php" target="_blank" novalidate>
                        <div class="mb-3">
                            <label for="office" class="form-label">Requesting Office/Unit</label>
                            <select id="office" name="office" class="form-select" required>
                                <option value="">Select Office/Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label section-title mb-2">Type of Request</label>
                            <div class="row">
                                <?php
                                $requestTypes = [
                                    'Correction of system issue',
                                    'System enhancement',
                                    'New System',
                                ];
                                foreach ($requestTypes as $idx => $label): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sysType[]" value="<?= $label ?>" id="requestType<?= $idx ?>">
                                        <label class="form-check-label" for="requestType<?= $idx ?>"><?= $label ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label section-title mb-2">Urgency</label>
                            <div class="row">
                                <?php
                                $urgencies = [
                                    'Immediate attention required',
                                    'Handle in normal priority',
                                    'Defer until new system is developed',
                                ];
                                foreach ($urgencies as $idx => $label): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="urgency[]" value="<?= $label ?>" id="urgency<?= $idx ?>">
                                        <label class="form-check-label" for="urgency<?= $idx ?>"><?= $label ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nameSystem" class="form-label">Name of Existing / Proposed System</label>
                            <input type="text" id="nameSystem" name="nameSystem" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="descRequest" class="form-label">Description of Request</label>
                            <textarea id="descRequest" name="descRequest" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea id="remarks" name="remarks" class="form-control" rows="2" required></textarea>
                        </div>

                        <hr>

                        <div class="row g-4">
                            <div class="col-md-4">
                                <h5 class="section-title mb-2">Requested By</h5>
                                <label class="form-label">Name</label>
                                <input type="text" name="reqByName" class="form-control" required>
                                <label class="form-label mt-2">Designation</label>
                                <input type="text" name="reqByDesignation" class="form-control" required>
                                <label class="form-label mt-2">Date</label>
                                <input type="date" name="reqByDate" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <h5 class="section-title mb-2">Recommending Approval</h5>
                                <label class="form-label">Name</label>
                                <input type="text" name="recApprovalName" class="form-control" required>
                                <label class="form-label mt-2">Designation</label>
                                <input type="text" name="recApprovalDesignation" class="form-control" required>
                                <label class="form-label mt-2">Date</label>
                                <input type="date" name="recApprovalDate" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <h5 class="section-title mb-2">Approved By</h5>
                                <label class="form-label">Name</label>
                                <input type="text" name="approvedByName" class="form-control" required>
                                <label class="form-label mt-2">Designation</label>
                                <input type="text" name="approvedByDesignation" class="form-control" required>
                                <label class="form-label mt-2">Date</label>
                                <input type="date" name="approvedByDate" class="form-control" required>
                            </div>
                        </div>

                        <hr>

                        <h5 class="section-title mb-2">To be completed by ICT Services</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="ictDate" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assigned to</label>
                                <input type="text" name="ictAssigned" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Description of Accomplished Tasks</label>
                                <textarea name="ictTasks" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Work Done By (Name)</label>
                                <input type="text" name="ictWorkByName" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="ictWorkByDesignation" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="ictWorkByDate" class="form-control">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Conforme - Name</label>
                                <input type="text" name="ictConformeName" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="ictConformeDesignation" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="ictConformeDate" class="form-control">
                            </div>
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
    const form = document.getElementById('systemRequestForm');

    document.querySelectorAll('.sendRequestBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const formType = this.getAttribute('data-form');

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

