<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$page_title = 'Kanban Dashboard';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-tasks text-danger"></i> ICT Service Request & Task Board
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Manage service requests from departments, tasks, and maintenance records
                    </p>
                </div>
                <div class="ms-auto" style="min-width: 220px;">
                    <label for="requestTypeFilter" class="form-label text-uppercase text-muted small mb-1">
                        Filter by request type
                    </label>
                    <select id="requestTypeFilter" class="form-select form-select-sm">
                        <option value="all" selected>Show all items</option>
                        <option value="service_request">Service Requests</option>
                        <option value="system_request">System Requests</option>
                    </select>
                </div>
            </div>

            <div id="alert-container"></div>

            <!-- Statistics Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie text-danger"></i> My Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-3">
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-blue clickable-stat" onclick="filterByType('equipment')" title="Click to view equipment">
                                        <i class="fas fa-desktop fa-3x mb-3"></i>
                                        <h3 class="mb-2" id="kanban-stat-equipment">0</h3>
                                        <small class="text-muted">Equipment Assigned</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-blue clickable-stat" onclick="filterByType('task')" title="Click to filter tasks">
                                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                                        <h3 class="mb-2" id="kanban-stat-tasks">0</h3>
                                        <small class="text-muted">Tasks Assigned</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-yellow clickable-stat" onclick="filterByType('maintenance')" title="Click to filter maintenance">
                                        <i class="fas fa-tools fa-3x mb-3"></i>
                                        <h3 class="mb-2" id="kanban-stat-maintenance">0</h3>
                                        <small class="text-muted">Maintenance Records</small>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-green clickable-stat" onclick="filterByMonth()" title="Click to filter current month items">
                                        <i class="fas fa-calendar fa-3x mb-3"></i>
                                        <h3 class="mb-2" id="kanban-stat-month"><?php echo date('M Y'); ?></h3>
                                        <small class="text-muted">Current Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kanban-board-container">
                <!-- Pending -->
                <div class="kanban-column-wrapper">
                    <div class="card kanban-column">
                        <div class="card-header kanban-header kanban-header-pending">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Pending
                                <span class="badge bg-dark ms-2" id="pending-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="pending" id="pending-tasks"></div>
                            <div class="empty-state" id="empty-pending">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No Pending</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="kanban-column-wrapper">
                    <div class="card kanban-column">
                        <div class="card-header kanban-header kanban-header-progress">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs"></i> In Progress
                                <span class="badge bg-light text-dark ms-2" id="in-progress-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="in_progress" id="in-progress-tasks"></div>
                            <div class="empty-state" id="empty-in-progress">
                                <i class="fas fa-cogs fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No In Progress</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="kanban-column-wrapper">
                    <div class="card kanban-column">
                        <div class="card-header kanban-header kanban-header-complete">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle"></i> Completed
                                <span class="badge bg-light text-dark ms-2" id="completed-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="completed" id="completed-tasks"></div>
                            <div class="empty-state" id="empty-completed">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p class="text-muted mb-0">No Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Complete Modal for both Tasks & Maintenance -->
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-comment"></i> Complete Item - Add Remarks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="completeForm">
          <input type="hidden" name="item_id" id="completeItemId">
          <input type="hidden" name="item_type" id="completeItemType"> <!-- task / maintenance -->
          <div class="mb-3">
            <label class="form-label">End Time</label>
            <input type="text" class="form-control" id="completeEndTime" readonly>
            <small class="text-muted">This is automatically set when you mark the item as complete</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" id="completeRemarks" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-check"></i> Confirm Complete
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Observe Equipment Modal -->
<div class="modal fade" id="observeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-eye"></i> Observe Equipment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="observeRequestDetails"></div>
        
        <!-- Observation Timer -->
        <div class="card mt-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-clock"></i> Observation Timer</h6>
          </div>
          <div class="card-body">
            <div class="row text-center mb-3">
              <div class="col-4">
                <div class="timer-display">
                  <label class="form-label text-muted small">Start Time</label>
                  <div class="timer-value" id="observeStartTime">--:--:--</div>
                </div>
              </div>
              <div class="col-4">
                <div class="timer-display">
                  <label class="form-label text-muted small">Duration</label>
                  <div class="timer-value" id="observeDuration">00:00:00</div>
                </div>
              </div>
              <div class="col-4">
                <div class="timer-display">
                  <label class="form-label text-muted small">Stop Time</label>
                  <div class="timer-value" id="observeStopTime">--:--:--</div>
                </div>
              </div>
            </div>
            <div class="d-flex gap-2 justify-content-center">
              <button type="button" class="btn btn-success" id="observeStartBtn" onclick="startObservationTimer()">
                <i class="fas fa-play"></i> Start
              </button>
              <button type="button" class="btn btn-warning" id="observeStopBtn" onclick="stopObservationTimer()" disabled>
                <i class="fas fa-stop"></i> Stop
              </button>
              <button type="button" class="btn btn-secondary" id="observeResetBtn" onclick="resetObservationTimer()">
                <i class="fas fa-redo"></i> Reset
              </button>
            </div>
            <input type="hidden" id="observeStartTimeValue">
            <input type="hidden" id="observeStopTimeValue">
            <input type="hidden" id="observeDurationValue">
          </div>
        </div>
        
        <div class="alert alert-info mt-3">
          <i class="fas fa-info-circle"></i> <strong>Next Step:</strong> After observing the equipment, assign the appropriate support level (L1-L4) based on the complexity of the issue.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="openServiceRequestModalFromObserve()">
          <i class="fas fa-arrow-right"></i> Assign Support Level
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Assign Support Level Modal -->
<div class="modal fade" id="updateServiceRequestModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-layer-group"></i> Assign Support Level & Processing Time</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Based on your observation, assign the appropriate support level and estimated processing time.
        </div>
        <form id="updateServiceRequestForm">
          <input type="hidden" id="serviceRequestId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Support Level <span class="text-danger">*</span></label>
              <select class="form-select" id="serviceRequestSupportLevel" required>
                <option value="">Select Support Level</option>
                <option value="L1" data-time="1 hour and 5 minutes">L1 - Basic Support (General support, basic software installation, peripheral setup)</option>
                <option value="L2" data-time="2 hours and 5 minutes">L2 - Intermediate Support (Intermediate troubleshooting, software installation/configuration, basic hardware repairs)</option>
                <option value="L3" data-time="2 days and 5 minutes">L3 - Advanced Support (Complex troubleshooting, hardware repair)</option>
                <option value="L4" data-time="5 days and 5 minutes">L4 - Expert Support (Network setups - installation of new network equipment and peripherals)</option>
              </select>
              <small class="text-muted">Select based on the complexity observed during inspection</small>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Processing Time <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="serviceRequestProcessingTime" placeholder="Auto-filled based on support level" required>
              <small class="text-muted">Processing time is automatically set based on support level (can be adjusted if needed)</small>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Initial Notes</label>
            <textarea class="form-control" id="serviceRequestAccomplishment" rows="3" placeholder="Add any initial notes about the observation or planned work..."></textarea>
          </div>
          
          <!-- Observation Timer Data (hidden, populated from observe modal) -->
          <input type="hidden" id="serviceRequestObserveStartTime">
          <input type="hidden" id="serviceRequestObserveStopTime">
          <input type="hidden" id="serviceRequestObserveDuration">
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> This will save the support level and keep the task in "In Progress". Use the "Mark Complete" button to finish the task.
          </div>
          
          <button type="submit" class="btn btn-warning w-100">
            <i class="fas fa-save"></i> Save Support Level & Processing Time
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Complete Service Request Modal -->
<div class="modal fade" id="completeServiceRequestModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-check-circle"></i> Complete Service Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-success">
          <i class="fas fa-info-circle"></i> <strong>Final Step:</strong> Provide all required information to complete this service request. The department will be notified and can evaluate your work.
        </div>
        <div class="alert alert-info">
          <i class="fas fa-clipboard-check"></i> <strong>Note:</strong> After completion, the department can submit their evaluation survey at <strong>checklist.php</strong>. You can view their ratings and feedback on the completed service request card in the kanban board.
        </div>
        <form id="completeServiceRequestForm">
          <input type="hidden" id="completeServiceRequestId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Support Level <span class="text-danger">*</span></label>
              <select class="form-select" id="completeServiceRequestSupportLevel" required>
                <option value="">Select Support Level</option>
                <option value="L1" data-time="1 hour and 5 minutes">L1 - Basic Support</option>
                <option value="L2" data-time="2 hours and 5 minutes">L2 - Intermediate Support</option>
                <option value="L3" data-time="2 days and 5 minutes">L3 - Advanced Support</option>
                <option value="L4" data-time="5 days and 5 minutes">L4 - Expert Support</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Processing Time <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="completeServiceRequestProcessingTime" placeholder="Auto-filled based on support level" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Accomplishment <span class="text-danger">*</span></label>
            <textarea class="form-control" id="completeServiceRequestAccomplishment" rows="4" placeholder="Describe in detail what was accomplished, what work was performed, and what was fixed or resolved..." required></textarea>
            <small class="text-muted">Be specific about the work completed</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Final Remarks <span class="text-danger">*</span></label>
            <textarea class="form-control" id="completeServiceRequestRemarks" rows="3" placeholder="Add any final remarks, recommendations, or notes for the client..." required></textarea>
            <small class="text-muted">This will be visible to the department</small>
          </div>
          
          <!-- Observation Timer Data (hidden, populated from saved data or assign modal) -->
          <input type="hidden" id="completeObserveStartTime">
          <input type="hidden" id="completeObserveStopTime">
          <input type="hidden" id="completeObserveDuration">
          
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-check-circle"></i> Mark as Complete & Notify Department
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Maintenance Observe Modal -->
<div class="modal fade" id="maintenanceObserveModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-eye"></i> Observe Maintenance Task</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="maintenanceObserveDetails"></div>
        <div class="alert alert-info mt-3">
          <i class="fas fa-info-circle"></i> After reviewing the task, assign the appropriate support level (L1-L4) to start working with an SLA timeline.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="openMaintenanceAssignModalFromObserve()">
          <i class="fas fa-arrow-right"></i> Assign Support Level
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Maintenance Assign Support Level Modal -->
<div class="modal fade" id="maintenanceAssignModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-layer-group"></i> Assign Maintenance Support Level & Timeline</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i> Select the difficulty level of this maintenance job. The system will create a deadline based on your choice.
        </div>
        <form id="maintenanceAssignForm">
          <input type="hidden" id="maintenanceAssignId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Support Level <span class="text-danger">*</span></label>
              <select class="form-select" id="maintenanceSupportLevel" required>
                <option value="">Select Support Level</option>
                <option value="L1" data-time="1 hour and 5 minutes" data-minutes="65">L1 - Basic Support</option>
                <option value="L2" data-time="2 hours and 5 minutes" data-minutes="125">L2 - Intermediate Support</option>
                <option value="L3" data-time="2 days and 5 minutes" data-minutes="2885">L3 - Advanced Support</option>
                <option value="L4" data-time="5 days and 5 minutes" data-minutes="7205">L4 - Expert Support</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Processing Time <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="maintenanceProcessingTime" placeholder="Auto-filled based on support level" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Observation Notes</label>
            <textarea class="form-control" id="maintenanceObservationNotes" rows="3" placeholder="Add observation notes or planned actions (optional)"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Start Time</label>
            <input type="text" class="form-control" id="maintenanceStartTime" readonly>
            <small class="text-muted">This is automatically set when you assign the support level</small>
          </div>
          <button type="submit" class="btn btn-warning w-100">
            <i class="fas fa-save"></i> Save Support Level & Start Maintenance
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Feedback Preview Modal -->
<div class="modal fade" id="feedbackPreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-star"></i> Feedback Preview - Satisfaction Levels</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="feedbackPreviewModalBody">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading feedback details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
let autoRefreshInterval;
let currentUserId = <?php echo $user_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize empty states as hidden
    ['pending','in_progress','completed'].forEach(status => {
        const emptyState = document.getElementById(`empty-${status.replace('_','-')}`);
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    });
    
    // Reset timer when observe modal is closed
    const observeModal = document.getElementById('observeModal');
    if (observeModal) {
        observeModal.addEventListener('hidden.bs.modal', function() {
            if (observationTimer) {
                stopObservationTimer();
            }
        });
    }
    
    loadAllItems();
    startAutoRefresh();
    loadStatistics();

    const filterSelect = document.getElementById('requestTypeFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            currentFilter.type = this.value;
            currentFilter.month = null; // Reset month filter when dropdown changes
            
            // Remove active class from all stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('stat-active');
            });
            
            applyRequestFilter();
        });
    }

    // Handle Complete Modal submit
    document.getElementById('completeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('completeItemId').value;
        const type = document.getElementById('completeItemType').value;
        const remarks = document.getElementById('completeRemarks').value.trim();
        if (!remarks) { alert("Please enter remarks."); return; }
        
        if (type === 'system_request') {
            fetch('api/task_webhook.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'update_system_request_status',
                    request_id: id,
                    new_status: 'completed',
                    remarks: remarks
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('System request completed!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
                    loadAllItems();
                } else {
                    showAlert('Failed: ' + data.message, 'danger');
                }
            })
            .catch(() => showAlert('Error completing system request', 'danger'));
        } else if (type === 'task') {
            sendTaskStatusUpdate(id, 'completed', remarks);
            bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
        } else {
            const endTime = document.getElementById('completeEndTime').value;
            sendMaintenanceStatusUpdate(id, 'completed', remarks, endTime);
            bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
        }
    });

    // Auto-populate processing time based on support level (Assign Support Level Modal)
    document.getElementById('serviceRequestSupportLevel').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const processingTime = selectedOption.getAttribute('data-time');
        if (processingTime) {
            document.getElementById('serviceRequestProcessingTime').value = processingTime;
        }
    });

    // Auto-populate processing time based on support level (Complete Modal)
    document.getElementById('completeServiceRequestSupportLevel').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const processingTime = selectedOption.getAttribute('data-time');
        if (processingTime) {
            document.getElementById('completeServiceRequestProcessingTime').value = processingTime;
        }
    });

    const maintenanceSupportSelect = document.getElementById('maintenanceSupportLevel');
    if (maintenanceSupportSelect) {
        maintenanceSupportSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const processingTime = selectedOption.getAttribute('data-time');
            if (processingTime) {
                document.getElementById('maintenanceProcessingTime').value = processingTime;
            }
        });
    }

    const maintenanceAssignForm = document.getElementById('maintenanceAssignForm');
    if (maintenanceAssignForm) {
        maintenanceAssignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const maintenanceId = document.getElementById('maintenanceAssignId').value;
            const supportLevel = document.getElementById('maintenanceSupportLevel').value;
            const processingTime = document.getElementById('maintenanceProcessingTime').value;
            const notes = document.getElementById('maintenanceObservationNotes').value.trim();

            if (!supportLevel || !processingTime) {
                alert('Please select a support level and processing time.');
                return;
            }

            const startTime = document.getElementById('maintenanceStartTime').value;
            
            fetch('api/task_webhook.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'assign_maintenance_support_level',
                    maintenance_id: maintenanceId,
                    support_level: supportLevel,
                    processing_time: processingTime,
                    notes: notes || null,
                    start_time: startTime
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAlert('Maintenance support level assigned and deadline set!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('maintenanceAssignModal')).hide();
                    loadAllItems();
                } else {
                    showAlert('Failed: ' + data.message, 'danger');
                }
            })
            .catch(() => showAlert('Error assigning maintenance support level', 'danger'));
        });
    }

    // Handle Update Service Request Form (Assign Support Level)
    document.getElementById('updateServiceRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const requestId = document.getElementById('serviceRequestId').value;
        const supportLevel = document.getElementById('serviceRequestSupportLevel').value;
        const processingTime = document.getElementById('serviceRequestProcessingTime').value;
        const accomplishment = document.getElementById('serviceRequestAccomplishment').value.trim();
        
        // Get observation timer data (from observe modal or assign modal hidden fields)
        const observeStartTime = document.getElementById('serviceRequestObserveStartTime').value || 
                                 (document.getElementById('observeStartTimeValue') ? document.getElementById('observeStartTimeValue').value : '');
        const observeStopTime = document.getElementById('serviceRequestObserveStopTime').value || 
                               (document.getElementById('observeStopTimeValue') ? document.getElementById('observeStopTimeValue').value : '');
        const observeDuration = document.getElementById('serviceRequestObserveDuration').value || 
                               (document.getElementById('observeDurationValue') ? document.getElementById('observeDurationValue').value : '');
        
        if (!supportLevel || !processingTime) {
            alert('Please select a support level and enter processing time.');
            return;
        }
        
        fetch('api/task_webhook.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_service_request',
                request_id: requestId,
                new_status: 'In Progress', // Keep in In Progress, don't complete
                support_level: supportLevel,
                processing_time: processingTime,
                accomplishment: accomplishment || null,
                observe_start_time: observeStartTime || null,
                observe_stop_time: observeStopTime || null,
                observe_duration: observeDuration || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Support level and processing time saved! Task remains in progress. Click "Mark Complete" when finished.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('updateServiceRequestModal')).hide();
                loadAllItems();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error updating request', 'danger'));
    });

    // Handle Complete Service Request Form
    document.getElementById('completeServiceRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const requestId = document.getElementById('completeServiceRequestId').value;
        const supportLevel = document.getElementById('completeServiceRequestSupportLevel').value;
        const processingTime = document.getElementById('completeServiceRequestProcessingTime').value;
        const accomplishment = document.getElementById('completeServiceRequestAccomplishment').value.trim();
        const remarks = document.getElementById('completeServiceRequestRemarks').value.trim();
        
        // Get observation timer data (from complete modal hidden fields, assign modal, or observe modal)
        const observeStartTime = document.getElementById('completeObserveStartTime').value || 
                                 document.getElementById('serviceRequestObserveStartTime').value || 
                                 (document.getElementById('observeStartTimeValue') ? document.getElementById('observeStartTimeValue').value : '');
        const observeStopTime = document.getElementById('completeObserveStopTime').value || 
                               document.getElementById('serviceRequestObserveStopTime').value || 
                               (document.getElementById('observeStopTimeValue') ? document.getElementById('observeStopTimeValue').value : '');
        const observeDuration = document.getElementById('completeObserveDuration').value || 
                               document.getElementById('serviceRequestObserveDuration').value || 
                               (document.getElementById('observeDurationValue') ? document.getElementById('observeDurationValue').value : '');
        
        if (!supportLevel || !processingTime || !accomplishment || !remarks) {
            alert("Please fill in all required fields.");
            return;
        }
        
        fetch('api/task_webhook.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_service_request',
                request_id: requestId,
                new_status: 'completed', // Only this button moves to completed
                support_level: supportLevel,
                processing_time: processingTime,
                accomplishment: accomplishment,
                remarks: remarks,
                observe_start_time: observeStartTime || null,
                observe_stop_time: observeStopTime || null,
                observe_duration: observeDuration || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Service request completed! The department can now submit their evaluation survey at checklist.php. You can view their ratings here once submitted.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('completeServiceRequestModal')).hide();
                loadAllItems();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error completing request', 'danger'));
    });
});

