<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Check if user is logged in
requireLogin();

$message = '';
$error = '';

// Handle task operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
				$title = trim($_POST['title']);
				$description = trim($_POST['description']);
				$assigned_to = $_POST['assigned_to']; 
				$priority = $_POST['priority'];
				$due_date = $_POST['due_date'];

				include '../logger.php';
				logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], "Task", "Assigned a task to user ID ".$assigned_to);

				// --- insert Task ---
				$stmt = $conn->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, due_date, remarks) 
					VALUES (?, ?, ?, ?, ?, ?, ?)");
				$remarks = ''; // Default empty remarks
				$stmt->bind_param("ssiisss", $title, $description, $assigned_to, $_SESSION['user_id'], $priority, $due_date, $remarks);

				if ($stmt->execute()) {
				$message = 'Task assigned successfully!';

				$userStmt = $conn->prepare("SELECT email, full_name FROM users WHERE id = ?");
				$userStmt->bind_param("i", $assigned_to);
				$userStmt->execute();
				$userStmt->bind_result($assigned_email, $assigned_name);
				$userStmt->fetch();
				$userStmt->close();

				if ($assigned_email) {
				require_once __DIR__ . '/../vendor/autoload.php';

				$mail = new PHPMailer(true);

				try {
				$mail->isSMTP();
				$mail->Host = 'smtp.gmail.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'ictoffice0520@gmail.com';
				$mail->Password = 'hkmp gplq zxsd otmy';
				$mail->SMTPSecure = 'tls';
				$mail->Port = 587;

				$mail->setFrom('your_email@gmail.com', 'Task Manager');
				$mail->addAddress($assigned_email, $assigned_name);

				$mail->isHTML(true);
				$mail->Subject = "New Task Assigned: $title";
				$mail->Body    = "
				<h3>Hello $assigned_name,</h3>
				<p>You have been assigned a new task.</p>
				<p><b>Title:</b> $title</p>
				<p><b>Description:</b> $description</p>
				<p><b>Priority:</b> $priority</p>
				<p><b>Due Date:</b> $due_date</p>
				<br>
				<p>Assigned by: ".$_SESSION['user_name']."</p>
				";

				$mail->send();
				} catch (Exception $e) {
				error_log("Mailer Error: {$mail->ErrorInfo}");
				}
				}

				} else {
				$error = 'Failed to assign task.';
				}
				$stmt->close();

                break;
                
            case 'update':
                $id = $_POST['id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $assigned_to = $_POST['assigned_to'];
                $priority = $_POST['priority'];
                $status = $_POST['status'];
                $due_date = $_POST['due_date'];
                include '../logger.php';
				logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], "Task", "Updated a task to". $assigned_to);
                $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, assigned_to = ?, priority = ?, status = ?, due_date = ? WHERE id = ?");
                $stmt->bind_param("ssiissi", $title, $description, $assigned_to, $priority, $status, $due_date, $id);
                
                if ($stmt->execute()) {
                    $message = 'Task updated successfully!';
                } else {
                    $error = 'Failed to update task.';
                }
                $stmt->close();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                include '../logger.php';
				logAdminAction($_SESSION['user_id'], $_SESSION['user_name'], "Task", "Deleted a task.");
                $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $message = 'Task deleted successfully!';
                } else {
                    $error = 'Failed to delete task.';
                }
                $stmt->close();
                break;
        }
    }
}

// Get tasks list with filters
$where_conditions = [];
$params = [];
$types = '';

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_conditions[] = "t.status = ?";
    $params[] = $_GET['status'];
    $types .= 's';
}

if (isset($_GET['priority']) && !empty($_GET['priority'])) {
    $where_conditions[] = "t.priority = ?";
    $params[] = $_GET['priority'];
    $types .= 's';
}

