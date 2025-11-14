<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

if (!isLoggedIn() || !isTechnician()) {
    header('Location: ../landing.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$status_filter = $_GET['status'] ?? '';
$date_from     = $_GET['date_from'] ?? '';
$date_to       = $_GET['date_to'] ?? '';

$where = ["h.user_id = ?"];
$params = [$user_id];
$types  = "i";

if ($status_filter) {
    $where[] = "h.action = ?";
    $params[] = $status_filter;
    $types   .= "s";
}
if ($date_from) {
    $where[] = "DATE(h.timestamp) >= ?";
    $params[] = $date_from;
    $types   .= "s";
}
if ($date_to) {
    $where[] = "DATE(h.timestamp) <= ?";
    $params[] = $date_to;
    $types   .= "s";
}
$where_clause = implode(" AND ", $where);
$sql = "
    SELECT h.*, u.email,
        COALESCE(
            l.asset_tag, p.asset_tag, a.asset_tag, s.asset_tag, t.asset_tag, d.asset_tag
        ) AS equipment_name
    FROM history h
    LEFT JOIN users u ON h.user_id = u.id

    -- Join each possible equipment table
    LEFT JOIN laptops l ON (h.table_name = 'laptops' AND h.equipment_id = l.id)
    LEFT JOIN printers p ON (h.table_name = 'printers' AND h.equipment_id = p.id)
    LEFT JOIN accesspoint a ON (h.table_name = 'accesspoint' AND h.equipment_id = a.id)
    LEFT JOIN switch s ON (h.table_name = 'switch' AND h.equipment_id = s.id)
    LEFT JOIN telephone t ON (h.table_name = 'telephone' AND h.equipment_id = t.id)
    LEFT JOIN desktop d ON (h.table_name = 'desktop' AND h.equipment_id = d.id)

    WHERE $where_clause
    ORDER BY h.timestamp DESC
";


$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$equipment_history = $stmt->get_result();

$page_title = 'Equipment History';
require_once 'header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-history"></i> Equipment History</h2>
                <button class="btn btn-outline-primary" onclick="exportHistory()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header"><h5><i class="fas fa-filter"></i> Filters</h5></div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Action</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="qr_scan" <?= $status_filter=='qr_scan'?'selected':'' ?>>QR Scan</option>
                                <option value="maintenance" <?= $status_filter=='maintenance'?'selected':'' ?>>Maintenance</option>
                                <option value="update" <?= $status_filter=='update'?'selected':'' ?>>Update</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                                <a href="history.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Equipment History Table -->
          <div class="card">
    <div class="card-header"><h5><i class="fas fa-list"></i> History Logs</h5></div>
    <div class="card-body">
        <?php if ($equipment_history->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipment Name</th>
                            <th>Table</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($row = $equipment_history->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td>
    <?= ucfirst(htmlspecialchars($row['table_name'])) ?>
    - <?= htmlspecialchars($row['equipment_name'] ?? 'N/A'); ?>
</td>

                                <td><span class="badge bg-info"><?= htmlspecialchars($row['table_name']); ?></span></td>
                                <td><?= date("M d, Y H:i", strtotime($row['timestamp'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No History Found</h4>
                <p class="text-muted">No history records found for your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- User Tasks -->
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-tasks"></i> My Tasks</h5>
    </div>
    <div class="card-body">
        <?php
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE assigned_to = ? ORDER BY due_date ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $tasks = $stmt->get_result();
        ?>

        <?php if ($tasks->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Due Date</th>
                            <th>Created</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while ($task = $tasks->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($task['title']); ?></td>
                                <td><?= htmlspecialchars($task['description']); ?></td>
                                <td>
                                    <span class="badge bg-<?= $task['priority'] == 'high' ? 'danger' : ($task['priority'] == 'medium' ? 'warning' : 'secondary'); ?>">
                                        <?= ucfirst($task['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $task['status'] == 'completed' ? 'success' : ($task['status'] == 'in_progress' ? 'info' : 'secondary'); ?>">
                                        <?= ucfirst($task['status']); ?>
                                    </span>
                                </td>
								<td><?= htmlspecialchars($task['remarks']); ?></td>
                                <td><?= htmlspecialchars($task['due_date']); ?></td>
                                <td><?= date("M d, Y H:i", strtotime($task['created_at'])); ?></td>
                                <td><?= date("M d, Y H:i", strtotime($task['updated_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Tasks Assigned</h5>
                <p class="text-muted">You don’t have any tasks assigned yet.</p>
            </div>
        <?php endif; ?>

        <?php $stmt->close(); ?>
    </div>
</div>

        </div>
    </div>
</div>

<script>
function exportHistory() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', '1');
    window.location.href = `export_history.php?${params.toString()}`;
}
</script>

<?php require_once 'footer.php'; ?>
