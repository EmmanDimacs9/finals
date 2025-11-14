<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Handle task status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['new_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?");
        $stmt->execute([$new_status, $task_id, $_SESSION['user_id']]);
        
        // Log the action
        $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$task_id, $_SESSION['user_id'], 'Status Updated', "Status changed to $new_status"]);
        
        header('Location: tasks.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Error updating task: ' . $e->getMessage();
    }
}

// Get tasks for the current user
$stmt = $pdo->prepare("
    SELECT t.*, e.name as equipment_name, u.full_name as assigned_by_name
    FROM tasks t 
    LEFT JOIN equipment e ON t.equipment_id = e.id 
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.assigned_to = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();

// Group tasks by status
$tasks_by_status = [
    'pending' => [],
    'in_progress' => [],
    'completed' => [],
    'cancelled' => []
];

foreach ($tasks as $task) {
    $tasks_by_status[$task['status']][] = $task;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - User Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i> User Management System
            </div>
           
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home nav-icon"></i>
                        <span class="nav-text">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tasks.php" class="nav-link active">
                        <i class="fas fa-tasks nav-icon"></i>
                        <span class="nav-text">My Task</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="qr.php" class="nav-link">
                        <i class="fas fa-qrcode nav-icon"></i>
                        <span class="nav-text">QR</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="history.php" class="nav-link">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">History</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
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
                <h1 class="card-title">My Tasks</h1>
            </div>
            
            <!-- Kanban Board -->
            <div class="kanban-board">
                <!-- Pending Tasks -->
                <div class="kanban-column">
                    <h3>
                        <i class="fas fa-clock" style="color: var(--warning);"></i>
                        Pending (<?php echo count($tasks_by_status['pending']); ?>)
                    </h3>
                    <?php foreach ($tasks_by_status['pending'] as $task): ?>
                        <div class="task-card">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <div class="task-meta">
                                <span><i class="fas fa-tools"></i> <?php echo htmlspecialchars($task['equipment_name'] ?? 'N/A'); ?></span>
                                <span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                            <div class="task-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="new_status" value="in_progress">
                                    <button type="submit" name="update_status" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        Start
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- In Progress Tasks -->
                <div class="kanban-column">
                    <h3>
                        <i class="fas fa-spinner" style="color: var(--info);"></i>
                        In Progress (<?php echo count($tasks_by_status['in_progress']); ?>)
                    </h3>
                    <?php foreach ($tasks_by_status['in_progress'] as $task): ?>
                        <div class="task-card">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <div class="task-meta">
                                <span><i class="fas fa-tools"></i> <?php echo htmlspecialchars($task['equipment_name'] ?? 'N/A'); ?></span>
                                <span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                            <div class="task-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <input type="hidden" name="new_status" value="completed">
                                    <button type="submit" name="update_status" class="btn btn-success" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                        Complete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Completed Tasks -->
                <div class="kanban-column">
                    <h3>
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Completed (<?php echo count($tasks_by_status['completed']); ?>)
                    </h3>
                    <?php foreach ($tasks_by_status['completed'] as $task): ?>
                        <div class="task-card">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <div class="task-meta">
                                <span><i class="fas fa-tools"></i> <?php echo htmlspecialchars($task['equipment_name'] ?? 'N/A'); ?></span>
                                <span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                            <div class="task-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></span>
                                <span class="badge badge-completed">Completed</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cancelled Tasks -->
                <div class="kanban-column">
                    <h3>
                        <i class="fas fa-times-circle" style="color: var(--primary-red);"></i>
                        Cancelled (<?php echo count($tasks_by_status['cancelled']); ?>)
                    </h3>
                    <?php foreach ($tasks_by_status['cancelled'] as $task): ?>
                        <div class="task-card">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                            <div class="task-meta">
                                <span><i class="fas fa-tools"></i> <?php echo htmlspecialchars($task['equipment_name'] ?? 'N/A'); ?></span>
                                <span class="badge badge-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                            </div>
                            <div class="task-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date'; ?></span>
                                <span class="badge badge-cancelled">Cancelled</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($tasks)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--gray);">
                    <i class="fas fa-tasks" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3>No tasks assigned</h3>
                    <p>You don't have any tasks assigned to you yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html> 