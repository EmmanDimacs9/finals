<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

// ✅ Ensure user is logged in
requireLogin();

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

// ✅ Stats
$totalRequests = $conn->query("SELECT COUNT(*) AS count FROM requests")->fetch_assoc()['count'] ?? 0;
$pendingRequests = $conn->query("SELECT COUNT(*) AS count FROM requests WHERE status='Pending'")->fetch_assoc()['count'] ?? 0;
$completedRequests = $conn->query("SELECT COUNT(*) AS count FROM requests WHERE status='Approved'")->fetch_assoc()['count'] ?? 0;
$activityLogs = $conn->query("SELECT COUNT(*) AS count FROM logs")->fetch_assoc()['count'] ?? 0;

// ✅ Date Filter (for Analytics) - Using prepared statements to prevent SQL injection
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// ✅ Analytics Query with Date Filter - Using prepared statements
$allowedForms = ['ICT Service Request Form', 'System Request'];
$formPlaceholders = implode(',', array_fill(0, count($allowedForms), '?'));

$analyticsQuery = "SELECT form_type, COUNT(*) as count, 
                   SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved_count,
                   SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                   SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected_count
                   FROM requests";

$conditions = [];
$params = [];
$types = '';

$conditions[] = "form_type IN ($formPlaceholders)";
$params = array_merge($params, $allowedForms);
$types .= str_repeat('s', count($allowedForms));

if (!empty($startDate) && !empty($endDate)) {
    $conditions[] = "DATE(created_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= 'ss';
} elseif (!empty($startDate)) {
    $conditions[] = "DATE(created_at) >= ?";
    $params[] = $startDate;
    $types .= 's';
} elseif (!empty($endDate)) {
    $conditions[] = "DATE(created_at) <= ?";
    $params[] = $endDate;
    $types .= 's';
}

if (!empty($conditions)) {
    $analyticsQuery .= ' WHERE ' . implode(' AND ', $conditions);
}
$analyticsQuery .= " GROUP BY form_type ORDER BY count DESC";

$stmt = $conn->prepare($analyticsQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$analyticsResult = $stmt->get_result();
$formAnalytics = [];
while ($row = $analyticsResult->fetch_assoc()) {
    $formAnalytics[] = $row;
}
$stmt->close();

// ✅ Include forms (legacy view restored)
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
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: #fff;
        }
        .card { border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .stats-card { text-align: center; padding: 25px 10px; }
        .stats-card h3 { font-weight: bold; font-size: 2.2rem; }
        .stats-card i { font-size: 2rem; margin-bottom: 10px; }
        #requestChart { max-width: 100%; height: auto; }
        .chart-scroll {
            position: relative;
            height: 400px;
            width: 100%;
            overflow-x: auto;
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
                <a href="depdashboard.php" class="nav-link active"><i class="fas fa-user-tie"></i> Depart Head</a>
                <a href="service_request.php" class="nav-link"><i class="fas fa-desktop"></i> Service Request</a>
                <a href="system_request.php" class="nav-link"><i class="fas fa-cog"></i> System Request</a>
                <a href="checklist.php" class="nav-link"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Department Dashboard</h2>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3"><div class="card stats-card text-primary"><i class="fas fa-clipboard-list"></i><h5>Total Requests</h5><h3><?= $totalRequests ?></h3></div></div>
                <div class="col-md-3"><div class="card stats-card text-warning"><i class="fas fa-hourglass-half"></i><h5>Pending Requests</h5><h3><?= $pendingRequests ?></h3></div></div>
                <div class="col-md-3"><div class="card stats-card text-success"><i class="fas fa-check-circle"></i><h5>Completed Requests</h5><h3><?= $completedRequests ?></h3></div></div>
                <div class="col-md-3"><div class="card stats-card text-danger"><i class="fas fa-file-alt"></i><h5>Activity Logs</h5><h3><?= $activityLogs ?></h3></div></div>
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
                                <div class="chart-scroll">
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
                                    <span><i class="fas fa-check-circle text-success"></i> Approved</span>
                                    <strong><?= $completedRequests ?></strong>
                                </div>
                                <?php $approvedPercentage = $totalRequests > 0 ? ($completedRequests / $totalRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: <?= $approvedPercentage ?>%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-hourglass-half text-warning"></i> Pending</span>
                                    <strong><?= $pendingRequests ?></strong>
                                </div>
                                <?php $pendingPercentage = $totalRequests > 0 ? ($pendingRequests / $totalRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: <?= $pendingPercentage ?>%"></div>
                                </div>
                            </div>

                            <?php 
                            $rejectedRequests = $conn->query("SELECT COUNT(*) AS count FROM requests WHERE status='Rejected'")->fetch_assoc()['count'] ?? 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="fas fa-times-circle text-danger"></i> Rejected</span>
                                    <strong><?= $rejectedRequests ?></strong>
                                </div>
                                <?php $rejectedPercentage = $totalRequests > 0 ? ($rejectedRequests / $totalRequests) * 100 : 0; ?>
                                <div class="progress">
                                    <div class="progress-bar bg-danger" style="width: <?= $rejectedPercentage ?>%"></div>
                                </div>
                            </div>

                            <hr>
                            <div class="text-center">
                                <h3 class="text-primary"><?= $totalRequests ?></h3>
                                <p class="text-muted mb-0">Total Requests</p>
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
