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
                <a href="system_request.php" class="nav-link"><i class="fas fa-cog"></i> System Request</a>
                <a href="preventive_plan.php" class="nav-link active"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</h2>
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary"><i class="fas fa-eraser"></i> Clear</button>
                    <button class="btn btn-danger"><i class="fas fa-save"></i> Save Plan</button>
                    <button class="btn btn-outline-danger"><i class="fas fa-file-pdf"></i> Export PDF</button>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Office / College</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Reference No.</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Effectivity Date</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Revision No.</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">FY</label>
                            <input type="text" class="form-control">
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

                    <div class="section-title mb-2">ICT Equipment - Desktop Computers</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered maintenance-table">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 260px;">Equipment / Item</th>
                                    <?php foreach ($months as $month): ?>
                                        <th><?= $month ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($desktopComputers)): ?>
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($desktopComputers as $equipment => $schedule): ?>
                                <tr>
                                    <td class="text-start"><?= $equipment ?></td>
                                    <?php foreach ($months as $month): ?>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option value=""></option>
                                            <option value="M" <?= ($schedule[$month] ?? '') === 'M' ? 'selected' : '' ?>>M</option>
                                            <option value="Q" <?= ($schedule[$month] ?? '') === 'Q' ? 'selected' : '' ?>>Q</option>
                                            <option value="SA" <?= ($schedule[$month] ?? '') === 'SA' ? 'selected' : '' ?>>SA</option>
                                        </select>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="section-title mb-2">ICT Equipment - Network Devices</div>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered maintenance-table">
                            <thead>
                                <tr>
                                    <th class="text-start" style="min-width: 260px;">Equipment / Item</th>
                                    <?php foreach ($months as $month): ?>
                                        <th><?= $month ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($networkEquipment)): ?>
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No equipment added yet. Use the "Add Equipment" button to get started.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($networkEquipment as $equipment => $schedule): ?>
                                <tr>
                                    <td class="text-start"><?= $equipment ?></td>
                                    <?php foreach ($months as $month): ?>
                                    <td>
                                        <select class="form-select form-select-sm">
                                            <option value=""></option>
                                            <option value="M" <?= ($schedule[$month] ?? '') === 'M' ? 'selected' : '' ?>>M</option>
                                            <option value="Q" <?= ($schedule[$month] ?? '') === 'Q' ? 'selected' : '' ?>>Q</option>
                                            <option value="SA" <?= ($schedule[$month] ?? '') === 'SA' ? 'selected' : '' ?>>SA</option>
                                        </select>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
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
                            <input type="text" class="form-control">
                            <small class="text-muted">ICT Services Staff</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Reviewed by</label>
                            <input type="text" class="form-control">
                            <small class="text-muted">Head, ICT Services</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label section-title mb-1">Approved by</label>
                            <input type="text" class="form-control">
                            <small class="text-muted">Vice Chancellor for Development and External Affairs</small>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span>Date Signed:</span>
                                <input type="date" class="form-control w-50">
                            </div>
                        </div>
                    </div>
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
</script>
</body>
</html>

