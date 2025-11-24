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
$stmt->close();

// Fetch completed records across modules for this technician
$completed_params = [$user_id, $user_id, $user_id];
$completed_types = "iii";
$completed_filters = [];

if ($date_from) {
    $completed_filters[] = "DATE(completed_date) >= ?";
    $completed_params[] = $date_from;
    $completed_types .= "s";
}
if ($date_to) {
    $completed_filters[] = "DATE(completed_date) <= ?";
    $completed_params[] = $date_to;
    $completed_types .= "s";
}

$completed_sql = "
    SELECT *
    FROM (
        SELECT 
            'Service Request' AS record_type,
            sr.id AS record_id,
            COALESCE(sr.equipment, 'Service Request') AS title,
            sr.accomplishment AS details,
            sr.support_level,
            sr.processing_time,
            sr.completed_at AS completed_date,
            sr.remarks AS notes
        FROM service_requests sr
        WHERE sr.technician_id = ? AND sr.status = 'Completed'

        UNION ALL

        SELECT 
            'System Request' AS record_type,
            sys.id AS record_id,
            COALESCE(sys.description, 'System Request') AS title,
            sys.description AS details,
            NULL AS support_level,
            NULL AS processing_time,
            sys.updated_at AS completed_date,
            sys.remarks AS notes
        FROM system_requests sys
        WHERE sys.technician_id = ? AND sys.status = 'Completed'

        UNION ALL

        SELECT 
            'Maintenance' AS record_type,
            mr.id AS record_id,
            COALESCE(mr.equipment_type, 'Maintenance Task') AS title,
            mr.description AS details,
            mr.support_level,
            mr.processing_time,
            mr.completed_at AS completed_date,
            mr.remarks AS notes
        FROM maintenance_records mr
        WHERE mr.technician_id = ? AND mr.status = 'completed'
    ) AS completed_records
";

if (!empty($completed_filters)) {
    $completed_sql .= " WHERE " . implode(' AND ', $completed_filters);
}
$completed_sql .= " ORDER BY completed_date DESC";

$completed_stmt = $conn->prepare($completed_sql);
$completed_stmt->bind_param($completed_types, ...$completed_params);
$completed_stmt->execute();
$completed_history = $completed_stmt->get_result();
$completed_stmt->close();

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
<!-- Completed Requests -->
<div class="card mt-4">
    <div class="card-header">
        <h5><i class="fas fa-check-circle"></i> Completed Requests & Maintenance</h5>
        <small class="text-muted">All finished service/system requests and maintenance jobs you handled.</small>
    </div>
    <div class="card-body">
        <?php if ($completed_history->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Details / Notes</th>
                            <th>Support Level</th>
                            <th>SLA / Processing</th>
                            <th>Completed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($completed = $completed_history->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge bg-success"><?= htmlspecialchars($completed['record_type']); ?></span></td>
                                <td><?= htmlspecialchars($completed['title']); ?></td>
                                <td>
                                    <?php if (!empty($completed['details'])): ?>
                                        <div><strong>Summary:</strong> <?= nl2br(htmlspecialchars($completed['details'])); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($completed['notes'])): ?>
                                        <div class="text-muted small"><?= nl2br(htmlspecialchars($completed['notes'])); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($completed['support_level'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($completed['processing_time'] ?? 'N/A'); ?></td>
                                <td><?= $completed['completed_date'] ? date("M d, Y H:i", strtotime($completed['completed_date'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No completed records found</h5>
                <p class="text-muted mb-0">Finish a service, system request, or maintenance record to see it logged here.</p>
            </div>
        <?php endif; ?>
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