if (isset($_GET['assigned_to']) && !empty($_GET['assigned_to'])) {
    $where_conditions[] = "t.assigned_to = ?";
    $params[] = $_GET['assigned_to'];
    $types .= 'i';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$query = "
    SELECT t.*, 
           u1.full_name as assigned_user_name,
           u2.full_name as assigned_by_name
    FROM tasks t
    LEFT JOIN users u1 ON t.assigned_to = u1.id
    LEFT JOIN users u2 ON t.assigned_by = u2.id
    $where_clause
    ORDER BY 
        CASE t.priority 
            WHEN 'urgent' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            WHEN 'low' THEN 4 
        END,
        t.due_date ASC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tasks_result = $stmt->get_result();

// Get users for assignment
$users = $conn->query("SELECT id, full_name, role FROM users ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - BSU Inventory Management System</title>
    <link rel="icon" href="assets/logo/bsutneu.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
         :root { --primary-color: #dc3545; --secondary-color: #343a40; }
        .navbar { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); }
        .sidebar { background: white; min-height: calc(100vh - 56px); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar .nav-link { color: var(--secondary-color); margin: 4px 10px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: var(--primary-color); color: #fff; }
        .main-content { padding: 20px; }
        .card { border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
    </style>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="../images/Ict logs.png" alt="Logo" style="height:40px;"> BSU Inventory System
            </a>
            <div class="navbar-nav ms-auto">
                <a href="profile.php" class="btn btn-light me-2"><i class="fas fa-user-circle"></i> Profile</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-tasks"></i> Task Management</h2>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="fas fa-plus"></i> Assign Task
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo (isset($_GET['status']) && $_GET['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select class="form-control" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="urgent" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                                    <option value="high" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                                    <option value="medium" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="low" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Assigned To</label>
                                <select class="form-control" name="assigned_to">
                                    <option value="">All Users</option>
                                    <?php 
                                    $users->data_seek(0);
                                    while ($user = $users->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['assigned_to']) && $_GET['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo $user['full_name']; ?> (<?php echo ucfirst($user['role']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-danger me-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="tasks.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tasks Grid -->
                <div class="row">
                    <?php while ($task = $tasks_result->fetch_assoc()): ?>
                        <?php
                        $is_overdue = $task['due_date'] && $task['due_date'] < date('Y-m-d') && $task['status'] != 'completed';
                        $priority_class = 'priority-' . $task['priority'];
                        $status_class = [
                            'pending' => 'secondary',
                            'in_progress' => 'warning',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card task-card <?php echo $is_overdue ? 'overdue' : ''; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($task['title']); ?></h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editTask(<?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="deleteTask(<?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($task['description'], 0, 100)); ?>
                                        <?php if (strlen($task['description']) > 100): ?>...<?php endif; ?>
                                    </p>
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Priority:</small><br>
                                            <span class="<?php echo $priority_class; ?>">
                                                <i class="fas fa-flag"></i> <?php echo ucfirst($task['priority']); ?>
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Status:</small><br>
                                            <span class="badge bg-<?php echo $status_class[$task['status']]; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <small class="text-muted">Assigned To:</small><br>
                                            <strong><?php echo htmlspecialchars($task['assigned_user_name']); ?></strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Due Date:</small><br>
                                            <strong><?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></strong>
                                        </div>
                                    </div>
                                    
                                    <?php if ($is_overdue): ?>
                                        <div class="alert alert-danger py-1 mb-0">
                                            <i class="fas fa-exclamation-triangle"></i> Overdue
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Assigned by: <?php echo htmlspecialchars($task['assigned_by_name']); ?><br>
                                            Created: <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></button>><i class="fas fa-plus"></i> Assign New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Task Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Describe the task in detail..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign To</label>
                                <select class="form-control" name="assigned_to" required>
                                    <option value="">Select User</option>
                                    <?php 
                                    $users->data_seek(0);
                                    while ($user = $users->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo $user['full_name']; ?> (<?php echo ucfirst($user['role']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-control" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Assign Task</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTask(id) {
            // Implement edit functionality
            alert('Edit task functionality will be implemented');
        }

        function deleteTask(id) {
            if (confirm('Are you sure you want to delete this task?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script>
    // Logout confirmation
    document.addEventListener('click', function(e) {
        const logoutLink = e.target.closest('a[href="logout.php"]');
        if (!logoutLink) return;
        e.preventDefault();
        if (confirm('Are you sure you want to log out?')) {
            window.location.href = logoutLink.href;
        }
    });
    </script>
</body>
</html> 