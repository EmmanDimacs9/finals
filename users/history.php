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

// Get task history
$stmt = $pdo->prepare("
    SELECT th.*, t.title as task_title, t.status as task_status, e.name as equipment_name
    FROM task_history th
    JOIN tasks t ON th.task_id = t.id
    LEFT JOIN equipment e ON t.equipment_id = e.id
    WHERE th.user_id = ?
    ORDER BY th.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$task_history = $stmt->fetchAll();

// Get equipment history
$stmt = $pdo->prepare("
    SELECT eh.*, e.name as equipment_name, e.qr_code
    FROM equipment_history eh
    JOIN equipment e ON eh.equipment_id = e.id
    WHERE eh.user_id = ?
    ORDER BY eh.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$equipment_history = $stmt->fetchAll();

// Get all tasks assigned to user
$stmt = $pdo->prepare("
    SELECT t.*, e.name as equipment_name, u.full_name as created_by_name
    FROM tasks t
    LEFT JOIN equipment e ON t.equipment_id = e.id
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.assigned_to = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$all_tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - User Management System</title>
    <link rel="icon" href="../assets/logo/bsutneu.png" type="image/png">
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
            <div>
              
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
                    <a href="tasks.php" class="nav-link">
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
                    <a href="history.php" class="nav-link active">
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
        <!-- All Tasks History -->
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Task History</h1>
            </div>
            
            <?php if ($all_tasks): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Equipment</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_tasks as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
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
                                    <td><?php echo htmlspecialchars($task['created_by_name'] ?? 'System'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($task['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray); padding: 2rem;">No tasks found.</p>
            <?php endif; ?>
        </div>

        <!-- Task Activity History -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Task Activity History</h2>
            </div>
            
            <?php if ($task_history): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Equipment</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($task_history as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['task_title']); ?></td>
                                    <td><?php echo htmlspecialchars($record['equipment_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($record['action']); ?></td>
                                    <td><?php echo htmlspecialchars($record['description']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray); padding: 2rem;">No task activity history found.</p>
            <?php endif; ?>
        </div>

        <!-- Equipment History -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Equipment History</h2>
            </div>
            
            <?php if ($equipment_history): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Equipment</th>
                                <th>QR Code</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipment_history as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['equipment_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($record['qr_code']); ?></code></td>
                                    <td><?php echo htmlspecialchars($record['action']); ?></td>
                                    <td><?php echo htmlspecialchars($record['description']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($record['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--gray); padding: 2rem;">No equipment history found.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html> 