<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check if user is logged in and is a technician
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'technician') {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch activity logs for this technician
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$countQuery = "SELECT COUNT(*) AS total FROM admin_logs WHERE admin_id = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalResult = $stmt->get_result()->fetch_assoc();
$totalLogs = $totalResult['total'];
$totalPages = ceil($totalLogs / $limit);
$stmt->close();

// Fetch logs with pagination
$logsQuery = "SELECT * FROM admin_logs WHERE admin_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($logsQuery);
$stmt->bind_param("iii", $user_id, $limit, $offset);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Activity Logs';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-2" style="color: #212529; font-weight: 700;">
                        <i class="fas fa-clipboard-list text-danger"></i> My Activity Logs
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> View all your system activities and actions
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-list"></i> Activity Logs
                    <span class="badge bg-light text-dark ms-2"><?php echo $totalLogs; ?> total</span>
                </div>
                <div class="card-body">
                    <?php if (count($logs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = $offset + 1;
                                    foreach ($logs as $log): 
                                    ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar-alt"></i>
                                                <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Activity Logs Found</h4>
                            <p class="text-muted">You haven't performed any actions yet. Your activity logs will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        background-color: #343a40;
        color: white;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    
    .badge {
        font-weight: 500;
        padding: 6px 12px;
    }
    
    .pagination .page-link {
        color: #dc3545;
        border-color: #dc3545;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    
    .pagination .page-link:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
</style>

<?php require_once 'footer.php'; ?>