// Load Statistics
function loadStatistics() {
    fetch('get_statistics.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('kanban-stat-equipment').textContent = data.equipment_count || 0;
                document.getElementById('kanban-stat-tasks').textContent = data.task_count || 0;
                document.getElementById('kanban-stat-maintenance').textContent = data.maintenance_count || 0;
            } else {
                document.getElementById('kanban-stat-equipment').textContent = '0';
                document.getElementById('kanban-stat-tasks').textContent = '0';
                document.getElementById('kanban-stat-maintenance').textContent = '0';
            }
        })
        .catch(err => {
            console.error('Error fetching statistics:', err);
            document.getElementById('kanban-stat-equipment').textContent = '0';
            document.getElementById('kanban-stat-tasks').textContent = '0';
            document.getElementById('kanban-stat-maintenance').textContent = '0';
        });
}

// Refreshing
function startAutoRefresh() { autoRefreshInterval = setInterval(loadAllItems, 10000); }
function stopAutoRefresh() { clearInterval(autoRefreshInterval); }
async function loadAllItems() {
    const statuses = ['pending','in_progress','completed'];
    const allPromises = [];

    statuses.forEach(status => {
        allPromises.push(loadTasksByStatus(status));
        allPromises.push(loadMaintenanceByStatus(status));
        allPromises.push(loadServiceRequestsByStatus(status));
        allPromises.push(loadSystemRequestsByStatus(status));
    });

    try {
        await Promise.all(allPromises);
    } catch (error) {
        console.error('Error loading items:', error);
    } finally {
        applyRequestFilter();
    }
}

