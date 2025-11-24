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

<div class="container-fluid" style="padding: 30px 20px; background-color: #f8f9fa; min-height: 100vh;">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="mb-5" style="margin: 0; width: 100%;">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3" style="width: 48px; height: 48px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                        <i class="fas fa-chart-bar text-white" style="font-size: 24px;"></i>
                    </div>
                    <div>
                        <h1 class="mb-0" style="color: #212529; font-weight: 700; font-size: 2rem; letter-spacing: -0.5px;">
                            Reports & Analytics
                        </h1>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-4" style="margin-left: 60px;">
                    <div class="me-2" style="width: 20px; height: 20px; background-color: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-info-circle text-white" style="font-size: 10px;"></i>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.95rem; color: #6c757d;">
                        Generate comprehensive reports for equipment, maintenance, and service requests
                    </p>
                </div>

                <!-- Preventive Maintenance Checklist Card -->
                <div class="row">
                    <div class="col-12 col-md-8 col-lg-6" style="max-width: 1200px; margin: 0; padding-left: 0;">
                        <div class="report-card-modern" onclick="window.location.href='../PDFS/PreventiveMaintenanceChecklist/preventiveChecklistForm.php'">
                            <div class="report-card-icon-modern">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="report-card-content-modern">
                                <h3 class="report-card-title-modern">Preventive Maintenance Checklist</h3>
                                <p class="report-card-description-modern">Generate preventive maintenance checklist and corrective action record form.</p>
                            </div>
                            <div class="report-card-arrow-modern">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-card-modern {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 20px;
    min-height: 120px;
}

.report-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: #28a745;
}

.report-card-icon-modern {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.report-card-modern:hover .report-card-icon-modern {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
}

.report-card-icon-modern i {
    font-size: 28px;
    color: #ffffff;
}

.report-card-content-modern {
    flex: 1;
    min-width: 0;
}

.report-card-title-modern {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 8px;
    line-height: 1.4;
}

.report-card-description-modern {
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0;
}

.report-card-arrow-modern {
    color: #6c757d;
    font-size: 1rem;
    opacity: 0.5;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.report-card-modern:hover .report-card-arrow-modern {
    opacity: 1;
    transform: translateX(4px);
    color: #28a745;
}

@media (max-width: 768px) {
    .report-card-modern {
        padding: 25px;
    }
    
    .report-card-icon-modern {
        width: 56px;
        height: 56px;
    }
    
    .report-card-icon-modern i {
        font-size: 24px;
    }
    
    .report-card-title-modern {
        font-size: 1.1rem;
    }
}
</style>

<?php require_once 'footer.php'; ?>

