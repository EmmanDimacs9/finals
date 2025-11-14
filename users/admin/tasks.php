<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$pdo = getDBConnection();
$message = '';
$message_type = '';

// Handle task creation/editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'edit') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $assigned_to = $_POST['assigned_to'];
            $equipment_id = $_POST['equipment_id'] ?: null;
            $priority = $_POST['priority'];
            $due_date = $_POST['due_date'] ?: null;
            
            if (empty($title) || empty($assigned_to)) {
                $message = 'Please fill in all required fields.';
                $message_type = 'danger';
            } else {
                try {
                    if ($_POST['action'] === 'create') {
                        $stmt = $pdo->prepare("
                            INSERT INTO tasks (title, description, assigned_to, equipment_id, priority, due_date, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$title, $description, $assigned_to, $equipment_id, $priority, $due_date, $_SESSION['user_id']]);
                        $message = 'Task created successfully!';
                    } else {
                        $task_id = $_POST['task_id'];
                        $stmt = $pdo->prepare("
                            UPDATE tasks SET title = ?, description = ?, assigned_to = ?, equipment_id = ?, priority = ?, due_date = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $assigned_to, $equipment_id, $priority, $due_date, $task_id]);
                        $message = 'Task updated successfully!';
                    }
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $task_id = $_POST['task_id'];
            try {
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
                $stmt->execute([$task_id]);
                $message = 'Task deleted successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'danger';
            }
        }
    }
}

// Get all users for assignment
$stmt = $pdo->prepare("SELECT id, full_name, role FROM users ORDER BY full_name");
$stmt->execute();
$users = $stmt->fetchAll();

// Get all equipment
$stmt = $pdo->prepare("SELECT id, name FROM equipment ORDER BY name");
$stmt->execute();
$equipment = $stmt->fetchAll();

// Get all tasks
$stmt = $pdo->prepare("
    SELECT t.*, u.full_name as assigned_to_name, e.name as equipment_name, c.full_name as created_by_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    LEFT JOIN equipment e ON t.equipment_id = e.id
    LEFT JOIN users c ON t.created_by = c.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i> User Management System - Admin
            </div>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../logout.php" class="btn btn-secondary" style="margin-left: 1rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../tasks.php" class="nav-link">
                        <i class="fas fa-tasks nav-icon"></i>
                        <span class="nav-text">My Task</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../qr.php" class="nav-link">
                        <i class="fas fa-qrcode nav-icon"></i>
                        <span class="nav-text">QR</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../history.php" class="nav-link">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../profile.php" class="nav-link">
                        <i class="fas fa-user nav-icon"></i>
                        <span class="nav-text">Profile</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Manage Tasks</h1>
                <button class="btn btn-primary" onclick="showCreateForm()">
                    <i class="fas fa-plus"></i> Add New Task
                </button>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Create/Edit Task Form -->
            <div id="taskForm" class="card" style="display: none; margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title" id="formTitle">Create New Task</h2>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="task_id" id="taskId">
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Task Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_to" class="form-label">Assign To *</label>
                        <select id="assigned_to" name="assigned_to" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['full_name']); ?> (<?php echo ucfirst($user['role']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="equipment_id" class="form-label">Equipment (Optional)</label>
                        <select id="equipment_id" name="equipment_id" class="form-select">
                            <option value="">Select Equipment</option>
                            <?php foreach ($equipment as $eq): ?>
                                <option value="<?php echo $eq['id']; ?>">
                                    <?php echo htmlspecialchars($eq['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select id="priority" name="priority" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" id="due_date" name="due_date" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Task
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tasks List -->
            <h2 style="margin-bottom: 1rem; color: var(--dark-gray);">All Tasks</h2>
            <?php if ($tasks): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Assigned To</th>
                                <th>Equipment</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['assigned_to_name']); ?></td>
                                    <td><?php echo htmlspecialchars($task['equipment_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo str_replace('_', '-', $task['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $task['priority']; ?>">
                                            <?php echo ucfirst($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></td>
                                    <td><?php echo htmlspecialchars($task['created_by_name']); ?></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                onclick="editTask(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                                    onclick="return confirm('Are you sure you want to delete this task?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray); padding: 2rem;">No tasks found.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function showCreateForm() {
            document.getElementById('taskForm').style.display = 'block';
            document.getElementById('formTitle').textContent = 'Create New Task';
            document.getElementById('formAction').value = 'create';
            document.getElementById('taskId').value = '';
            document.getElementById('title').value = '';
            document.getElementById('description').value = '';
            document.getElementById('assigned_to').value = '';
            document.getElementById('equipment_id').value = '';
            document.getElementById('priority').value = 'medium';
            document.getElementById('due_date').value = '';
        }
        
        function hideForm() {
            document.getElementById('taskForm').style.display = 'none';
        }
        
        function editTask(task) {
            document.getElementById('taskForm').style.display = 'block';
            document.getElementById('formTitle').textContent = 'Edit Task';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('taskId').value = task.id;
            document.getElementById('title').value = task.title;
            document.getElementById('description').value = task.description;
            document.getElementById('assigned_to').value = task.assigned_to;
            document.getElementById('equipment_id').value = task.equipment_id || '';
            document.getElementById('priority').value = task.priority;
            document.getElementById('due_date').value = task.due_date || '';
        }
    </script>
</body>
</html> 