function updateStatusCounts() {
    ['pending','in_progress','completed'].forEach(status => {
        const container = document.getElementById(`${status.replace('_','-')}-tasks`);
        const badge = document.getElementById(`${status.replace('_','-')}-count`);
        const emptyState = document.getElementById(`empty-${status.replace('_','-')}`);
        
        if (!container || !badge) return;
        
        const totalItems = container.querySelectorAll('.task-card:not(.d-none)').length;
        badge.textContent = totalItems;
        
        // Show/hide empty state
        if (emptyState) {
            if (totalItems === 0) {
                emptyState.style.display = 'flex';
            } else {
                emptyState.style.display = 'none';
            }
        }
    });
}

let currentFilter = {
    type: 'all',
    month: null
};

function applyRequestFilter() {
    const filterSelect = document.getElementById('requestTypeFilter');
    // Only update currentFilter.type from dropdown if it's not already set by stat card click
    if (filterSelect && filterSelect.value !== 'all' && currentFilter.type === 'all') {
        currentFilter.type = filterSelect.value;
    }
    
    const cards = document.querySelectorAll('.task-card');

    cards.forEach(card => {
        const cardType = card.getAttribute('data-card-type') || 'task';
        let shouldShow = true;
        
        // Apply type filter (from dropdown or stat card)
        if (currentFilter.type !== 'all' && cardType !== currentFilter.type) {
            shouldShow = false;
        }
        
        // Apply month filter if active
        if (currentFilter.month && shouldShow) {
            const cardDate = card.getAttribute('data-created-date');
            if (cardDate) {
                try {
                    const cardDateObj = new Date(cardDate);
                    const filterMonth = currentFilter.month.getMonth();
                    const filterYear = currentFilter.month.getFullYear();
                    if (cardDateObj.getMonth() !== filterMonth || cardDateObj.getFullYear() !== filterYear) {
                        shouldShow = false;
                    }
                } catch (e) {
                    // Invalid date, hide card
                    shouldShow = false;
                }
            } else {
                shouldShow = false;
            }
        }
        
        if (shouldShow) {
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    });

    updateStatusCounts();
}

function filterByType(type) {
    // Reset month filter
    currentFilter.month = null;
    
    // Remove active class from all stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('stat-active');
    });
    
    // Set the filter dropdown
    const filterSelect = document.getElementById('requestTypeFilter');
    if (filterSelect) {
        if (type === 'equipment') {
            // Navigate to inventory page
            window.location.href = 'inventory.php';
            return;
        } else if (type === 'task') {
            // Toggle filter - if already filtering tasks, reset to all
            if (currentFilter.type === 'task') {
                currentFilter.type = 'all';
                filterSelect.value = 'all';
                showAlert('Showing all items.', 'info');
            } else {
                filterSelect.value = 'all';
                currentFilter.type = 'task';
                document.querySelectorAll('.stat-card')[1].classList.add('stat-active');
                showAlert('Showing all tasks assigned to you.', 'info');
            }
        } else if (type === 'maintenance') {
            // Toggle filter - if already filtering maintenance, reset to all
            if (currentFilter.type === 'maintenance') {
                currentFilter.type = 'all';
                filterSelect.value = 'all';
                showAlert('Showing all items.', 'info');
            } else {
                filterSelect.value = 'all';
                currentFilter.type = 'maintenance';
                document.querySelectorAll('.stat-card')[2].classList.add('stat-active');
                showAlert('Showing all maintenance records.', 'info');
            }
        }
    }
    
    // Apply filter
    applyRequestFilter();
    
    // Scroll to kanban board
    setTimeout(() => {
        const kanbanColumn = document.querySelector('.kanban-column');
        if (kanbanColumn) {
            kanbanColumn.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);
}

function filterByMonth() {
    // Toggle month filter - if already filtering by month, reset
    if (currentFilter.month) {
        currentFilter.month = null;
        currentFilter.type = 'all';
        const filterSelect = document.getElementById('requestTypeFilter');
        if (filterSelect) {
            filterSelect.value = 'all';
        }
        document.querySelectorAll('.stat-card')[3].classList.remove('stat-active');
        showAlert('Showing all items.', 'info');
    } else {
        // Reset type filter
        currentFilter.type = 'all';
        const filterSelect = document.getElementById('requestTypeFilter');
        if (filterSelect) {
            filterSelect.value = 'all';
        }
        
        // Set current month filter
        currentFilter.month = new Date();
        document.querySelectorAll('.stat-card')[3].classList.add('stat-active');
        showAlert('Showing items from ' + new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' }), 'info');
    }
    
    // Apply filter
    applyRequestFilter();
    
    // Scroll to kanban board
    setTimeout(() => {
        const kanbanColumn = document.querySelector('.kanban-column');
        if (kanbanColumn) {
            kanbanColumn.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 100);
}

// ---------------- TASKS ----------------
function loadTasksByStatus(status) {
    return fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_tasks', status: status, user_id: currentUserId})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderTasks(status, data.data);
        }
        return Promise.resolve();
    });
}

function renderTasks(status, tasks) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    // Remove only task cards
    container.querySelectorAll('[data-task-id]').forEach(el => el.remove());
    // Add new task cards
    tasks.forEach(task => {
        const existing = container.querySelector(`[data-task-id="${task.id}"]`);
        if (!existing) {
            container.insertAdjacentHTML('beforeend', createTaskElement(task));
        }
    });
    // Count will be updated by loadAllItems after all items are loaded
}

function createTaskElement(task) {
    const dueDate = new Date(task.due_date).toLocaleDateString();
    const createdDate = new Date(task.created_at).toLocaleDateString();

    return `
    <div class="task-card ${task.status === 'completed' ? 'completed' : ''}" data-task-id="${task.id}" data-card-type="task" data-created-date="${task.created_at}">
        <div class="task-header">
            <h6 class="task-title">${escapeHtml(task.title)}</h6>
            <span class="priority-badge priority-${task.priority}">${task.priority}</span>
        </div>
        <p class="task-description">${escapeHtml(task.description)}</p>
        <div class="task-meta">
            <small class="text-muted">
                <i class="fas fa-user"></i> ${escapeHtml(task.assigned_to_name)}<br>
                <i class="fas fa-calendar"></i> Due: ${dueDate}<br>
                <i class="fas fa-clock"></i> Created: ${createdDate}
            </small>
        </div>
        ${task.status === 'completed' && task.remarks ? `<div><strong>Remarks:</strong> ${escapeHtml(task.remarks)}</div>` : ''}
        ${task.status === 'in_progress' ? `
            <div class="task-actions">
                <button class="btn btn-sm btn-warning" onclick="openCompleteModal(${task.id}, 'task')">Complete</button>
            </div>` : task.status === 'pending' ? `
            <div class="task-actions">
                <button class="btn btn-sm btn-primary" onclick="updateTaskStatus(${task.id}, 'in_progress')">Accept Request</button>
            </div>` : `
            <div class="task-actions">
                <button class="btn btn-sm btn-success" onclick="deleteTask(${task.id})">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>`}
    </div>`;
}

// ---------------- MAINTENANCE ----------------
function loadMaintenanceByStatus(status) {
    return fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_maintenance', user_id: currentUserId})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const records = data.data.filter(r => r.status === status);
            renderMaintenance(status, records);
        }
        return Promise.resolve();
    });
}

function renderMaintenance(status, records) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    // Remove only maintenance cards
    container.querySelectorAll('[data-maintenance-id]').forEach(el => el.remove());
    // Add new maintenance cards
    records.forEach(r => container.insertAdjacentHTML('beforeend', createMaintenanceElement(r)));
    // Count will be updated by loadAllItems after all items are loaded
}

function createMaintenanceElement(record) {
    const startDate = record.start_date ? new Date(record.start_date).toLocaleDateString() : 'N/A';
    const endDate = record.end_date ? new Date(record.end_date).toLocaleDateString() : 'N/A';
    const costValue = record.cost ? parseFloat(record.cost) : 0;
    const formattedCost = costValue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const equipmentName = record.equipment_type ? escapeHtml(record.equipment_type) : 'Maintenance Task';
    const descriptionBlock = record.description
        ? `<div class="mb-2"><strong><i class="fas fa-clipboard-list"></i> Notes:</strong> ${escapeHtml(record.description)}</div>`
        : '';
    const technicianName = record.assigned_to_name ? escapeHtml(record.assigned_to_name) : 'Unassigned';
    const statusClass = record.status === 'completed' ? 'completed' : '';
    const timelineInfo = buildMaintenanceTimeline(record);

    return `
    <div class="task-card maintenance-card ${statusClass}" data-maintenance-id="${record.id}" data-card-type="maintenance" data-created-date="${record.start_date || record.created_at || ''}">
        <div class="task-header">
            <h6 class="task-title">
                <i class="fas fa-tools text-warning"></i> ${equipmentName}
            </h6>
            <span class="priority-badge priority-medium">Maintenance</span>
        </div>

        <div class="service-request-info maintenance-info">
            <div class="mb-2">
                <strong><i class="fas fa-wrench text-warning"></i> Type:</strong> ${escapeHtml(record.maintenance_type)}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-user-cog text-warning"></i> Technician:</strong> ${technicianName}
            </div>
            ${record.support_level ? `
            <div class="mb-2">
                <strong><i class="fas fa-layer-group"></i> Support Level:</strong>
                <span class="badge bg-info">${escapeHtml(record.support_level)}</span>
            </div>` : ''}
            ${record.processing_time ? `
            <div class="mb-2">
                <strong><i class="fas fa-clock"></i> Processing Time:</strong> ${escapeHtml(record.processing_time)}
            </div>` : ''}
            ${descriptionBlock}
        </div>

        <div class="task-meta">
            <div><i class="fas fa-calendar"></i> ${startDate} → ${endDate}</div>
            <div><i class="fas fa-coins"></i> ₱${formattedCost}</div>
            ${timelineInfo || ''}
        </div>

        <div class="task-actions mt-2">
            ${record.status === 'pending' ? `
            <button class="btn btn-sm btn-primary w-100" onclick="acceptMaintenanceRequest(${record.id})">
                <i class="fas fa-check"></i> Accept Request
            </button>` : record.status === 'completed' ? `
            <button class="btn btn-sm btn-success w-100" onclick="deleteMaintenance(${record.id})">
                <i class="fas fa-trash"></i> Remove
            </button>` : `
            <button class="btn btn-sm btn-warning w-100 mb-2" onclick="openMaintenanceObserveModal(${record.id})">
                <i class="fas fa-eye"></i> Observe Equipment
            </button>
            <button class="btn btn-sm btn-warning w-100 mb-2" onclick="openMaintenanceAssignModal(${record.id})">
                <i class="fas fa-layer-group"></i> Assign Support Level
            </button>
            <button class="btn btn-sm btn-warning w-100" onclick="openCompleteMaintenanceModal(${record.id})">
                <i class="fas fa-check-circle"></i> Mark Complete
            </button>`}
        </div>
    </div>`;
}

