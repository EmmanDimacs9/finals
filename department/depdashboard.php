<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

// ✅ Ensure user is logged in
requireLogin();

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'User';

// ✅ Ensure the requests table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS `requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `form_type` varchar(255) NOT NULL,
    `form_data` longtext DEFAULT NULL,
    `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `form_type` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
$conn->query($createTableQuery);

// ✅ Personalized Stats - Service Requests (from service_requests table)
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM service_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalServiceRequests = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM service_requests WHERE user_id = ? AND status='Pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pendingServiceRequests = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM service_requests WHERE user_id = ? AND (status='In Progress' OR status='Assigned')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inProgressServiceRequests = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM service_requests WHERE user_id = ? AND status='Completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completedServiceRequests = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

// ✅ All Activity Logs (showing all activities from all offices/departments)
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM logs");
$stmt->execute();
$activityLogs = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
$stmt->close();

// ✅ Date Filter (for Analytics)
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// ✅ Analytics Query with Date Filter (Personalized) - Service Requests Only
if (!empty($startDate) && !empty($endDate)) {
    $stmt = $conn->prepare("SELECT 
                           'Service Request' as form_type,
                           COUNT(*) as count, 
                           SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as approved_count,
                           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                           SUM(CASE WHEN status = 'In Progress' OR status = 'Assigned' THEN 1 ELSE 0 END) as rejected_count
                           FROM service_requests 
                           WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->bind_param("iss", $user_id, $startDate, $endDate);
} elseif (!empty($startDate)) {
    $stmt = $conn->prepare("SELECT 
                           'Service Request' as form_type,
                           COUNT(*) as count, 
                           SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as approved_count,
                           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                           SUM(CASE WHEN status = 'In Progress' OR status = 'Assigned' THEN 1 ELSE 0 END) as rejected_count
                           FROM service_requests 
                           WHERE user_id = ? AND DATE(created_at) >= ?");
    $stmt->bind_param("is", $user_id, $startDate);
} elseif (!empty($endDate)) {
    $stmt = $conn->prepare("SELECT 
                           'Service Request' as form_type,
                           COUNT(*) as count, 
                           SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as approved_count,
                           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                           SUM(CASE WHEN status = 'In Progress' OR status = 'Assigned' THEN 1 ELSE 0 END) as rejected_count
                           FROM service_requests 
                           WHERE user_id = ? AND DATE(created_at) <= ?");
    $stmt->bind_param("is", $user_id, $endDate);
} else {
    $stmt = $conn->prepare("SELECT 
                           'Service Request' as form_type,
                           COUNT(*) as count, 
                           SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as approved_count,
                           SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                           SUM(CASE WHEN status = 'In Progress' OR status = 'Assigned' THEN 1 ELSE 0 END) as rejected_count
                           FROM service_requests 
                           WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$analyticsResult = $stmt->get_result();
$formAnalytics = [];
while ($row = $analyticsResult->fetch_assoc()) {
    if ($row['count'] > 0) {
        $formAnalytics[] = $row;
    }
}
$stmt->close();

// ✅ Include forms
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
    <title>Department Dashboard - BSU ICT Management System</title>
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
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .stats-card { text-align: center; padding: 25px 10px; }
        .stats-card h3 { font-weight: bold; font-size: 2.2rem; }
        .stats-card i { font-size: 2rem; margin-bottom: 10px; }
        #requestChart { max-width: 100%; height: auto; }
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
                <a href="depdashboard.php" class="nav-link active"><i class="fas fa-user-tie"></i> Depart Head</a>
                <a href="service_request.php" class="nav-link"><i class="fas fa-desktop"></i> Service Request</a>
                <a href="system_request.php" class="nav-link"><i class="fas fa-cog"></i> System Request</a>
                <a href="preventive_plan.php" class="nav-link"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><i class="fas fa-tachometer-alt"></i> My Dashboard</h2>
                    <p class="text-muted mb-0"><i class="fas fa-user"></i> Welcome, <?= htmlspecialchars($user_name) ?></p>
                </div>
            </div>

            <!-- Service Request Stats -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-desktop"></i> Service Requests</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card stats-card text-primary border-primary">
                                <i class="fas fa-clipboard-list"></i>
                                <h5>Total Service Requests</h5>
                                <h3><?= $totalServiceRequests ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card text-warning border-warning">
                                <i class="fas fa-hourglass-half"></i>
                                <h5>Pending</h5>
                                <h3><?= $pendingServiceRequests ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card text-info border-info">
                                <i class="fas fa-cogs"></i>
                                <h5>In Progress</h5>
                                <h3><?= $inProgressServiceRequests ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card text-success border-success">
                                <i class="fas fa-check-circle"></i>
                                <h5>Completed</h5>
                                <h3><?= $completedServiceRequests ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card stats-card text-info border-info">
                        <i class="fas fa-history"></i>
                        <h5>All Activity Logs</h5>
                        <h3><?= $activityLogs ?></h3>
                        <small class="text-muted">From all offices/departments</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stats-card text-secondary border-secondary">
                        <i class="fas fa-chart-line"></i>
                        <h5>Total Service Requests</h5>
                        <h3><?= $totalServiceRequests ?></h3>
                    </div>
                </div>
            </div>

            <!-- Analytics Section -->
            <?php if (!empty($formAnalytics)): ?>
            <div class="row g-4 mb-3">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <!-- ✅ Chart Header with Date Filter -->
                        <div class="card-header bg-danger text-white d-flex flex-wrap justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Request Distribution</h5>
                                <?php if ($startDate || $endDate): ?>
                                    <small class="text-white ms-2">
                                        (<?= $startDate ?: 'All time' ?> → <?= $endDate ?: 'Today' ?>)
                                    </small>
                                <?php endif; ?>
                            </div>

                            <!-- ✅ Date Filter Form -->
                            <form method="GET" class="d-flex align-items-center mt-2 mt-md-0">
                                <label for="start_date" class="fw-semibold text-white mb-0 me-2">From:</label>
                                <input type="date" name="start_date" id="start_date" 
                                    class="form-control form-control-sm me-3"
                                    value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">

                                <label for="end_date" class="fw-semibold text-white mb-0 me-2">To:</label>
                                <input type="date" name="end_date" id="end_date"
                                    class="form-control form-control-sm me-3"
                                    value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">

                                <button class="btn btn-sm btn-light" type="submit">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </form>

                        </div>

                        <div class="collapse show" id="requestChartSection">
                            <div class="card-body p-3" style="background: #ffffff;">
                                <div style="position: relative; height: 400px; width: 100%;">
                                    <canvas id="requestChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Summary -->
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-tasks"></i> Status Summary</h5>
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#statusSummarySection" aria-expanded="true">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                        </div>
                        <div class="collapse show" id="statusSummarySection">
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-check-circle text-success"></i> Completed</span>
                                    <strong><?= $completedServiceRequests ?></strong>
                                </div>
                                <?php $completedPercentage = $totalServiceRequests > 0 ? ($completedServiceRequests / $totalServiceRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?= $completedPercentage ?>%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-hourglass-half text-warning"></i> Pending</span>
                                    <strong><?= $pendingServiceRequests ?></strong>
                                </div>
                                <?php $pendingPercentage = $totalServiceRequests > 0 ? ($pendingServiceRequests / $totalServiceRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: <?= $pendingPercentage ?>%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-cogs text-info"></i> In Progress</span>
                                    <strong><?= $inProgressServiceRequests ?></strong>
                                </div>
                                <?php $inProgressPercentage = $totalServiceRequests > 0 ? ($inProgressServiceRequests / $totalServiceRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-info" style="width: <?= $inProgressPercentage ?>%"></div>
                                </div>
                            </div>

                            <hr>
                            <div class="text-center">
                                <h3 class="text-primary"><?= $totalServiceRequests ?></h3>
                                <p class="text-muted mb-0">Total Service Requests</p>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No analytics data available yet</h5>
                            <p class="text-muted">Start making requests to see analytics and insights</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
<?php if (!empty($formAnalytics)): ?>
const ctx = document.getElementById('requestChart');
const formTypes = <?= json_encode(array_column($formAnalytics, 'form_type')) ?>;
const approved = <?= json_encode(array_column($formAnalytics, 'approved_count')) ?>;
const pending = <?= json_encode(array_column($formAnalytics, 'pending_count')) ?>;
const rejected = <?= json_encode(array_column($formAnalytics, 'rejected_count')) ?>;

if (formTypes.length === 0) {
    // ✅ No data found for selected date
    ctx.parentElement.innerHTML = `
    <div class="d-flex flex-column justify-content-center align-items-center py-5 text-muted">
        <i class="fas fa-chart-line mb-3" style="font-size: 2.5rem; color: #6c757d;"></i>
        <h5 class="fw-semibold">No analytics data available yet</h5>
        <p class="mb-4" style="font-size: 0.95rem;">Start making requests to see analytics and insights</p>
        <a href="depdashboard.php" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>`;

} else {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: formTypes,
            datasets: [
                { label: 'Approved', data: approved, borderColor: '#28a745', tension: 0.4, fill: true },
                { label: 'Pending', data: pending, borderColor: '#ffc107', tension: 0.4, fill: true },
                { label: 'Rejected', data: rejected, borderColor: '#dc3545', tension: 0.4, fill: true }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}
<?php else: ?>
// ✅ No data at all (initially)
document.getElementById('requestChart').parentElement.innerHTML = `
    <div class="text-center py-5 text-muted fw-bold" style="font-size: 1.1rem;">
        <i class="fas fa-info-circle me-2"></i>No request data available.
    </div>`;
<?php endif; ?>
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
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

</body>
</html>
