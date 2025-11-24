<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

// First, ensure the requests table exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS `requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `form_type` varchar(255) NOT NULL,
    `form_data` longtext DEFAULT NULL,
    `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    KEY `form_type` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

$conn->query($createTableQuery);

// Check if form_data column exists, if not add it
$checkColumnQuery = "SHOW COLUMNS FROM `requests` LIKE 'form_data'";
$columnResult = $conn->query($checkColumnQuery);

if ($columnResult->num_rows == 0) {
    $addColumnQuery = "ALTER TABLE `requests` ADD COLUMN `form_data` longtext DEFAULT NULL AFTER `form_type`";
    $conn->query($addColumnQuery);
}

// Fetch only Service Request Form requests (office requests)
$query = "SELECT r.*, u.full_name FROM requests r 
          LEFT JOIN users u ON r.user_id = u.id 
          WHERE r.form_type = 'ICT Service Request Form'
          ORDER BY r.created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("❌ Query Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - BSU Inventory Management System</title>
    <link rel="icon" href="../images/bsutneu.png" type="image/png">
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
        .btn-view { background-color: #ffc107; color: black; border: none; }
        .btn-approve { background-color: #6c757d; color: white; border: none; }
        .btn-reject { background-color: #dc3545; color: white; border: none; }
        .btn-view:hover { background-color: #e0a800 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-approve:hover { background-color: #5a6268 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-reject:hover { background-color: #c82333 !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-outline-danger:hover { background-color: #dc3545 !important; color: white !important; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .btn-action { transition: all 0.3s ease; }
        .page-item.active .page-link { background-color: #dc3545 !important; border-color: #dc3545 !important; }
    </style>
</head>
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
            <div class="col-md-9 col-lg-10 p-4">
                <h2><i class="fas fa-wrench"></i> Service Request (Office Requests)</h2>
                <div class="card p-4 mt-3">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="requestTable" class="table table-bordered table-striped text-center align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>User Name</th>
                                        <th>Form Type</th>
                                        <th>Status</th>
                                        <th>Date Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <?php 
                                            $formDataArray = json_decode($row['form_data'] ?? '[]', true);
                                            $requestPayload = [
                                                'id' => $row['id'],
                                                'form_type' => $row['form_type'],
                                                'status' => $row['status'],
                                                'created_at' => $row['created_at'],
                                                'full_name' => $row['full_name'] ?? 'Unknown User',
                                                'form_data' => $formDataArray
                                            ];
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['id']) ?></td>
                                            <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown User') ?></td>
                                            <td><?= htmlspecialchars($row['form_type']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match ($row['status']) {
                                                    'Approved' => 'bg-success text-white',
                                                    'Rejected' => 'bg-danger text-white',
                                                    default => 'bg-warning text-dark'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= htmlspecialchars($row['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                                            <td>
                                                <!-- View Button -->
                                                <button type="button"
                                                        class="btn btn-view btn-sm btn-action view-request-btn"
                                                        data-request='<?= htmlspecialchars(json_encode($requestPayload), ENT_QUOTES, 'UTF-8') ?>'>
                                                    <i class="fas fa-eye"></i> View
                                                </button>

                                                <!-- Approve Button -->
                                                <a href="approve_request.php?id=<?= $row['id'] ?>" class="btn btn-approve btn-sm btn-action" onclick="return confirm('Approve this request?');">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>

                                                <!-- Reject Button -->
                                                <a href="reject_request.php?id=<?= $row['id'] ?>" class="btn btn-reject btn-sm btn-action" onclick="return confirm('Reject this request?');">
                                                    <i class="fas fa-times"></i> Reject
                                                </a>

                                                <!-- Delete Button -->
                                                <a href="delete_request.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm btn-action" onclick="return confirm('Are you sure you want to delete this request? This action cannot be undone.');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No requests found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="requestModalLabel"><i class="fas fa-file-alt text-danger"></i> Request Details</h5>
                    <small class="text-muted" id="requestModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5><i class="fas fa-user-circle text-secondary"></i> Request Summary</h5>
                                <ul class="list-unstyled mt-3 mb-4" id="requestMetaList"></ul>
                                <div class="border-top pt-3">
                                    <h6><i class="fas fa-list text-secondary"></i> Submitted Details</h6>
                                    <div id="requestDetailsList" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0"><i class="fas fa-file-pdf text-danger"></i> PDF Preview</h5>
                                    <a href="#" id="viewFullPageLink" class="btn btn-outline-secondary btn-sm" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Open Full Page
                                    </a>
                                </div>
                                <iframe id="requestPdfFrame" src="" style="width: 100%; flex-grow: 1; border-radius: 8px; border: 1px solid #dee2e6;" title="Request PDF Preview"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#requestTable').DataTable();
        });

        const requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
        const metaList = document.getElementById('requestMetaList');
        const detailsList = document.getElementById('requestDetailsList');
        const requestPdfFrame = document.getElementById('requestPdfFrame');
        const modalSubtitle = document.getElementById('requestModalSubtitle');
        const viewFullPageLink = document.getElementById('viewFullPageLink');

        function formatLabel(key) {
            return key
                .replace(/_/g, ' ')
                .replace(/([a-z])([A-Z])/g, '$1 $2')
                .replace(/\b\w/g, c => c.toUpperCase());
        }

        function formatValue(value) {
            if (Array.isArray(value)) {
                const flattened = value.map(item => formatValue(item)).filter(Boolean);
                return flattened.join(', ');
            } else if (typeof value === 'object' && value !== null) {
                const entries = [];
                for (const [k, v] of Object.entries(value)) {
                    const formatted = formatValue(v);
                    if (formatted) {
                        entries.push(`${formatLabel(k)}: ${formatted}`);
                    }
                }
                return entries.join('; ');
            } else if (value === null || value === undefined) {
                return '';
            }
            const trimmed = String(value).trim();
            return trimmed;
        }

        function buildDetailsList(formData) {
            if (!formData || Object.keys(formData).length === 0) {
                return '<p class="text-muted mb-0">No additional form data provided.</p>';
            }
            const ignoreKeys = ['form_type', 'formType', 'token'];
            let sections = ['<div class="row g-3">'];
            let count = 0;

            for (const [key, value] of Object.entries(formData)) {
                if (ignoreKeys.includes(key)) continue;
                const formattedValue = formatValue(value);
                if (!formattedValue) continue;

                sections.push(`
                    <div class="col-sm-6">
                        <div class="detail-card p-2 h-100 border rounded">
                            <small class="text-muted text-uppercase fw-semibold d-block">${formatLabel(key)}</small>
                            <span class="fw-semibold">${formattedValue}</span>
                        </div>
                    </div>
                `);
                count++;
            }

            sections.push('</div>');
            return count > 0 ? sections.join('') : '<p class="text-muted mb-0">No additional form data provided.</p>';
        }

        document.querySelectorAll('.view-request-btn').forEach(button => {
            button.addEventListener('click', () => {
                const payload = JSON.parse(button.dataset.request);
                modalSubtitle.textContent = `Request #${payload.id} · ${payload.form_type}`;
                metaList.innerHTML = `
                    <li><strong>Submitted By:</strong> ${payload.full_name || 'Unknown'}</li>
                    <li><strong>Status:</strong> ${payload.status}</li>
                    <li><strong>Date Submitted:</strong> ${payload.created_at}</li>
                `;
                detailsList.innerHTML = buildDetailsList(payload.form_data || {});
                requestPdfFrame.src = `request_pdf_preview.php?id=${payload.id}`;
                viewFullPageLink.href = `view_request.php?id=${payload.id}`;
                requestModal.show();
            });
        });

        document.getElementById('requestModal').addEventListener('hidden.bs.modal', () => {
            requestPdfFrame.src = '';
        });
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