// ---------------- TASKS ----------------
function updateTaskStatus(id, newStatus) {
    if (newStatus === 'completed') {
        openCompleteModal(id, 'task');
    } else {
        sendTaskStatusUpdate(id, newStatus);
    }
}

function sendTaskStatusUpdate(id, status, remarks = '') {
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update_status',
            task_id: id,
            new_status: status,
            remarks: remarks
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Task updated!', 'success');
            loadAllItems();
            loadStatistics();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error updating task', 'danger'));
}

function deleteTask(taskId) {
    if (!confirm('Remove this completed task from the board? This cannot be undone.')) {
        return;
    }
    
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'delete_task',
            task_id: taskId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Task removed from board.', 'success');
            loadAllItems();
            loadStatistics();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error removing task', 'danger'));
}

// ---------------- MAINTENANCE ----------------
function updateMaintenanceStatus(maintenanceId, newStatus) {
    if (newStatus === 'completed') {
        document.getElementById('completeMaintenanceId').value = maintenanceId;
        document.getElementById('maintenanceRemarks').value = '';
        new bootstrap.Modal(document.getElementById('completeMaintenanceModal')).show();
    } else {
        if (confirm('Are you sure you want to update this maintenance status?')) {
            sendMaintenanceStatusUpdate(maintenanceId, newStatus);
        }
    }
}

function sendMaintenanceStatusUpdate(id, status, remarks = '', endTime = '') {
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update_maintenance_status',
            maintenance_id: id,
            new_status: status,
            remarks: remarks,
            end_time: endTime
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Maintenance updated!', 'success');
            loadAllItems();
            loadStatistics();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error updating maintenance', 'danger'));
}

function acceptMaintenanceRequest(maintenanceId) {
    if (confirm('Accept this maintenance request? It will be moved to In Progress.')) {
        fetch('api/task_webhook.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'accept_maintenance_request',
                maintenance_id: maintenanceId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Maintenance request accepted and moved to In Progress!', 'success');
                loadAllItems();
                loadStatistics();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error accepting maintenance request', 'danger'));
    }
}

function deleteMaintenance(maintenanceId) {
    if (!confirm('Remove this completed maintenance record from the board? This cannot be undone.')) {
        return;
    }
    
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'delete_maintenance',
            maintenance_id: maintenanceId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Maintenance record removed from board.', 'success');
            loadAllItems();
            loadStatistics();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error removing maintenance record', 'danger'));
}

// ---------------- SERVICE REQUESTS ----------------
function loadServiceRequestsByStatus(status) {
    return fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_requests', 
            status: status === 'pending' ? 'Pending' : (status === 'in_progress' ? 'In Progress' : 'Completed'),
            technician_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderServiceRequests(status, data.data);
        }
        return Promise.resolve();
    })
    .catch(err => {
        console.error('Error loading service requests:', err);
        return Promise.resolve();
    });
}

function renderServiceRequests(status, requests) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    // Remove existing service request cards for this status
    container.querySelectorAll('[data-service-request-id]').forEach(el => el.remove());
    // Add new service request cards
    requests.forEach(req => {
        container.insertAdjacentHTML('beforeend', createServiceRequestElement(req));
    });
    // Count will be updated by loadAllItems after all items are loaded
}

function createServiceRequestElement(request) {
    const createdDate = new Date(request.created_at).toLocaleDateString();
    const createdTime = new Date(request.created_at).toLocaleTimeString();
    const statusClass = request.status === 'completed' ? 'completed' : '';
    const isPending = request.status === 'pending';
    const isAssigned = request.technician_id && request.technician_id == currentUserId && request.status === 'in_progress';
    const surveyCount = parseInt(request.survey_count || 0, 10) || 0;
    const surveyAverage = request.survey_average ? parseFloat(request.survey_average).toFixed(1) : null;
    const surveyLatestAt = request.survey_latest_at ? new Date(request.survey_latest_at).toLocaleString() : null;
    const surveyLatestComment = request.survey_latest_comment ? escapeHtml(request.survey_latest_comment) : '';
    const timelineInfo = buildServiceRequestTimeline(request);
    
    return `
    <div class="task-card service-request-card ${statusClass}" data-service-request-id="${request.id}" data-card-type="service_request" data-created-date="${request.created_at}">
        <div class="task-header">
            <h6 class="task-title">
                <i class="fas fa-desktop text-primary"></i> ${escapeHtml(request.equipment || 'Service Request')}
            </h6>
            <span class="priority-badge priority-medium">Service Request</span>
        </div>
        
        <div class="service-request-info">
            <div class="mb-2">
                <strong><i class="fas fa-user"></i> Client:</strong> ${escapeHtml(request.client_name)}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-building"></i> Office:</strong> ${escapeHtml(request.office || 'N/A')}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-map-marker-alt"></i> Location:</strong> ${escapeHtml(request.location || 'N/A')}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-clipboard-list"></i> Requirements:</strong><br>
                <span class="text-muted small">${escapeHtml(request.requirements || 'N/A')}</span>
            </div>
            ${request.ict_srf_no ? `
            <div class="mb-2">
                <strong><i class="fas fa-tag"></i> ICT SRF No:</strong> ${escapeHtml(request.ict_srf_no)}
            </div>
            ` : ''}
            ${request.support_level ? `
            <div class="mb-2">
                <strong><i class="fas fa-layer-group"></i> Support Level:</strong> 
                <span class="badge bg-info">${escapeHtml(request.support_level)}</span>
            </div>
            ` : ''}
            ${request.processing_time ? `
            <div class="mb-2">
                <strong><i class="fas fa-clock"></i> Processing Time:</strong> ${escapeHtml(request.processing_time)}
            </div>
            ` : ''}
            ${request.accomplishment ? `
            <div class="mb-2">
                <strong><i class="fas fa-check-circle text-success"></i> Accomplishment:</strong><br>
                <span class="text-muted small">${escapeHtml(request.accomplishment)}</span>
            </div>
            ` : ''}
            ${request.remarks ? `
            <div class="mb-2">
                <strong><i class="fas fa-comment"></i> Remarks:</strong><br>
                <span class="text-muted small">${escapeHtml(request.remarks)}</span>
            </div>
            ` : ''}
        </div>
        
        <div class="task-meta">
            <small class="text-muted">
                <i class="fas fa-calendar-alt"></i> ${createdDate} ${createdTime}
            </small>
            ${timelineInfo}
        </div>
        
        ${request.status !== 'completed' ? `
            <div class="task-actions mt-2">
                ${isPending
                    ? `<button class="btn btn-sm btn-primary w-100" onclick="acceptServiceRequest(${request.id})">
                           <i class="fas fa-hand-paper"></i> Accept Request
                       </button>`
                    : isAssigned
                    ? `<div class="d-grid gap-2">
                           <button class="btn btn-sm btn-warning" onclick="openObserveModal(${request.id})">
                               <i class="fas fa-eye"></i> Observe Equipment
                           </button>
                           <button class="btn btn-sm btn-warning" onclick="openServiceRequestModal(${request.id})">
                               <i class="fas fa-edit"></i> Assign Support Level
                           </button>
                           <button class="btn btn-sm btn-warning" onclick="openCompleteServiceRequestModal(${request.id})">
                               <i class="fas fa-check-circle"></i> Mark Complete
                           </button>
                       </div>`
                    : ''}
            </div>
        ` : `
            <div class="mt-3 d-grid gap-2">
                <span class="badge bg-success mb-2"><i class="fas fa-check"></i> Completed</span>
                ${request.accomplishment ? `
                    <div class="mt-2">
                        <small class="text-muted text-uppercase fw-bold">Work Done</small>
                        <p class="small text-muted mb-0">${escapeHtml(request.accomplishment)}</p>
                    </div>
                ` : ''}
                ${surveyCount > 0 ? `
                    <div class="feedback-card mt-3 p-3 clickable-feedback" onclick="viewFeedbackPreview(${request.id})" style="cursor: pointer; border: 2px solid #198754; border-radius: 8px; background: linear-gradient(135deg, #d1e7dd 0%, #ffffff 100%);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><i class="fas fa-star text-warning"></i> Department Rating & Feedback</strong>
                            ${surveyAverage ? `<span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 0.5rem 0.75rem;">
                                <i class="fas fa-star"></i> ${surveyAverage}/5.0
                            </span>` : ''}
                        </div>
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                <strong>Average Rating:</strong>
                                <span class="badge bg-success ms-2">${surveyAverage}/5.0</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-success me-2"></i>
                                <strong>Total Responses:</strong>
                                <span class="badge bg-success ms-2">${surveyCount} ${surveyCount === 1 ? 'response' : 'responses'}</span>
                            </div>
                        </div>
                        ${surveyLatestComment ? `
                            <div class="mt-2 p-2 bg-light rounded">
                                <strong><i class="fas fa-comment text-success"></i> Latest Comment:</strong>
                                <p class="text-muted small mb-1 mt-1">${surveyLatestComment}</p>
                            </div>
                        ` : ''}
                        ${surveyLatestAt ? `<small class="text-muted d-block mt-2"><i class="fas fa-clock"></i> Submitted: ${surveyLatestAt}</small>` : ''}
                        <div class="mt-2">
                            <span class="badge bg-success">
                                <i class="fas fa-eye"></i> Click to view detailed ratings
                            </span>
                        </div>
                    </div>
                ` : `
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <i class="fas fa-clipboard-check"></i> Awaiting department survey feedback. 
                        <br><small>The department can submit their evaluation at <strong>checklist.php</strong></small>
                    </div>
                `}
                <button class="btn btn-success btn-sm" onclick="deleteServiceRequest(${request.id})">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        `}
    </div>`;
}

