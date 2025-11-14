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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-tasks text-danger"></i> ICT Service Request & Task Board
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Manage service requests from departments, tasks, and maintenance records
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshTasks()" style="border-radius: 8px;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefresh" checked style="cursor: pointer;">
                        <label class="form-check-label" for="autoRefresh" style="cursor: pointer; font-weight: 500;">
                            Auto Refresh
                        </label>
                    </div>
                </div>
            </div>

            <div id="alert-container"></div>

            <div class="row">
                <!-- Pending -->
                <div class="col-md-4">
                    <div class="card kanban-column">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Pending
                                <span class="badge bg-dark ms-2" id="pending-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="pending" id="pending-tasks"></div>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="col-md-4">
                    <div class="card kanban-column">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cogs"></i> In Progress
                                <span class="badge bg-light text-dark ms-2" id="in-progress-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="in_progress" id="in-progress-tasks"></div>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="col-md-4">
                    <div class="card kanban-column">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle"></i> Completed
                                <span class="badge bg-light text-dark ms-2" id="completed-count">0</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="task-list" data-status="completed" id="completed-tasks"></div>
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
          <button type="submit" class="btn btn-success w-100">
            <i class="fas fa-check-circle"></i> Mark as Complete & Notify Department
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- View Ratings Modal -->
<div class="modal fade" id="viewRatingsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-star"></i> Service Ratings & Feedback</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="ratingsContent">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading ratings...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let autoRefreshInterval;
let currentUserId = <?php echo $user_id; ?>;

document.addEventListener('DOMContentLoaded', function() {
    loadAllItems();
    startAutoRefresh();

    document.getElementById('autoRefresh').addEventListener('change', function() {
        this.checked ? startAutoRefresh() : stopAutoRefresh();
    });

    // Handle Complete Modal submit
    document.getElementById('completeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const id = document.getElementById('completeItemId').value;
        const type = document.getElementById('completeItemType').value;
        const remarks = document.getElementById('completeRemarks').value.trim();
        if (!remarks) { alert("Please enter remarks."); return; }
        if (type === 'task') {
            sendTaskStatusUpdate(id, 'completed', remarks);
        } else {
            sendMaintenanceStatusUpdate(id, 'completed', remarks);
        }
        bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
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

    // Handle Update Service Request Form (Assign Support Level)
    document.getElementById('updateServiceRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const requestId = document.getElementById('serviceRequestId').value;
        const supportLevel = document.getElementById('serviceRequestSupportLevel').value;
        const processingTime = document.getElementById('serviceRequestProcessingTime').value;
        const accomplishment = document.getElementById('serviceRequestAccomplishment').value.trim();
        
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
                new_status: 'In Progress',
                support_level: supportLevel,
                processing_time: processingTime,
                accomplishment: accomplishment || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Support level and processing time assigned! You can now proceed to complete the request.', 'success');
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
                new_status: 'completed',
                support_level: supportLevel,
                processing_time: processingTime,
                accomplishment: accomplishment,
                remarks: remarks
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Service request completed!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('completeServiceRequestModal')).hide();
                loadAllItems();
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error completing request', 'danger'));
    });
});

// Refreshing
function startAutoRefresh() { autoRefreshInterval = setInterval(loadAllItems, 10000); }
function stopAutoRefresh() { clearInterval(autoRefreshInterval); }
function loadAllItems() {
    // Don't reset counts here - let each render function calculate the correct count
    // This prevents flickering and ensures accurate counts
    
    // Load all items
    ['pending','in_progress','completed'].forEach(status => {
        loadTasksByStatus(status);
        loadMaintenanceByStatus(status);
        loadServiceRequestsByStatus(status);
    });
}

// ---------------- TASKS ----------------
function loadTasksByStatus(status) {
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'get_tasks', status: status, user_id: currentUserId})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            renderTasks(status, data.data);
        }
    });
}

