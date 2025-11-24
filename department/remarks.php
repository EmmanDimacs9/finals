<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

requireLogin();

// Get current logged-in user ID
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch service requests with remarks - only for the current logged-in department admin
$remarksQuery = "
    SELECT 
        sr.id,
        sr.ict_srf_no,
        sr.client_name,
        sr.office,
        sr.equipment,
        sr.remarks,
        sr.status,
        sr.completed_at,
        sr.created_at,
        u.full_name as technician_name
    FROM service_requests sr
    LEFT JOIN users u ON sr.technician_id = u.id
    WHERE sr.remarks IS NOT NULL AND sr.remarks != '' AND sr.user_id = ?
    ORDER BY sr.completed_at DESC, sr.created_at DESC
";
$stmt = $conn->prepare($remarksQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$remarksResult = $stmt->get_result();
$remarks = [];
while ($row = $remarksResult->fetch_assoc()) {
    $remarks[] = $row;
}
$stmt->close();

// Include forms
include '../PDFS/PreventiveMaintenancePlan/preventiveForm.php';
include '../PDFS/PreventiveMaintendancePlanIndexCard/PreventiveMaintendancePlanIndexCard.php';
include '../PDFS/AnnouncementGreetings/announcementForm.php';
include '../PDFS/WebsitePosting/webpostingForm.php';
include '../PDFS/SystemRequest/systemReqsForm.php';
include '../PDFS/ICTRequestForm/ICTRequestForm.php';
include '../PDFS/ISPEvaluation/ISPEvaluation.php';
include '../PDFS/UserAccountForm/UserAccountForm.php';
include '../PDFS/PostingRequestForm/PostingRequestForm.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remarks - BSU ICT Management System</title>
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
        .remarks-card {
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }
        .remarks-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.12);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in-progress {
            background-color: #cce5ff;
            color: #004085;
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
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link active"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-comment-alt"></i> Service Request Remarks</h2>
                <div class="text-muted">
                    <i class="fas fa-info-circle"></i> View remarks from technicians on completed service requests
                </div>
            </div>

            <?php if (empty($remarks)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No remarks available</h5>
                        <p class="text-muted">Remarks from technicians will appear here once service requests are completed.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($remarks as $remark): ?>
                        <div class="col-12">
                            <div class="card remarks-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <i class="fas fa-tag text-danger"></i> 
                                                <?= htmlspecialchars($remark['ict_srf_no'] ?: 'SR-' . $remark['id']) ?>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($remark['client_name']) ?> | 
                                                <i class="fas fa-building"></i> <?= htmlspecialchars($remark['office']) ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $remark['status'])) ?>">
                                            <?= htmlspecialchars($remark['status']) ?>
                                        </span>
                                    </div>

                                    <div class="mb-3">
                                        <strong><i class="fas fa-desktop"></i> Equipment:</strong>
                                        <span class="ms-2"><?= htmlspecialchars($remark['equipment']) ?></span>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded">
                                        <strong><i class="fas fa-comment-dots text-primary"></i> Remarks:</strong>
                                        <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($remark['remarks'])) ?></p>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            <div>
                                                <?php if ($remark['technician_name']): ?>
                                                    <i class="fas fa-user-cog"></i> Technician: <?= htmlspecialchars($remark['technician_name']) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-1">
                                                <?php if ($remark['completed_at']): ?>
                                                    <i class="fas fa-check-circle text-success"></i> Completed: <?= date('M d, Y h:i A', strtotime($remark['completed_at'])) ?>
                                                <?php else: ?>
                                                    <i class="fas fa-clock"></i> Created: <?= date('M d, Y h:i A', strtotime($remark['created_at'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="../PDFS/ICTRequestForm/ictServiceRequestPDF.php?service_request_id=<?= $remark['id'] ?>" 
                                               target="_blank" 
                                               class="btn btn-danger btn-sm">
                                                <i class="fas fa-file-pdf"></i> View PDF with Survey
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Logout confirmation
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