// ---------------- SYSTEM REQUESTS ----------------
function loadSystemRequestsByStatus(status) {
    return fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_system_requests', 
            status: status === 'pending' ? 'Pending' : (status === 'in_progress' ? 'In Progress' : 'Completed'),
            technician_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderSystemRequests(status, data.data);
        }
        return Promise.resolve();
    })
    .catch(err => {
        console.error('Error loading system requests:', err);
        return Promise.resolve();
    });
}

function renderSystemRequests(status, requests) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    // Remove existing system request cards for this status
    container.querySelectorAll('[data-system-request-id]').forEach(el => el.remove());
    // Add new system request cards
    requests.forEach(req => {
        container.insertAdjacentHTML('beforeend', createSystemRequestElement(req));
    });
    // Count will be updated by loadAllItems after all items are loaded
}

function createSystemRequestElement(request) {
    const createdDate = new Date(request.created_at).toLocaleDateString();
    const createdTime = new Date(request.created_at).toLocaleTimeString();
    const statusClass = request.status === 'completed' ? 'completed' : '';
    const isPending = request.status === 'pending' && !request.technician_id;
    const isAssigned = request.technician_id && request.technician_id == currentUserId && (request.status === 'pending' || request.status === 'in_progress');
    
    return `
    <div class="task-card system-request-card ${statusClass}" data-system-request-id="${request.id}" data-card-type="system_request" data-created-date="${request.created_at}">
        <div class="task-header">
            <h6 class="task-title">
                <i class="fas fa-cog text-info"></i> ${escapeHtml(request.system_name || 'System Request')}
            </h6>
            <span class="priority-badge priority-medium">System Request</span>
        </div>
        
        <div class="service-request-info">
            <div class="mb-2">
                <strong><i class="fas fa-building"></i> Office:</strong> ${escapeHtml(request.requesting_office || 'N/A')}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-tag"></i> Type:</strong> ${escapeHtml(request.type_of_request || 'N/A')}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-exclamation-triangle"></i> Urgency:</strong> ${escapeHtml(request.urgency || 'N/A')}
            </div>
            <div class="mb-2">
                <strong><i class="fas fa-clipboard-list"></i> Description:</strong><br>
                <span class="text-muted small">${escapeHtml(request.description || 'N/A')}</span>
            </div>
            ${request.remarks ? `
            <div class="mb-2">
                <strong><i class="fas fa-comment"></i> Remarks:</strong><br>
                <span class="text-muted small">${escapeHtml(request.remarks)}</span>
            </div>
            ` : ''}
        </div>
        
        <div class="task-meta">
            <small class="text-muted">
                <i class="fas fa-calendar-alt"></i> ${createdDate} ${createdTime}
            </small>
        </div>
        
        ${request.status !== 'completed' ? `
            <div class="task-actions mt-2">
                ${isPending
                    ? `<button class="btn btn-sm btn-primary w-100" onclick="acceptSystemRequest(${request.id})">
                           <i class="fas fa-hand-paper"></i> Accept Request
                       </button>`
                    : isAssigned
                    ? `<div class="d-grid gap-2">
                           <button class="btn btn-sm btn-warning" onclick="openCompleteSystemRequestModal(${request.id})">
                               <i class="fas fa-check-circle"></i> Mark Complete
                           </button>
                       </div>`
                    : ''}
            </div>
        ` : `
            <div class="mt-3">
                <span class="badge bg-success mb-2"><i class="fas fa-check"></i> Completed</span>
            </div>
        `}
    </div>`;
}

function acceptSystemRequest(requestId) {
    if (confirm('Accept this system request?')) {
        fetch('api/task_webhook.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'accept_system_request',
                request_id: requestId,
                technician_id: currentUserId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('System request accepted and moved to In Progress!', 'success');
                loadAllItems();
                loadStatistics();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error accepting request', 'danger'));
    }
}

function openCompleteSystemRequestModal(requestId) {
    document.getElementById('completeItemId').value = requestId;
    document.getElementById('completeItemType').value = 'system_request';
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}

function acceptServiceRequest(requestId) {
    if (confirm('Accept this service request?')) {
        fetch('api/task_webhook.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'accept_service_request',
                request_id: requestId,
                technician_id: currentUserId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Service request accepted and moved to In Progress!', 'success');
                loadAllItems();
                loadStatistics();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error accepting request', 'danger'));
    }
}

let currentObserveRequestId = null;
let currentMaintenanceId = null;
let observationTimer = null;
let observationStartTime = null;
let observationElapsed = 0;

function startObservationTimer() {
    observationStartTime = new Date();
    observationElapsed = 0;
    
    document.getElementById('observeStartTime').textContent = observationStartTime.toLocaleTimeString();
    document.getElementById('observeStartTimeValue').value = observationStartTime.toISOString();
    document.getElementById('observeStopTime').textContent = '--:--:--';
    document.getElementById('observeStopTimeValue').value = '';
    document.getElementById('observeDuration').textContent = '00:00:00';
    document.getElementById('observeDurationValue').value = '';
    
    document.getElementById('observeStartBtn').disabled = true;
    document.getElementById('observeStopBtn').disabled = false;
    document.getElementById('observeResetBtn').disabled = true;
    
    observationTimer = setInterval(() => {
        observationElapsed += 1000;
        const duration = formatDuration(observationElapsed);
        document.getElementById('observeDuration').textContent = duration;
        document.getElementById('observeDurationValue').value = duration;
    }, 1000);
}

function stopObservationTimer() {
    if (observationTimer) {
        clearInterval(observationTimer);
        observationTimer = null;
    }
    
    const stopTime = new Date();
    document.getElementById('observeStopTime').textContent = stopTime.toLocaleTimeString();
    document.getElementById('observeStopTimeValue').value = stopTime.toISOString();
    
    document.getElementById('observeStartBtn').disabled = false;
    document.getElementById('observeStopBtn').disabled = true;
    document.getElementById('observeResetBtn').disabled = false;
}

function resetObservationTimer() {
    if (observationTimer) {
        clearInterval(observationTimer);
        observationTimer = null;
    }
    
    observationStartTime = null;
    observationElapsed = 0;
    
    document.getElementById('observeStartTime').textContent = '--:--:--';
    document.getElementById('observeStartTimeValue').value = '';
    document.getElementById('observeStopTime').textContent = '--:--:--';
    document.getElementById('observeStopTimeValue').value = '';
    document.getElementById('observeDuration').textContent = '00:00:00';
    document.getElementById('observeDurationValue').value = '';
    
    document.getElementById('observeStartBtn').disabled = false;
    document.getElementById('observeStopBtn').disabled = true;
    document.getElementById('observeResetBtn').disabled = true;
}

function formatDuration(ms) {
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

function openObserveModal(requestId) {
    currentObserveRequestId = requestId;
    // Reset timer when opening modal
    resetObservationTimer();
    // Fetch request details
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_requests',
            technician_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const request = data.data.find(r => r.id == requestId);
            if (request) {
                const deadlineString = request.processing_deadline ? new Date(request.processing_deadline).toLocaleString() : null;
                const timelineStatus = (request.completed_within_sla === null || request.completed_within_sla === undefined)
                    ? ''
                    : `<p><strong>Status vs Deadline:</strong> ${parseInt(request.completed_within_sla, 10) === 1 ? 'Completed on time' : 'Completed after deadline'}</p>`;
                const detailsHtml = `
                    <div class="card">
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle"></i> Service Request Details</h6>
                            <hr>
                            <p><strong>Client:</strong> ${escapeHtml(request.client_name)}</p>
                            <p><strong>Office:</strong> ${escapeHtml(request.office || 'N/A')}</p>
                            <p><strong>Location:</strong> ${escapeHtml(request.location || 'N/A')}</p>
                            <p><strong>Equipment:</strong> ${escapeHtml(request.equipment || 'N/A')}</p>
                            <p><strong>Requirements/Issue:</strong></p>
                            <p class="bg-light p-2 rounded">${escapeHtml(request.requirements || 'N/A')}</p>
                            <p><strong>Date/Time of Request:</strong> ${new Date(request.date_time_call || request.created_at).toLocaleString()}</p>
                            ${request.support_level ? `<p><strong>Support Level:</strong> ${escapeHtml(request.support_level)}</p>` : ''}
                            ${request.processing_time ? `<p><strong>Processing Time:</strong> ${escapeHtml(request.processing_time)}</p>` : ''}
                            ${deadlineString ? `<p><strong>Deadline:</strong> ${deadlineString}</p>` : ''}
                            ${timelineStatus}
                        </div>
                    </div>
                `;
                document.getElementById('observeRequestDetails').innerHTML = detailsHtml;
                new bootstrap.Modal(document.getElementById('observeModal')).show();
            }
        }
    })
    .catch(err => {
        showAlert('Error loading request details', 'danger');
    });
}

