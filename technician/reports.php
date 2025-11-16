<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$page_title = 'Reports';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-chart-bar text-danger"></i> Reports & Analytics
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Generate comprehensive reports for equipment, maintenance, and service requests
                    </p>
                </div>
            </div>

            <!-- Report Types -->
            <div class="row">
                <!-- System Data Reports -->
                <div class="col-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header text-white text-center" style="background-color: #dc3545;">
                            <i class="fas fa-database"></i> System Data Reports
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <button class="list-group-item list-group-item-action" onclick="showReportModal('inventory')">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Complete Inventory Report</strong><br>
                                        <small>Generate a comprehensive list of all equipment with detailed information.</small>
                                    </div>
                                </button>

                                <button class="list-group-item list-group-item-action" onclick="showReportModal('department')">
                                    <i class="fas fa-building text-info me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Department Analysis Report</strong><br>
                                        <small>Equipment distribution and analysis by department.</small>
                                    </div>
                                </button>

                                <button class="list-group-item list-group-item-action" onclick="showReportModal('maintenance')">
                                    <i class="fas fa-tools text-warning me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Maintenance & Status Report</strong><br>
                                        <small>Maintenance records, schedules, and equipment status reports.</small>
                                    </div>
                                </button>

                                <button class="list-group-item list-group-item-action" onclick="showReportModal('incomplete')">
                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Incomplete Items Report</strong><br>
                                        <small>Equipment with missing or incomplete information.</small>
                                    </div>
                                </button>

                                <button class="list-group-item list-group-item-action" onclick="showReportModal('acquisition')">
                                    <i class="fas fa-calendar-alt text-secondary me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Acquisition Timeline Report</strong><br>
                                        <small>Equipment acquisition timeline and purchase history.</small>
                                    </div>
                                </button>

                                <button class="list-group-item list-group-item-action" onclick="window.location.href='../PDFS/PreventiveMaintenanceChecklist/preventiveChecklistForm.php'">
                                    <i class="fas fa-clipboard-check text-success me-2"></i>
                                    <div class="d-inline-block text-start">
                                        <strong>Preventive Maintenance Checklist</strong><br>
                                        <small>Generate preventive maintenance checklist and corrective action record form.</small>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showReportModal(reportType) {
    const reportMap = {
        'inventory': 'complete_inventory.php',
        'department': 'department_report.php',
        'maintenance': 'maintenance_report.php',
        'incomplete': 'incomplete_report.php',
        'acquisition': 'acquisition_report.php',
    };

    if (!reportMap[reportType]) return;

    // Create a temporary form for immediate download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../PDFS/' + reportMap[reportType];
    form.target = '_blank';

    // Optional hidden input for filters (date/department/etc.)
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'report_type';
    input.value = reportType;
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

<style>
.list-group-item {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    margin-bottom: 8px;
    border-radius: 8px;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.list-group-item i {
    font-size: 1.5rem;
}

.card {
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: none;
}

.card-header {
    border-radius: 15px 15px 0 0;
    font-weight: 600;
    padding: 20px;
}
</style>

<?php require_once 'footer.php'; ?>

