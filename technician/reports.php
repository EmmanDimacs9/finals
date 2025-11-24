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
                <!-- Preventive Maintenance Checklist -->
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="report-card" onclick="window.location.href='../PDFS/PreventiveMaintenanceChecklist/preventiveChecklistForm.php'">
                        <div class="report-card-icon">
                            <i class="fas fa-clipboard-check"></i>
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
.report-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.report-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
    transition: width 0.3s ease;
}

.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    border-color: #28a745;
}

.report-card:hover::before {
    width: 6px;
}

.report-card-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.report-card:hover .report-card-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.report-card-icon i {
    font-size: 2rem;
    color: #ffffff;
}

.report-card-content {
    flex: 1;
}

.report-card-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 12px;
    line-height: 1.4;
}

.report-card-description {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0;
}

.report-card-arrow {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.report-card:hover .report-card-arrow {
    background: #28a745;
    transform: translateX(5px);
}

.report-card-arrow i {
    color: #6c757d;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.report-card:hover .report-card-arrow i {
    color: #ffffff;
}

@media (max-width: 768px) {
    .report-card {
        padding: 25px;
    }
    
    .report-card-icon {
        width: 60px;
        height: 60px;
    }
    
    .report-card-icon i {
        font-size: 1.75rem;
    }
    
    .report-card-title {
        font-size: 1.1rem;
    }
}
</style>

<?php require_once 'footer.php'; ?>