function openServiceRequestModalFromObserve() {
    // Stop timer if running and preserve timer data
    if (observationTimer) {
        stopObservationTimer();
    }
    
    // Transfer timer data to assign modal
    const startTime = document.getElementById('observeStartTimeValue').value;
    const stopTime = document.getElementById('observeStopTimeValue').value;
    const duration = document.getElementById('observeDurationValue').value;
    
    if (startTime) {
        document.getElementById('serviceRequestObserveStartTime').value = startTime;
    }
    if (stopTime) {
        document.getElementById('serviceRequestObserveStopTime').value = stopTime;
    }
    if (duration) {
        document.getElementById('serviceRequestObserveDuration').value = duration;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('observeModal')).hide();
    if (currentObserveRequestId) {
        openServiceRequestModal(currentObserveRequestId);
    }
}

function openMaintenanceObserveModal(maintenanceId) {
    currentMaintenanceId = maintenanceId;
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_maintenance_details',
            maintenance_id: maintenanceId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.record) {
            const record = data.record;
            const timelineInfo = buildMaintenanceTimeline(record);
            const requestDetails = buildMaintenanceRequestDetails(record);
            const detailsHtml = `
                <div class="card">
                    <div class="card-body">
                        <h6><i class="fas fa-tools"></i> Maintenance Details</h6>
                        <hr>
                        <p><strong>Equipment:</strong> ${escapeHtml(record.equipment_type || 'N/A')}</p>
                        <p><strong>Maintenance Type:</strong> ${escapeHtml(record.maintenance_type || 'N/A')}</p>
                        <p><strong>Technician:</strong> ${escapeHtml(record.assigned_to_name || 'Unassigned')}</p>
                        <p><strong>Schedule:</strong> ${formatDateTimeLocal(record.start_date)} - ${formatDateTimeLocal(record.end_date)}</p>
                        ${record.support_level ? `<p><strong>Support Level:</strong> ${escapeHtml(record.support_level)}</p>` : ''}
                        ${record.processing_time ? `<p><strong>Processing Time:</strong> ${escapeHtml(record.processing_time)}</p>` : ''}
                        ${record.description ? `<p><strong>Notes:</strong><br><span class="text-muted small">${escapeHtml(record.description)}</span></p>` : ''}
                        ${timelineInfo || ''}
                        ${requestDetails || ''}
                    </div>
                </div>
            `;
            document.getElementById('maintenanceObserveDetails').innerHTML = detailsHtml;
            new bootstrap.Modal(document.getElementById('maintenanceObserveModal')).show();
        } else {
            showAlert(data.message || 'Maintenance record not found', 'danger');
        }
    })
    .catch(() => showAlert('Error loading maintenance details', 'danger'));
}

function openMaintenanceAssignModalFromObserve() {
    const modalEl = document.getElementById('maintenanceObserveModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    }
    if (currentMaintenanceId) {
        openMaintenanceAssignModal(currentMaintenanceId);
    }
}

function openMaintenanceAssignModal(maintenanceId) {
    currentMaintenanceId = maintenanceId;
    document.getElementById('maintenanceAssignId').value = maintenanceId;

    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_maintenance_details',
            maintenance_id: maintenanceId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.record) {
            const record = data.record;
            const supportLevelSelect = document.getElementById('maintenanceSupportLevel');
            const processingInput = document.getElementById('maintenanceProcessingTime');

            if (record.support_level) {
                supportLevelSelect.value = record.support_level;
                const selectedOption = supportLevelSelect.options[supportLevelSelect.selectedIndex];
                const processingTime = selectedOption ? selectedOption.getAttribute('data-time') : null;
                processingInput.value = record.processing_time || processingTime || '';
            } else {
                supportLevelSelect.value = '';
                processingInput.value = '';
            }

            document.getElementById('maintenanceObservationNotes').value = record.description || '';
            
            // Set start time if not already set
            const startTimeInput = document.getElementById('maintenanceStartTime');
            if (startTimeInput && !record.started_at) {
                const now = new Date();
                startTimeInput.value = now.toLocaleString();
            } else if (startTimeInput && record.started_at) {
                startTimeInput.value = new Date(record.started_at).toLocaleString();
            }
        }
        // Set start time when modal opens (if not already set)
        const startTimeInput = document.getElementById('maintenanceStartTime');
        if (startTimeInput && !startTimeInput.value) {
            const now = new Date();
            startTimeInput.value = now.toLocaleString();
        }
        new bootstrap.Modal(document.getElementById('maintenanceAssignModal')).show();
    })
    .catch(() => {
        // Set start time when modal opens
        const startTimeInput = document.getElementById('maintenanceStartTime');
        if (startTimeInput) {
            const now = new Date();
            startTimeInput.value = now.toLocaleString();
        }
        new bootstrap.Modal(document.getElementById('maintenanceAssignModal')).show();
    });
}

function openCompleteMaintenanceModal(maintenanceId) {
    openCompleteModal(maintenanceId, 'maintenance');
}

function formatDateTimeLocal(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) {
        return dateString;
    }
    return date.toLocaleString();
}

function formatDurationFromMs(ms) {
    if (!ms || ms <= 0) return '0m';
    const totalMinutes = Math.floor(ms / 60000);
    const days = Math.floor(totalMinutes / (60 * 24));
    const hours = Math.floor((totalMinutes % (60 * 24)) / 60);
    const minutes = totalMinutes % 60;
    const parts = [];
    if (days > 0) parts.push(`${days}d`);
    if (hours > 0) parts.push(`${hours}h`);
    if (minutes > 0) parts.push(`${minutes}m`);
    return parts.join(' ');
}

function buildServiceRequestTimeline(request) {
    if (!request.processing_deadline) {
        return '';
    }

    const deadline = new Date(request.processing_deadline);
    const now = new Date();

    if (request.status === 'completed') {
        if (request.completed_within_sla === null || request.completed_within_sla === undefined) {
            return `
                <div class="timeline-chip chip-success mt-2">
                    <i class="fas fa-hourglass"></i>
                    Completed
                </div>
                <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(request.processing_deadline)}</small>
            `;
        }
        const onTime = parseInt(request.completed_within_sla, 10) === 1;
        return `
            <div class="timeline-chip ${onTime ? 'chip-success' : 'chip-danger'} mt-2">
                <i class="fas ${onTime ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${onTime ? 'Completed on time' : 'Completed after deadline'}
            </div>
            <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(request.processing_deadline)}</small>
        `;
    }

    const diff = deadline.getTime() - now.getTime();
    const overdue = diff < 0;
    const label = overdue ? `Overdue by ${formatDurationFromMs(Math.abs(diff))}` : `Time left: ${formatDurationFromMs(diff)}`;

    return `
        <div class="timeline-chip ${overdue ? 'chip-danger' : 'chip-success'} mt-2">
            <i class="fas ${overdue ? 'fa-exclamation-circle' : 'fa-clock'}"></i>
            ${label}
        </div>
        <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(request.processing_deadline)}</small>
    `;
}

function buildMaintenanceTimeline(record) {
    if (!record.processing_deadline) {
        return '';
    }

    const deadline = new Date(record.processing_deadline);
    const now = new Date();

    if (record.status === 'completed') {
        if (record.completed_within_sla === null || record.completed_within_sla === undefined) {
            return `
                <div class="timeline-chip chip-success mt-2">
                    <i class="fas fa-hourglass"></i>
                    Completed
                </div>
                <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(record.processing_deadline)}</small>
            `;
        }
        const onTime = parseInt(record.completed_within_sla, 10) === 1;
        return `
            <div class="timeline-chip ${onTime ? 'chip-success' : 'chip-danger'} mt-2">
                <i class="fas ${onTime ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${onTime ? 'Completed on time' : 'Completed after deadline'}
            </div>
            <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(record.processing_deadline)}</small>
        `;
    }

    const diff = deadline.getTime() - now.getTime();
    const overdue = diff < 0;
    const label = overdue ? `Overdue by ${formatDurationFromMs(Math.abs(diff))}` : `Time left: ${formatDurationFromMs(diff)}`;

    return `
        <div class="timeline-chip ${overdue ? 'chip-danger' : 'chip-success'} mt-2">
            <i class="fas ${overdue ? 'fa-exclamation-circle' : 'fa-clock'}"></i>
            ${label}
        </div>
        <small class="text-muted d-block"><i class="fas fa-hourglass-end"></i> Deadline: ${formatDateTimeLocal(record.processing_deadline)}</small>
    `;
}

function buildMaintenanceRequestDetails(record) {
    if (!record.request_form_type && !record.request_form_data) {
        return '';
    }

    const items = [];
    if (record.request_form_type) {
        items.push(`<p><strong>Original Form:</strong> ${escapeHtml(record.request_form_type)}</p>`);
    }
    if (record.request_created_at) {
        items.push(`<p><strong>Request Submitted:</strong> ${formatDateTimeLocal(record.request_created_at)}</p>`);
    }

    if (record.request_form_data) {
        items.push('<div class="mt-3"><strong><i class="fas fa-list"></i> Request Details:</strong><div class="mt-2">');
        for (const [key, value] of Object.entries(record.request_form_data)) {
            if (typeof value === 'object') {
                const subItems = [];
                Object.entries(value).forEach(([subKey, subValue]) => {
                    if (typeof subValue === 'string' && subValue.trim() !== '') {
                        subItems.push(`<div class="text-muted small"><strong>${escapeHtml(formatLabel(subKey))}:</strong> ${escapeHtml(subValue)}</div>`);
                    }
                });
                if (subItems.length > 0) {
                    items.push(`<div class="mb-2"><strong>${escapeHtml(formatLabel(key))}</strong><div class="ms-2">${subItems.join('')}</div></div>`);
                }
            } else if (typeof value === 'string' && value.trim() !== '') {
                items.push(`<div class="mb-1 text-muted small"><strong>${escapeHtml(formatLabel(key))}:</strong> ${escapeHtml(value)}</div>`);
            }
        }
        items.push('</div></div>');
    }

    if (items.length === 0) {
        return '';
    }

    return `
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="fas fa-file-alt"></i> Original Request</h6>
                <hr>
                ${items.join('')}
            </div>
        </div>
    `;
}