function renderTasks(status, tasks) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    if (!container) return;
    
    // Remove only task cards, not service request cards or maintenance cards
    container.querySelectorAll('[data-task-id]').forEach(el => el.remove());
    
    // Add new task cards
    tasks.forEach(task => {
        const existing = container.querySelector(`[data-task-id="${task.id}"]`);
        if (!existing) {
            const taskHtml = createTaskElement(task).replace('<div class="task-card', '<div class="task-card" data-task-id="' + task.id + '"');
            container.insertAdjacentHTML('beforeend', taskHtml);
        }
    });
    
    // Update count - count all items in the container
    const allCards = container.querySelectorAll('.task-card');
    const countElement = document.getElementById(`${status.replace('_','-')}-count`);
    if (countElement) {
        countElement.textContent = allCards.length;
    }
}

function createTaskElement(task) {
    const dueDate = new Date(task.due_date).toLocaleDateString();
    const createdDate = new Date(task.created_at).toLocaleDateString();

    return `
    <div class="task-card ${task.status === 'completed' ? 'completed' : ''}">
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
        ${task.status !== 'completed' ? `
            <div class="task-actions">
                ${task.status === 'pending'
                    ? `<button class="btn btn-sm btn-success" onclick="updateTaskStatus(${task.id}, 'in_progress')">Start</button>`
                    : `<button class="btn btn-sm btn-success" onclick="openCompleteModal(${task.id}, 'task')">Complete</button>`}
            </div>` : ''}
    </div>`;
}

// ---------------- MAINTENANCE ----------------
function loadMaintenanceByStatus(status) {
    fetch('api/task_webhook.php', {
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
    });
}

function renderMaintenance(status, records) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    if (!container) return;
    
    // Remove only maintenance cards
    container.querySelectorAll('[data-maintenance-id]').forEach(el => el.remove());
    
    // Add new maintenance cards
    records.forEach(r => {
        const existing = container.querySelector(`[data-maintenance-id="${r.id}"]`);
        if (!existing) {
            container.insertAdjacentHTML('beforeend', createMaintenanceElement(r));
        }
    });
    
    // Update count - count all items in the container
    const allCards = container.querySelectorAll('.task-card');
    const countElement = document.getElementById(`${status.replace('_','-')}-count`);
    if (countElement) {
        countElement.textContent = allCards.length;
    }
}

