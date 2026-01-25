<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/logs.php';

requireLogin();

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
    <title>Checklist - BSU ICT Management System</title>
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
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: var(--secondary-color);
        }
        .checklist-table th,
        .checklist-table td {
            vertical-align: middle;
            text-align: center;
            font-size: 0.9rem;
        }
        .checklist-table th {
            background-color: #f1f3f5;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            height: 2rem;
        }
        .form-check-inline {
            margin-right: 1.5rem;
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
                <a href="preventive_plan.php" class="nav-link"><i class="fas fa-calendar-check"></i> Preventive Maintenance Plan</a>
                <a href="checklist.php" class="nav-link active"><i class="fas fa-clipboard-check"></i> Checklist</a>
                <a href="remarks.php" class="nav-link"><i class="fas fa-comment-alt"></i> Remarks</a>
                <a href="dep_activity_logs.php" class="nav-link"><i class="fas fa-history"></i> Activity Logs</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-clipboard-check"></i> Service Evaluation Survey</h2>
                <div class="text-muted">
                    <i class="fas fa-info-circle"></i> Please evaluate completed ICT services
                </div>
            </div>

            <?php
            // Fetch completed service requests that haven't been surveyed yet
            $user_id = $_SESSION['user_id'] ?? 0;
            
            // First, let's check what surveys exist for this user
            $surveyCheckQuery = "SELECT service_request_id FROM service_surveys WHERE user_id = ?";
            $surveyStmt = $conn->prepare($surveyCheckQuery);
            $surveyStmt->bind_param("i", $user_id);
            $surveyStmt->execute();
            $surveyResult = $surveyStmt->get_result();
            $surveyedIds = [];
            while ($row = $surveyResult->fetch_assoc()) {
                $surveyedIds[] = $row['service_request_id'];
            }
            $surveyStmt->close();
            
            // Query to find completed service requests for this user
            $completedRequestsQuery = "
                SELECT sr.*, 
                    (SELECT COUNT(*) FROM service_surveys WHERE service_request_id = sr.id) as survey_count
                FROM service_requests sr 
                WHERE sr.user_id = ?
                AND sr.status = 'Completed'
                ORDER BY 
                    CASE WHEN sr.completed_at IS NOT NULL THEN sr.completed_at ELSE sr.updated_at END DESC";
            
            $stmt = $conn->prepare($completedRequestsQuery);
            if (!$stmt) {
                error_log("Checklist query error: " . $conn->error);
                $completedRequestsArray = [];
            } else {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $allCompleted = $stmt->get_result();
                
                // Filter out already surveyed requests
                $completedRequestsArray = [];
                while ($row = $allCompleted->fetch_assoc()) {
                    if (!in_array($row['id'], $surveyedIds)) {
                        $completedRequestsArray[] = $row;
                    }
                }
                $stmt->close();
            }
            ?>
            
            <?php if (count($completedRequestsArray) > 0): ?>
                <?php foreach ($completedRequestsArray as $request): ?>
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-check-circle"></i> Service Request #<?= $request['id'] ?></h5>
                                <small>Completed on <?= $request['completed_at'] ? date('F d, Y g:i A', strtotime($request['completed_at'])) : 'Date not available' ?></small>
                            </div>
                            <span class="badge bg-light text-dark"><?= $request['support_level'] ?? 'N/A' ?> Support</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Equipment:</strong> <?= htmlspecialchars($request['equipment']) ?></p>
                                <p><strong>Location:</strong> <?= htmlspecialchars($request['location']) ?></p>
                                <p><strong>Technician:</strong> <?= htmlspecialchars($request['technician_id'] ? 'Technician ID: ' . $request['technician_id'] : 'N/A') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Service Description:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($request['requirements']) ?></p>
                                <?php if ($request['accomplishment']): ?>
                                <p><strong>Accomplishment:</strong></p>
                                <p class="text-muted"><?= htmlspecialchars($request['accomplishment']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr>

                        <h5 class="section-title mb-3">ICT Service Survey Form</h5>
                        <p class="text-muted mb-3">Please evaluate the service provided</p>
                        
                        <form class="service-survey-form" data-request-id="<?= $request['id'] ?>">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center align-middle">
                                    <thead>
                                        <tr>
                                            <th class="text-start">Evaluation Statements</th>
                                            <th>5<br><small>Very Satisfied</small></th>
                                            <th>4<br><small>Satisfied</small></th>
                                            <th>3<br><small>Neither Satisfied nor Dissatisfied</small></th>
                                            <th>2<br><small>Dissatisfied</small></th>
                                            <th>1<br><small>Very Dissatisfied</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start">Response time to your initial call for service</td>
                                            <?php for ($score = 5; $score >= 1; $score--): ?>
                                                <td><input type="radio" name="eval_response" value="<?= $score ?>" required></td>
                                            <?php endfor; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Quality of service provided to resolve the problem</td>
                                            <?php for ($score = 5; $score >= 1; $score--): ?>
                                                <td><input type="radio" name="eval_quality" value="<?= $score ?>" required></td>
                                            <?php endfor; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Courtesy and professionalism of the attending ICT staff</td>
                                            <?php for ($score = 5; $score >= 1; $score--): ?>
                                                <td><input type="radio" name="eval_courtesy" value="<?= $score ?>" required></td>
                                            <?php endfor; ?>
                                        </tr>
                                        <tr>
                                            <td class="text-start">Overall satisfaction with the assistance/service provided</td>
                                            <?php for ($score = 5; $score >= 1; $score--): ?>
                                                <td><input type="radio" name="eval_overall" value="<?= $score ?>" required></td>
                                            <?php endfor; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Comments (Optional)</label>
                                <textarea name="comments" class="form-control" rows="3" placeholder="Please share any additional feedback..."></textarea>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-paper-plane"></i> Submit Survey
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                // Check if user has any service requests at all (for debugging)
                $allRequestsQuery = "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress
                    FROM service_requests WHERE user_id = ?";
                $allStmt = $conn->prepare($allRequestsQuery);
                $allStmt->bind_param("i", $user_id);
                $allStmt->execute();
                $allResult = $allStmt->get_result();
                $allData = $allResult->fetch_assoc();
                $allStmt->close();
                ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No completed services to evaluate</h5>
                        <p class="text-muted">Completed service requests will appear here for evaluation.</p>
                        <?php if (isset($allData) && $allData['total'] > 0): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <strong>Your Service Requests:</strong><br>
                                    Total: <?= $allData['total'] ?> | 
                                    Completed: <?= $allData['completed'] ?> | 
                                    Pending: <?= $allData['pending'] ?> | 
                                    In Progress: <?= $allData['in_progress'] ?>
                                    <?php if ($allData['completed'] > 0): ?>
                                        <br><span class="text-info"><i class="fas fa-info-circle"></i> You have <?= $allData['completed'] ?> completed request(s). If they're not showing, they may have already been evaluated.</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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

// Handle survey form submission
document.querySelectorAll('.service-survey-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const requestId = this.getAttribute('data-request-id');
        const formData = new FormData(this);
        formData.append('request_id', requestId);
        formData.append('action', 'submit_survey');
        
        fetch('submit_survey.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Survey submitted successfully! Thank you for your feedback.');
                location.reload();
            } else {
                alert('❌ Error: ' + (data.message || 'Failed to submit survey'));
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