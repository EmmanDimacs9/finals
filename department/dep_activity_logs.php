<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// ✅ Check user login
requireLogin();

// ✅ Clear all activity logs
if (isset($_GET['clear_logs']) && $_GET['clear_logs'] === '1') {
    $conn->query("DELETE FROM logs");
    header("Location: dep_activity_logs.php?cleared=1");
    exit;
}

// ✅ Fetch all logs with pagination (including service requests)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count from logs table
$totalLogsCount = $conn->query("SELECT COUNT(*) AS count FROM logs")->fetch_assoc()['count'] ?? 0;

// Get total count from service_requests table
$totalServiceRequestsCount = $conn->query("SELECT COUNT(*) AS count FROM service_requests")->fetch_assoc()['count'] ?? 0;

// Total combined count
$totalLogs = $totalLogsCount + $totalServiceRequestsCount;
$totalPages = ceil($totalLogs / $limit);

// Fetch logs and service requests, combine and sort by date
$logsQuery = "
    SELECT * FROM (
        SELECT 
            id,
            user as user_name,
            action as description,
            DATE_FORMAT(date, '%Y-%m-%d %H:%i:%s') as log_date,
            'log' as source_type
        FROM logs
        
        UNION ALL
        
        SELECT 
            sr.id,
            COALESCE(u.full_name, sr.client_name) as user_name,
            CONCAT('Service Request: ', sr.equipment, ' - ', LEFT(sr.requirements, 50)) as description,
            DATE_FORMAT(sr.created_at, '%Y-%m-%d %H:%i:%s') as log_date,
            'service_request' as source_type
        FROM service_requests sr
        LEFT JOIN users u ON sr.user_id = u.id
    ) AS combined_logs
    ORDER BY log_date DESC
    LIMIT $limit OFFSET $offset
";

$logs = $conn->query($logsQuery);

// ✅ Include your forms (modals)
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
    <title>Activity Logs - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #343a40;
        }
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
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
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .pagination {
            justify-content: center;
        }
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .page-link {
            color: var(--primary-color);
        }
        .page-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>

<!-- Navbar -->
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
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar py-4">
            <h5 class="text-center text-danger mb-3"><i class="fas fa-bars"></i> Navigation</h5>
            <div class="nav flex-column">
                <a href="depdashboard.php" class="nav-link"><i class="fas fa-user-tie"></i> Depart Head</a>
                <a href="service_request.php" class="nav-link"><i class="fas fa-desktop"></i> Service Request</a>
                <a href="system_request.php" class="nav-link"><i class="fas fa-cog"></i> System Request</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link active"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <?php if (isset($_GET['cleared']) && $_GET['cleared'] === '1'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> All activity logs have been cleared successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-history"></i> Activity Logs</h2>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted">
                        <i class="fas fa-info-circle"></i> Total: <?= $totalLogs ?> logs
                    </div>
                    <?php if ($totalLogs > 0): ?>
                    <a href="dep_activity_logs.php?clear_logs=1" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to clear all activity logs? This action cannot be undone.');">
                        <i class="fas fa-trash-alt"></i> Clear All Logs
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Logs Table -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-list"></i> All Activity Logs
                </div>
                <div class="card-body p-0">
                    <?php if ($logs->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($log = $logs->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($log['id'] ?? '') ?></td>
                                            <td>
                                                <i class="fas fa-user-circle text-primary me-2"></i>
                                                <?= htmlspecialchars($log['user_name'] ?? '') ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($log['source_type'] ?? '') === 'service_request' ? 'bg-success' : 'bg-info' ?>">
                                                    <?php if (($log['source_type'] ?? '') === 'service_request'): ?>
                                                        <i class="fas fa-desktop me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($log['description'] ?? '') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-clock text-muted me-2"></i>
                                                <?= htmlspecialchars($log['log_date'] ?? '') ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No activity logs found</h5>
                            <p class="text-muted">Activity logs will appear here when users perform actions in the system.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Activity logs pagination" class="mt-4">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// System Request Form Date Enhancement
document.addEventListener('DOMContentLoaded', function() {
    const systemReqsModal = document.getElementById('systemReqsModal');
    
    if (systemReqsModal) {
        systemReqsModal.addEventListener('shown.bs.modal', function() {
            // Set today's date as default for ICT Services date field
            const today = new Date();
            const todayString = today.toISOString().split('T')[0];
            
            // Set default date for ICT Services
            const ictDateInput = document.querySelector('input[name="ictDate"]');
            if (ictDateInput && !ictDateInput.value) {
                ictDateInput.value = todayString;
            }
            
            // Set default date for Work Done By date if empty
            const workDoneDateInput = document.querySelector('input[name="ictWorkByDate"]');
            if (workDoneDateInput && !workDoneDateInput.value) {
                workDoneDateInput.value = todayString;
            }
            
            // Set default date for Conforme date if empty
            const conformeDateInput = document.querySelector('input[name="ictConformeDate"]');
            if (conformeDateInput && !conformeDateInput.value) {
                conformeDateInput.value = todayString;
            }
        });
        
        // Clear form when modal is hidden
        systemReqsModal.addEventListener('hidden.bs.modal', function() {
            const form = systemReqsModal.querySelector('form');
            if (form) {
                form.reset();
            }
        });
    }
});
</script>
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

// Handle Send Request button for all forms
document.querySelectorAll('.sendRequestBtn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const formType = this.getAttribute('data-form');
        const form = this.closest('form');
        const modal = this.closest('.modal');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Get all form data
        const formData = new FormData(form);
        formData.append('form_type', formType);
        
        // Send request via AJAX
        fetch('send_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            if (data.includes('✅')) {
                // Close the modal and refresh if successful
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
                setTimeout(() => window.location.reload(), 500);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error);
        });
    });
});
</script>
</body>
</html>