function formatLabel(key) {
    return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

function openServiceRequestModal(requestId) {
    document.getElementById('serviceRequestId').value = requestId;
    // Try to load existing values if any
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_requests',
            technician_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const request = data.data.find(r => r.id == requestId);
            if (request) {
                const supportLevelSelect = document.getElementById('serviceRequestSupportLevel');
                const processingTimeInput = document.getElementById('serviceRequestProcessingTime');
                
                if (request.support_level) {
                    supportLevelSelect.value = request.support_level;
                    // Auto-populate processing time if support level is set
                    const selectedOption = supportLevelSelect.options[supportLevelSelect.selectedIndex];
                    const processingTime = selectedOption.getAttribute('data-time');
                    if (processingTime && !request.processing_time) {
                        processingTimeInput.value = processingTime;
                    } else if (request.processing_time) {
                        processingTimeInput.value = request.processing_time;
                    }
                } else {
                    supportLevelSelect.value = '';
                    processingTimeInput.value = '';
                }
                document.getElementById('serviceRequestAccomplishment').value = request.accomplishment || '';
            }
        }
        new bootstrap.Modal(document.getElementById('updateServiceRequestModal')).show();
    })
    .catch(() => {
        new bootstrap.Modal(document.getElementById('updateServiceRequestModal')).show();
    });
}

function openCompleteServiceRequestModal(requestId) {
    document.getElementById('completeServiceRequestId').value = requestId;
    // Load existing values if any
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_requests',
            technician_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const request = data.data.find(r => r.id == requestId);
            if (request) {
                const supportLevelSelect = document.getElementById('completeServiceRequestSupportLevel');
                const processingTimeInput = document.getElementById('completeServiceRequestProcessingTime');
                
                if (request.support_level) {
                    supportLevelSelect.value = request.support_level;
                    // Auto-populate processing time if support level is set
                    const selectedOption = supportLevelSelect.options[supportLevelSelect.selectedIndex];
                    const processingTime = selectedOption.getAttribute('data-time');
                    if (processingTime && !request.processing_time) {
                        processingTimeInput.value = processingTime;
                    } else if (request.processing_time) {
                        processingTimeInput.value = request.processing_time;
                    }
                } else {
                    supportLevelSelect.value = '';
                    processingTimeInput.value = '';
                }
                document.getElementById('completeServiceRequestAccomplishment').value = request.accomplishment || '';
                document.getElementById('completeServiceRequestRemarks').value = request.remarks || '';
                
                // Load observation timer data if available
                if (request.observe_start_time) {
                    document.getElementById('completeObserveStartTime').value = request.observe_start_time;
                }
                if (request.observe_stop_time) {
                    document.getElementById('completeObserveStopTime').value = request.observe_stop_time;
                }
                if (request.observe_duration) {
                    document.getElementById('completeObserveDuration').value = request.observe_duration;
                }
            }
        }
        
        // Also try to get timer data from assign modal if available
        const assignStartTime = document.getElementById('serviceRequestObserveStartTime') ? document.getElementById('serviceRequestObserveStartTime').value : '';
        const assignStopTime = document.getElementById('serviceRequestObserveStopTime') ? document.getElementById('serviceRequestObserveStopTime').value : '';
        const assignDuration = document.getElementById('serviceRequestObserveDuration') ? document.getElementById('serviceRequestObserveDuration').value : '';
        
        if (assignStartTime && !document.getElementById('completeObserveStartTime').value) {
            document.getElementById('completeObserveStartTime').value = assignStartTime;
        }
        if (assignStopTime && !document.getElementById('completeObserveStopTime').value) {
            document.getElementById('completeObserveStopTime').value = assignStopTime;
        }
        if (assignDuration && !document.getElementById('completeObserveDuration').value) {
            document.getElementById('completeObserveDuration').value = assignDuration;
        }
        
        new bootstrap.Modal(document.getElementById('completeServiceRequestModal')).show();
    })
    .catch(() => {
        new bootstrap.Modal(document.getElementById('completeServiceRequestModal')).show();
    });
}

function updateServiceRequestCount(status, count) {
    // Don't add to existing count, just set it (will be recalculated with all items)
    // The count will be updated by the main refresh function
}

// ---------------- FEEDBACK PREVIEW ----------------
function getSatisfactionLevel(rating) {
    if (rating === 5) return 'Very Satisfied';
    if (rating === 4) return 'Satisfied';
    if (rating === 3) return 'Neither Satisfied nor Dissatisfied';
    if (rating === 2) return 'Dissatisfied';
    if (rating === 1) return 'Very Dissatisfied';
    return '';
}

