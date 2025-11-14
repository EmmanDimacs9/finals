<?php
session_start();
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_report') {
    $task_id = $_POST['task_id'];
    $report_content = trim($_POST['report_content']);
    
    if (!empty($report_content)) {
        $stmt = $conn->prepare("INSERT INTO task_reports (task_id, technician_id, report_content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $task_id, $user_id, $report_content);
        
        if ($stmt->execute()) {
            $success = 'Report added successfully!';
			
			
		include 'logger.php';
include 'logger.php';
		logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], "User", "Added Report");   
        } else {
            $error = 'Failed to add report.';
        }
        $stmt->close();
    } else {
        $error = 'Please enter a report content.';
    }
}

// Get tasks assigned to this technician

				


$page_title = 'My Tasks';
require_once 'header.php';
?>

<?php require_once 'footer.php'; ?> 
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-tasks"></i> My Assigned Tasks</h2>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php while ($task = $tasks->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card task-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?php echo htmlspecialchars($task['title']); ?></h6>
                                <span class="badge priority-<?php echo $task['priority']; ?>">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars($task['description']); ?></p>
                                
                                <div class="task-meta mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> Assigned by: <?php echo htmlspecialchars($task['assigned_by_name']); ?><br>
                                        <i class="fas fa-calendar"></i> Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?><br>
                                        <i class="fas fa-clock"></i> Status: 
                                        <span class="badge bg-<?php 
                                            echo $task['status'] == 'completed' ? 'success' : 
                                                ($task['status'] == 'in_progress' ? 'info' : 'warning'); 
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                        </span>
                                    </small>
                                </div>

                                <?php if ($task['status'] !== 'completed'): ?>
                                    <div class="task-actions mb-3">
                                        <?php if ($task['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-info" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'in_progress')">
                                                <i class="fas fa-play"></i> Start Task
                                            </button>
                                        <?php elseif ($task['status'] == 'in_progress'): ?>
                                            <button class="btn btn-sm btn-success" onclick="updateTaskStatus(<?php echo $task['id']; ?>, 'completed')">
                                                <i class="fas fa-check"></i> Complete Task
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="reports-section">
                                    <h6 class="mb-2">
                                        <i class="fas fa-file-alt"></i> Reports (<?php echo $task['report_count']; ?>)
                                    </h6>
                                    
                                    <?php
                                    // Get reports for this task
                                    $reports = $conn->query("
                                        SELECT tr.*, u.full_name as technician_name
                                        FROM task_reports tr
                                        LEFT JOIN users u ON tr.technician_id = u.id
                                        WHERE tr.task_id = {$task['id']}
                                        ORDER BY tr.created_at DESC
                                    ");
                                    
                                    if ($reports->num_rows > 0):
                                    ?>
                                        <div class="reports-list mb-3">
                                            <?php while ($report = $reports->fetch_assoc()): ?>
                                                <div class="report-item p-2 border rounded mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($report['technician_name']); ?> - 
                                                        <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?>
                                                    </small>
                                                    <p class="mb-0 mt-1"><?php echo htmlspecialchars($report['report_content']); ?></p>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small">No reports yet</p>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addReportModal" data-task-id="<?php echo $task['id']; ?>">
                                        <i class="fas fa-plus"></i> Add Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($tasks->num_rows == 0): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No tasks assigned to you</h4>
                    <p class="text-muted">You will see tasks here when they are assigned to you.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Report Modal -->
<div class="modal fade" id="addReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_report">
                <input type="hidden" name="task_id" id="reportTaskId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Report Content</label>
                        <textarea class="form-control" name="report_content" rows="4" required 
                                  placeholder="Describe your progress, findings, or any issues encountered..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateTaskStatus(taskId, newStatus) {
    if (confirm('Are you sure you want to update this task status?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="task_id" value="${taskId}">
            <input type="hidden" name="new_status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle report modal
document.getElementById('addReportModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const taskId = button.getAttribute('data-task-id');
    document.getElementById('reportTaskId').value = taskId;
});
</script>

<style>
.task-card {
    transition: transform 0.3s ease;
}

.task-card:hover {
    transform: translateY(-2px);
}

.priority-low { background-color: #d4edda; color: #155724; }
.priority-medium { background-color: #fff3cd; color: #856404; }
.priority-high { background-color: #f8d7da; color: #721c24; }
.priority-urgent { background-color: #f5c6cb; color: #721c24; }

.report-item {
    background-color: #f8f9fa;
    border-color: #e9ecef !important;
}

.task-actions {
    display: flex;
    gap: 5px;
}

.task-actions .btn {
    font-size: 0.8rem;
    padding: 4px 8px;
}
</style>