function createMaintenanceElement(record) {
    const startDate = new Date(record.start_date).toLocaleDateString();
    const endDate = new Date(record.end_date).toLocaleDateString();

    return `
    <div class="task-card ${record.status === 'completed' ? 'completed' : ''}" data-maintenance-id="${record.id}">
        <div class="task-header">
            <h6 class="task-title">🔧 ${escapeHtml(record.maintenance_type)}</h6>
            <span class="priority-badge priority-medium">Maintenance</span>
        </div>
        <p class="task-description">${escapeHtml(record.description || '')}</p>
        <div class="task-meta">
            <small class="text-muted">
                <i class="fas fa-user-cog"></i> ${escapeHtml(record.assigned_to_name)}<br>
                <i class="fas fa-calendar"></i> ${startDate} → ${endDate}<br>
                <i class="fas fa-coins"></i> ₱${record.cost || 0}
            </small>
        </div>
        ${record.status !== 'completed' ? `
            <div class="task-actions">
                ${record.status === 'pending'
                    ? `<button class="btn btn-sm btn-success" onclick="updateMaintenanceStatus(${record.id}, 'in_progress')"><i class="fas fa-play"></i> Start</button>`
                    : `<button class="btn btn-sm btn-success" onclick="openCompleteModal(${record.id}, 'maintenance')"><i class="fas fa-check"></i> Complete</button>`}
            </div>
        ` : ''}
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
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error updating task', 'danger'));
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

function sendMaintenanceStatusUpdate(id, status, remarks = '') {
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update_maintenance_status',
            maintenance_id: id,
            new_status: status,
            remarks: remarks
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Maintenance updated!', 'success');
            loadAllItems();
        } else {
            showAlert('Failed: ' + data.message, 'danger');
        }
    })
    .catch(() => showAlert('Error updating maintenance', 'danger'));
}

// ---------------- SERVICE REQUESTS ----------------
function loadServiceRequestsByStatus(status) {
    // For pending status, don't filter by technician_id so all technicians can see and accept requests
    // For in_progress and completed, filter by technician_id to show only assigned requests
    const requestBody = {
        action: 'get_service_requests', 
        status: status === 'pending' ? 'Pending' : (status === 'in_progress' ? 'In Progress' : 'Completed')
    };
    
    // Only include technician_id for in_progress and completed statuses
    if (status === 'in_progress' || status === 'completed') {
        requestBody.technician_id = currentUserId;
    }
    
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(requestBody)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            console.log(`✅ Loaded ${data.data.length} service requests for status: ${status}`, data.data);
            if (data.data.length > 0) {
                console.log('Sample request:', data.data[0]);
            }
            renderServiceRequests(status, data.data);
        } else {
            console.error('❌ Failed to load service requests:', data.message || 'Unknown error');
            if (data.error) {
                console.error('Error details:', data.error);
            }
        }
    })
    .catch(err => {
        console.error('❌ Error loading service requests:', err);
        showAlert('Error loading service requests: ' + err.message, 'danger');
    });
}

function renderServiceRequests(status, requests) {
    const container = document.getElementById(`${status.replace('_','-')}-tasks`);
    if (!container) {
        console.error(`Container not found for status: ${status}`);
        return;
    }
    
    console.log(`Rendering ${requests.length} service requests for status: ${status}`, requests);
    
    // Get incoming request IDs
    const incomingIds = new Set(requests.map(r => String(r.id)));
    
    // Remove service request cards that are no longer in the incoming list
    container.querySelectorAll('[data-service-request-id]').forEach(el => {
        const id = el.getAttribute('data-service-request-id');
        if (id && !incomingIds.has(id)) {
            el.remove();
        }
    });
    
    // Add new service request cards that don't exist yet
    let addedCount = 0;
    requests.forEach(req => {
        const existing = container.querySelector(`[data-service-request-id="${req.id}"]`);
        if (!existing) {
            try {
                const html = createServiceRequestElement(req);
                container.insertAdjacentHTML('beforeend', html);
                addedCount++;
            } catch (error) {
                console.error(`Error creating element for request ${req.id}:`, error);
            }
        }
    });
    
    console.log(`Added ${addedCount} new service request cards`);
    
    // Update count - count all items in the container (tasks + maintenance + service requests)
    const allCards = container.querySelectorAll('.task-card');
    const countElement = document.getElementById(`${status.replace('_','-')}-count`);
    if (countElement) {
        countElement.textContent = allCards.length;
    }
}

function createServiceRequestElement(request) {
    const createdDate = new Date(request.created_at).toLocaleDateString();
    const createdTime = new Date(request.created_at).toLocaleTimeString();
    const statusClass = request.status === 'completed' ? 'completed' : '';
    
    // Convert technician_id to number for comparison
    const requestTechId = parseInt(request.technician_id) || 0;
    const currentTechId = parseInt(currentUserId) || 0;
    
    // Show accept button for pending requests (even if already assigned to someone else)
    // This allows technicians to receive/reassign requests
    // Note: 'Assigned' status is mapped to 'pending' in the API, so it shows in pending column
    const isPending = request.status === 'pending' && 
                      (!requestTechId || requestTechId !== currentTechId);
    
    // Show action buttons if assigned to current technician and in progress
    const isAssigned = requestTechId === currentTechId && 
                      request.status === 'in_progress';
    const surveyCount = parseInt(request.survey_count || 0, 10) || 0;
    const surveyAverage = request.survey_average ? parseFloat(request.survey_average).toFixed(1) : null;
    const surveyLatestAt = request.survey_latest_at ? new Date(request.survey_latest_at).toLocaleString() : null;
    const surveyLatestComment = request.survey_latest_comment ? escapeHtml(request.survey_latest_comment) : '';
    
    return `
    <div class="task-card service-request-card ${statusClass}" data-service-request-id="${request.id}">
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
        </div>
        
        ${request.status !== 'completed' ? `
            <div class="task-actions mt-2">
                ${isPending
                    ? `<button class="btn btn-sm btn-primary w-100" onclick="acceptServiceRequest(${request.id})">
                           <i class="fas fa-hand-paper"></i> ${requestTechId && requestTechId !== currentTechId ? 'Reassign to Me' : 'Accept Request'}
                       </button>`
                    : isAssigned
                    ? `<div class="d-grid gap-2">
                           <button class="btn btn-sm btn-info" onclick="openObserveModal(${request.id})">
                               <i class="fas fa-eye"></i> Observe Equipment
                           </button>
                           <button class="btn btn-sm btn-warning" onclick="openServiceRequestModal(${request.id})">
                               <i class="fas fa-edit"></i> Assign Support Level
                           </button>
                           <button class="btn btn-sm btn-success" onclick="openCompleteServiceRequestModal(${request.id})">
                               <i class="fas fa-check-circle"></i> Mark Complete
                           </button>
                       </div>`
                    : requestTechId && requestTechId !== currentTechId
                    ? `<div class="alert alert-warning mb-0 py-2">
                           <i class="fas fa-info-circle"></i> Assigned to another technician
                       </div>`
                    : ''}
            </div>
        ` : `
            <div class="mt-3">
                <span class="badge bg-success mb-2"><i class="fas fa-check"></i> Completed</span>
                ${request.accomplishment ? `
                    <div class="mt-2">
                        <small class="text-muted text-uppercase fw-bold">Work Done</small>
                        <p class="small text-muted mb-0">${escapeHtml(request.accomplishment)}</p>
                    </div>
                ` : ''}
                ${surveyCount > 0 ? `
                    <div class="feedback-card mt-3 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><i class="fas fa-comment-dots text-primary"></i> Department Feedback</strong>
                            ${surveyAverage ? `<span class="badge bg-primary">${surveyAverage}/5 Avg</span>` : ''}
                        </div>
                        ${surveyLatestComment ? `<p class="text-muted small mb-1">${surveyLatestComment}</p>` : '<p class="text-muted small mb-1">No additional comments provided.</p>'}
                        ${surveyLatestAt ? `<small class="text-muted">Submitted: ${surveyLatestAt}</small>` : ''}
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-users"></i> ${surveyCount} ${surveyCount === 1 ? 'response' : 'responses'}
                            </span>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewRatings(${request.id})">
                                <i class="fas fa-star"></i> View All Ratings
                            </button>
                        </div>
                    </div>
                ` : `
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <i class="fas fa-info-circle"></i> Awaiting department survey feedback.
                    </div>
                `}
            </div>
        `}
    </div>`;
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
            } else {
                showAlert('Failed: ' + data.message, 'danger');
            }
        })
        .catch(() => showAlert('Error accepting request', 'danger'));
    }
}

let currentObserveRequestId = null;

function openObserveModal(requestId) {
    currentObserveRequestId = requestId;
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
    bootstrap.Modal.getInstance(document.getElementById('observeModal')).hide();
    if (currentObserveRequestId) {
        openServiceRequestModal(currentObserveRequestId);
    }
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
            }
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

// ---------------- RATINGS VIEW ----------------
function viewRatings(requestId) {
    const modal = new bootstrap.Modal(document.getElementById('viewRatingsModal'));
    const content = document.getElementById('ratingsContent');
    
    // Show loading state
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading ratings...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch ratings data
    fetch('api/task_webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_service_ratings',
            request_id: requestId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data) {
            const { ratings, summary } = data.data;
            renderRatings(content, ratings, summary);
        } else {
            content.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message || 'Failed to load ratings'}
                </div>
            `;
        }
    })
    .catch(err => {
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> Error loading ratings: ${err.message}
            </div>
        `;
    });
}

function renderRatings(container, ratings, summary) {
    if (!ratings || ratings.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No ratings available for this service request yet.
            </div>
        `;
        return;
    }
    
    // Helper function to render stars
    function renderStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star text-warning"></i>';
            } else {
                stars += '<i class="far fa-star text-muted"></i>';
            }
        }
        return stars;
    }
    
    // Helper function to get rating label
    function getRatingLabel(rating) {
        if (rating >= 4.5) return 'Excellent';
        if (rating >= 3.5) return 'Good';
        if (rating >= 2.5) return 'Average';
        if (rating >= 1.5) return 'Below Average';
        return 'Poor';
    }
    
    // Helper function to get satisfaction level based on rating (matching the form)
    function getSatisfactionLevel(rating) {
        if (rating === 5) return 'Very Satisfied';
        if (rating === 4) return 'Satisfied';
        if (rating === 3) return 'Neither Satisfied nor Dissatisfied';
        if (rating === 2) return 'Dissatisfied';
        if (rating === 1) return 'Very Dissatisfied';
        return '';
    }
    
    // Helper function to get rating color
    function getRatingColor(rating) {
        if (rating >= 4.5) return 'success';
        if (rating >= 3.5) return 'info';
        if (rating >= 2.5) return 'warning';
        if (rating >= 1.5) return 'danger';
        return 'secondary';
    }
    
    let html = `
        <!-- Summary Section -->
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Overall Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-1">${summary.total_ratings}</h3>
                            <small class="text-muted">Total Ratings</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-${getRatingColor(summary.avg_total)} mb-1">${summary.avg_total}/5</h3>
                            <small class="text-muted">Average Rating</small>
                            <div class="mt-1">${renderStars(Math.round(summary.avg_total))}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-${getRatingColor(summary.avg_total)} mb-0">${getRatingLabel(summary.avg_total)}</h4>
                            <small class="text-muted">Overall Performance</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="text-info mb-0">${((summary.avg_total / 5) * 100).toFixed(1)}%</h4>
                            <small class="text-muted">Satisfaction Rate</small>
                        </div>
                    </div>
                </div>
                
                <!-- Category Breakdown -->
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="small"><strong>Response time to your initial call for service:</strong></span>
                            <span class="badge bg-${getRatingColor(summary.avg_response)}">${summary.avg_response}/5</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="small"><strong>Quality of service provided to resolve the problem:</strong></span>
                            <span class="badge bg-${getRatingColor(summary.avg_quality)}">${summary.avg_quality}/5</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="small"><strong>Courtesy and professionalism of the attending ICT staff:</strong></span>
                            <span class="badge bg-${getRatingColor(summary.avg_courtesy)}">${summary.avg_courtesy}/5</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center p-2 bg-white rounded border">
                            <span class="small"><strong>Overall satisfaction with the assistance/service provided:</strong></span>
                            <span class="badge bg-${getRatingColor(summary.avg_overall)}">${summary.avg_overall}/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Individual Ratings -->
        <h5 class="mb-3"><i class="fas fa-list"></i> Individual Ratings (${ratings.length})</h5>
        <div class="row">
    `;
    
    ratings.forEach((rating, index) => {
        const avgRating = (rating.eval_response + rating.eval_quality + rating.eval_courtesy + rating.eval_overall) / 4;
        const submittedDate = new Date(rating.submitted_at).toLocaleString();
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="card h-100 border">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="fas fa-user"></i> ${escapeHtml(rating.rater_name || 'Anonymous')}</strong>
                            ${rating.office ? `<br><small class="text-muted">${escapeHtml(rating.office)}</small>` : ''}
                        </div>
                        <span class="badge bg-${getRatingColor(avgRating)}">${avgRating.toFixed(1)}/5</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted"><i class="fas fa-calendar"></i> ${submittedDate}</small>
                        </div>
                        
                        <div class="rating-breakdown mb-3">
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small"><strong>Response time to your initial call for service</strong></span>
                                    <div>
                                        ${renderStars(rating.eval_response)}
                                        <span class="badge bg-${getRatingColor(rating.eval_response)} ms-2">${rating.eval_response}/5</span>
                                    </div>
                                </div>
                                <small class="text-muted ms-3">${getSatisfactionLevel(rating.eval_response)}</small>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small"><strong>Quality of service provided to resolve the problem</strong></span>
                                    <div>
                                        ${renderStars(rating.eval_quality)}
                                        <span class="badge bg-${getRatingColor(rating.eval_quality)} ms-2">${rating.eval_quality}/5</span>
                                    </div>
                                </div>
                                <small class="text-muted ms-3">${getSatisfactionLevel(rating.eval_quality)}</small>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small"><strong>Courtesy and professionalism of the attending ICT staff</strong></span>
                                    <div>
                                        ${renderStars(rating.eval_courtesy)}
                                        <span class="badge bg-${getRatingColor(rating.eval_courtesy)} ms-2">${rating.eval_courtesy}/5</span>
                                    </div>
                                </div>
                                <small class="text-muted ms-3">${getSatisfactionLevel(rating.eval_courtesy)}</small>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small"><strong>Overall satisfaction with the assistance/service provided</strong></span>
                                    <div>
                                        ${renderStars(rating.eval_overall)}
                                        <span class="badge bg-${getRatingColor(rating.eval_overall)} ms-2">${rating.eval_overall}/5</span>
                                    </div>
                                </div>
                                <small class="text-muted ms-3">${getSatisfactionLevel(rating.eval_overall)}</small>
                            </div>
                        </div>
                        
                        ${rating.comments ? `
                            <div class="mt-3 p-2 bg-light rounded">
                                <strong><i class="fas fa-comment"></i> Comments:</strong>
                                <p class="mb-0 mt-1 small">${escapeHtml(rating.comments)}</p>
                            </div>
                        ` : '<p class="text-muted small mb-0"><em>No additional comments provided.</em></p>'}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
    `;
    
    container.innerHTML = html;
}

// ---------------- Helpers ----------------
function openCompleteModal(id, type) {
    document.getElementById('completeItemId').value = id;
    document.getElementById('completeItemType').value = type;
    document.getElementById('completeRemarks').value = '';
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
.kanban-column { 
    height: 75vh; 
    overflow-y: auto; 
    border-radius: 12px;
    transition: all 0.3s ease;
}

.kanban-column:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.kanban-column .card-header {
    border-radius: 12px 12px 0 0;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.kanban-column .card-body {
    padding: 15px;
    min-height: 200px;
}

/* Task Card Styling */
.task-card { 
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #dc3545 0%, #343a40 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.task-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    border-color: #dc3545;
}

.task-card:hover::before {
    opacity: 1;
}

.task-card.completed { 
    opacity: 0.85;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-color: #28a745;
}

.task-card.completed::before {
    background: #28a745;
    opacity: 1;
}

/* Service Request Card */
.service-request-card { 
    border-left: 4px solid #007bff;
    background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);
}

.service-request-card:hover {
    border-left-width: 6px;
    background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
}

.service-request-card .service-request-info { 
    font-size: 0.9rem;
    line-height: 1.6;
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
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.priority-medium {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.priority-high {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.priority-urgent {
    background: linear-gradient(135deg, #f5c6cb 0%, #f1aeb5 100%);
    color: #721c24;
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

/* Service Request Info */
.service-request-info {
    background: rgba(0, 123, 255, 0.05);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.service-request-info strong {
    color: #007bff;
    font-weight: 600;
}

.feedback-card {
    background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(13,110,253,0.02) 100%);
    border-left: 4px solid #0d6efd;
    border-radius: 10px;
    box-shadow: inset 0 0 0 1px rgba(13,110,253,0.1);
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

/* Responsive Design */
@media (max-width: 768px) {
    .kanban-column {
        height: 60vh;
        margin-bottom: 20px;
    }
    
    .task-card {
        padding: 15px;
    }
    
    .task-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php require_once 'footer.php'; ?>