function viewFeedbackPreview(requestId) {
    const modal = new bootstrap.Modal(document.getElementById('feedbackPreviewModal'));
    const modalBody = document.getElementById('feedbackPreviewModalBody');
    
    // Show loading
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading feedback details...</p></div>';
    modal.show();
    
    // Fetch survey data
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_request_surveys',
            request_id: parseInt(requestId)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.surveys && data.surveys.length > 0) {
            let html = '<div class="feedback-preview-content">';
            html += '<h6 class="mb-3"><i class="fas fa-star text-warning"></i> Feedback Ratings & Satisfaction Levels</h6>';
            
            data.surveys.forEach((survey, index) => {
                const avgRating = (parseInt(survey.eval_response) + parseInt(survey.eval_quality) + parseInt(survey.eval_courtesy) + parseInt(survey.eval_overall)) / 4;
                const submittedDate = new Date(survey.submitted_at).toLocaleString();
                
                html += `<div class="card mb-3 border-start border-primary border-4">`;
                html += `<div class="card-header bg-light">`;
                html += `<div class="d-flex justify-content-between align-items-center">`;
                html += `<div>`;
                html += `<strong><i class="fas fa-user"></i> ${escapeHtml(survey.user_name || 'Anonymous')}</strong>`;
                html += `${survey.office ? `<br><small class="text-muted">${escapeHtml(survey.office)}</small>` : ''}`;
                html += `</div>`;
                html += `<span class="badge bg-primary">Average: ${avgRating.toFixed(1)}/5</span>`;
                html += `</div>`;
                html += `<small class="text-muted"><i class="fas fa-calendar"></i> ${submittedDate}</small>`;
                html += `</div>`;
                html += `<div class="card-body">`;
                
                // Response time
                html += `<div class="mb-3 p-2 bg-light rounded">`;
                html += `<div class="d-flex justify-content-between align-items-center mb-1">`;
                html += `<span><strong>Response time to your initial call for service:</strong></span>`;
                html += `<span class="badge bg-${survey.eval_response >= 4 ? 'success' : survey.eval_response >= 3 ? 'warning' : 'danger'}">${survey.eval_response}/5</span>`;
                html += `</div>`;
                html += `<div class="text-muted small"><i class="fas fa-check-circle"></i> ${getSatisfactionLevel(parseInt(survey.eval_response))}</div>`;
                html += `</div>`;
                
                // Quality
                html += `<div class="mb-3 p-2 bg-light rounded">`;
                html += `<div class="d-flex justify-content-between align-items-center mb-1">`;
                html += `<span><strong>Quality of service provided to resolve the problem:</strong></span>`;
                html += `<span class="badge bg-${survey.eval_quality >= 4 ? 'success' : survey.eval_quality >= 3 ? 'warning' : 'danger'}">${survey.eval_quality}/5</span>`;
                html += `</div>`;
                html += `<div class="text-muted small"><i class="fas fa-check-circle"></i> ${getSatisfactionLevel(parseInt(survey.eval_quality))}</div>`;
                html += `</div>`;
                
                // Courtesy
                html += `<div class="mb-3 p-2 bg-light rounded">`;
                html += `<div class="d-flex justify-content-between align-items-center mb-1">`;
                html += `<span><strong>Courtesy and professionalism of the attending ICT staff:</strong></span>`;
                html += `<span class="badge bg-${survey.eval_courtesy >= 4 ? 'success' : survey.eval_courtesy >= 3 ? 'warning' : 'danger'}">${survey.eval_courtesy}/5</span>`;
                html += `</div>`;
                html += `<div class="text-muted small"><i class="fas fa-check-circle"></i> ${getSatisfactionLevel(parseInt(survey.eval_courtesy))}</div>`;
                html += `</div>`;
                
                // Overall
                html += `<div class="mb-3 p-2 bg-light rounded">`;
                html += `<div class="d-flex justify-content-between align-items-center mb-1">`;
                html += `<span><strong>Overall satisfaction with the assistance/service provided:</strong></span>`;
                html += `<span class="badge bg-${survey.eval_overall >= 4 ? 'success' : survey.eval_overall >= 3 ? 'warning' : 'danger'}">${survey.eval_overall}/5</span>`;
                html += `</div>`;
                html += `<div class="text-muted small"><i class="fas fa-check-circle"></i> ${getSatisfactionLevel(parseInt(survey.eval_overall))}</div>`;
                html += `</div>`;
                
                // Comments
                if (survey.comments) {
                    html += `<div class="mt-3 p-2 bg-info bg-opacity-10 rounded">`;
                    html += `<strong><i class="fas fa-comment"></i> Comments:</strong>`;
                    html += `<p class="mb-0 mt-2">${escapeHtml(survey.comments)}</p>`;
                    html += `</div>`;
                }
                
                html += `</div>`;
                html += `</div>`;
            });
            
            html += '</div>';
            modalBody.innerHTML = html;
        } else {
            modalBody.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No feedback data available.</div>';
        }
    })
    .catch(error => {
        modalBody.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error loading feedback: ${escapeHtml(error.message)}</div>`;
    });
}

function deleteServiceRequest(requestId) {
    if (!confirm('Remove this completed service request from the board? This cannot be undone.')) {
        return;
    }
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'delete_service_request',
            request_id: requestId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Service request removed.', 'success');
            loadAllItems();
            loadStatistics();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error removing service request', 'danger'));
}

// ---------------- Helpers ----------------
function openCompleteModal(id, type) {
    document.getElementById('completeItemId').value = id;
    document.getElementById('completeItemType').value = type;
    document.getElementById('completeRemarks').value = '';
    
    // Set end time when modal opens
    const endTimeInput = document.getElementById('completeEndTime');
    if (endTimeInput) {
        const now = new Date();
        endTimeInput.value = now.toLocaleString();
    }
    
    new bootstrap.Modal(document.getElementById('completeModal')).show();
}
// Counts are now handled in individual render functions
function refreshTasks(){ loadAllItems(); showAlert("Refreshed","info"); }
function showAlert(msg,type){
    const id='alert-'+Date.now();
    document.getElementById('alert-container').innerHTML=`
        <div class="alert alert-${type} alert-dismissible fade show" id="${id}">
        ${msg}<button class="btn-close" data-bs-dismiss="alert"></button></div>`;
    setTimeout(()=>{const el=document.getElementById(id);if(el)el.remove();},3000);
}
function escapeHtml(txt){const div=document.createElement('div');div.textContent=txt;return div.innerHTML;}
</script>

<style>
/* Kanban Board Styling */
.kanban-board-container {
    display: flex;
    flex-direction: row;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 10px;
    min-height: 75vh;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.kanban-board-container::-webkit-scrollbar {
    height: 8px;
}

.kanban-board-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.kanban-board-container::-webkit-scrollbar-thumb {
    background: linear-gradient(90deg, #dc3545 0%, #343a40 100%);
    border-radius: 10px;
}

.kanban-board-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(90deg, #c82333 0%, #23272b 100%);
}

.kanban-column-wrapper {
    flex: 1 1 0;
    min-width: 320px;
    max-width: 100%;
}

.kanban-column { 
    height: 75vh; 
    min-height: 600px;
    overflow-y: auto; 
    border-radius: 14px;
    border: 1px solid #e2e6ef;
    background: linear-gradient(180deg, #fdfdff 0%, #f6f7fb 100%);
    transition: all 0.35s ease;
    display: flex;
    flex-direction: column;
}

.kanban-column:hover {
    box-shadow: 0 12px 32px rgba(149, 157, 165, 0.2);
}

.kanban-column .card-body {
    padding: 18px;
    min-height: 200px;
    flex: 1;
    overflow-y: auto;
    position: relative;
}

.empty-state {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    text-align: center;
    color: #9ca3af;
    min-height: 300px;
}

.empty-state i {
    opacity: 0.4;
    margin-bottom: 16px;
}

.empty-state p {
    font-size: 1rem;
    font-weight: 500;
    margin: 0;
    opacity: 0.6;
}

/* Observation Timer Styling */
.timer-display {
    padding: 10px;
}

.timer-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    font-family: 'Courier New', monospace;
    margin-top: 5px;
}

.timer-display label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#observeStartBtn, #observeStopBtn, #observeResetBtn {
    min-width: 100px;
}

.kanban-header {
    border-radius: 12px 12px 0 0;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e0e6ef;
    background: #f5f7fb;
    color: #475467;
    position: sticky;
    top: 0;
    z-index: 2;
}

.kanban-header i {
    color: #4c6ef5;
}

.kanban-header-pending {
    background: linear-gradient(90deg, #f6f8fb 0%, #edf1f9 100%);
}
.kanban-header-progress {
    background: linear-gradient(90deg, #f4f7fc 0%, #e7edf9 100%);
}
.kanban-header-complete {
    background: linear-gradient(90deg, #f3f8f5 0%, #e4f0e8 100%);
}

/* Task Card Styling */
.task-card { 
    background: #ffffff;
    border: 1px solid #e4e8f1;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 16px;
    box-shadow: 0 6px 18px rgba(82, 96, 112, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;
    background: linear-gradient(180deg, #a5bdfd 0%, #748ffc 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 32px rgba(82, 96, 112, 0.18);
    border-color: #c7d3ea;
}

.task-card:hover::before {
    opacity: 1;
}

.task-card.completed { 
    opacity: 0.9;
    background: linear-gradient(135deg, #f9fbf9 0%, #eef5ef 100%);
    border-color: #a9d6b8;
}

.task-card.completed::before {
    background: #7bc28f;
    opacity: 1;
}

/* Service Request Card */
.service-request-card { 
    border-left: 5px solid #9cbffd;
    background: linear-gradient(135deg, #ffffff 0%, #f3f7ff 100%);
}

.service-request-card:hover {
    border-left-width: 7px;
    background: linear-gradient(135deg, #ffffff 0%, #e7f0ff 100%);
}

.service-request-card .service-request-info { 
    font-size: 0.9rem;
    line-height: 1.6;
    background: rgba(76, 110, 245, 0.05);
    padding: 12px;
    border-radius: 10px;
}

.timeline-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 10px;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
}

.timeline-chip i {
    font-size: 0.9rem;
}

.chip-success {
    background: #e9f7ef;
    color: #0f5132;
}

.chip-danger {
    background: #fdecea;
    color: #842029;
}

.maintenance-card { 
    border-left: 5px solid #ffc107;
    background: linear-gradient(135deg, #ffffff 0%, #fffbf0 100%);
}

.maintenance-card:hover {
    border-left-width: 7px;
    background: linear-gradient(135deg, #ffffff 0%, #fff8e6 100%);
}

.maintenance-card .maintenance-info {
    background: rgba(255, 193, 7, 0.1);
    border-radius: 10px;
    padding: 12px;
    font-size: 0.9rem;
    line-height: 1.6;
}

.maintenance-card .maintenance-info strong {
    color: #b8860b;
    font-weight: 600;
}

/* Task Header */
.task-header { 
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    gap: 10px;
}

.task-header .task-title { 
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #212529;
    flex: 1;
    line-height: 1.4;
}

/* Priority Badges */
.priority-badge { 
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.priority-low {
    background: linear-gradient(135deg, #e7f5ef 0%, #d4f1e3 100%);
    color: #2e7d5b;
}

.priority-medium {
    background: linear-gradient(135deg, #fff6e3 0%, #ffeccc 100%);
    color: #a1781c;
}

.priority-high {
    background: linear-gradient(135deg, #fdebec 0%, #f8dfe1 100%);
    color: #b04b50;
}

.priority-urgent {
    background: linear-gradient(135deg, #fbe3e5 0%, #f7d0d4 100%);
    color: #a12d35;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Task Description */
.task-description {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Task Meta */
.task-meta { 
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
    font-size: 0.85rem;
}

.task-meta i {
    color: #6c757d;
    width: 16px;
    margin-right: 5px;
}

/* Task Actions */
.task-actions {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
}

.task-actions .btn {
    font-size: 0.85rem;
    padding: 8px 16px;
    margin-top: 5px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.task-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.task-actions .btn-sm { 
    font-size: 0.8rem;
    padding: 6px 12px;
}

.task-actions .btn-primary,
.task-actions .btn-info,
.task-actions .btn-warning,
.task-actions .btn-success {
    border: none;
    color: #fdfdfd;
    box-shadow: 0 4px 12px rgba(76, 110, 245, 0.15);
}

.task-actions .btn-primary,
.task-actions .btn-info {
    background: linear-gradient(120deg, #6c8df7 0%, #4f6edb 100%);
}

.task-actions .btn-warning {
    background: linear-gradient(120deg, #f6c460 0%, #f3a847 100%);
    color: #4f2d00;
}

.task-actions .btn-success {
    background: linear-gradient(120deg, #5fb687 0%, #4a9f72 100%);
}

.task-actions .btn-primary:hover,
.task-actions .btn-info:hover,
.task-actions .btn-warning:hover,
.task-actions .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(70, 90, 180, 0.25);
    opacity: 0.95;
}

/* Service Request Info */
.service-request-info strong {
    color: #4f6edb;
    font-weight: 600;
}

.feedback-card {
    background: linear-gradient(135deg, rgba(79,110,219,0.08) 0%, rgba(79,110,219,0.02) 100%);
    border-left: 4px solid #94b3ff;
    border-radius: 12px;
    box-shadow: inset 0 0 0 1px rgba(79,110,219,0.08);
}

.clickable-feedback {
    transition: all 0.3s ease;
}

.clickable-feedback:hover {
    background: linear-gradient(135deg, rgba(13,110,253,0.15) 0%, rgba(13,110,253,0.08) 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13,110,253,0.2) !important;
    border-left-width: 6px;
}

/* Scrollbar Styling */
.kanban-column::-webkit-scrollbar {
    width: 8px;
}

.kanban-column::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.kanban-column::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #dc3545 0%, #343a40 100%);
    border-radius: 10px;
}

.kanban-column::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #c82333 0%, #23272b 100%);
}

/* Alert Container */
#alert-container {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 1050;
    max-width: 400px;
}

/* Statistics Card Styling */
.stat-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 25px 15px;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.stat-card-blue i {
    color: #007bff;
}

.stat-card-yellow i {
    color: #ffc107;
}

.stat-card-green i {
    color: #28a745;
}

.stat-card h3 {
    font-weight: 700;
    color: #212529;
    margin: 0;
    font-size: 1.75rem;
}

.stat-card small {
    font-size: 0.85rem;
    font-weight: 500;
    color: #6c757d;
}

.clickable-stat {
    cursor: pointer;
    position: relative;
}

.clickable-stat:hover {
    border-color: #007bff;
}

.stat-active {
    border: 2px solid #007bff !important;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
}

.stat-active.stat-card-blue {
    border-color: #007bff !important;
}

.stat-active.stat-card-yellow {
    border-color: #ffc107 !important;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3) !important;
}

.stat-active.stat-card-green {
    border-color: #28a745 !important;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3) !important;
}

/* Responsive Design */
@media (max-width: 992px) {
    .kanban-board-container {
        flex-wrap: wrap;
    }
    
    .kanban-column-wrapper {
        flex: 1 1 100%;
        min-width: 100%;
        margin-bottom: 20px;
    }
    
    .kanban-column {
        height: 65vh;
        min-height: 500px;
    }
}

@media (max-width: 768px) {
    .kanban-board-container {
        flex-direction: column;
        gap: 15px;
    }
    
    .kanban-column-wrapper {
        width: 100%;
        min-width: 100%;
    }
    
    .kanban-column {
        height: 60vh;
        min-height: 400px;
    }
    
    .task-card {
        padding: 15px;
    }
    
    .task-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stat-card {
        padding: 20px 10px;
    }
    
    .stat-card i {
        font-size: 2rem !important;
    }
    
    .stat-card h3 {
        font-size: 1.5rem;
    }
}

@media (min-width: 1200px) {
    .kanban-column {
        height: 80vh;
    }
    
    .kanban-board-container {
        gap: 24px;
    }
}
</style>

<?php require_once 'footer.php'; ?>